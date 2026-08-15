"""Validate and restore a backup only after explicit operator confirmation."""
import gzip
import os
import shutil
import subprocess
from pathlib import Path

from django.conf import settings
from django.core.management.base import BaseCommand, CommandError

from core.services.backups import BackupError, decrypt_backup, restore_sqlite_backup, validate_backup_archive


class Command(BaseCommand):
    help = 'Validate a backup by default; restore only with --confirm-restore and an explicit target.'

    def add_arguments(self, parser):
        parser.add_argument('--input', required=True, help='Path to .sql.gz or encrypted .sql.gz.enc backup.')
        parser.add_argument('--target-sqlite', help='Explicit SQLite target path. Required for SQLite restore.')
        parser.add_argument('--target-postgres-dsn', help='Explicit PostgreSQL connection string for psql restore.')
        parser.add_argument('--confirm-restore', action='store_true', help='Required acknowledgement before any database is modified.')

    def handle(self, *args, **options):
        source = Path(options['input']).expanduser().resolve()
        if not source.exists():
            raise CommandError('فایل backup پیدا نشد.')
        temporary = None
        try:
            archive = source
            if source.name.endswith('.enc'):
                temporary = decrypt_backup(source)
                archive = temporary
            validate_backup_archive(archive)
            self.stdout.write(self.style.SUCCESS('اعتبارسنجی archive و محتوای SQL با موفقیت انجام شد.'))
            if not options['confirm_restore']:
                self.stdout.write(self.style.WARNING('dry-run کامل شد. برای restore واقعی --confirm-restore و یک هدف صریح تعیین کنید.'))
                return
            target_sqlite = options.get('target_sqlite')
            target_postgres = options.get('target_postgres_dsn')
            if bool(target_sqlite) == bool(target_postgres):
                raise CommandError('دقیقاً یکی از --target-sqlite یا --target-postgres-dsn را تعیین کنید.')
            if target_sqlite:
                target = Path(target_sqlite).expanduser().resolve()
                restore_sqlite_backup(archive, target_database=target)
                self.stdout.write(self.style.SUCCESS(f'restore SQLite با موفقیت انجام شد: {target}'))
                return
            self._restore_postgres(archive, target_postgres)
            self.stdout.write(self.style.SUCCESS('restore PostgreSQL با موفقیت انجام شد.'))
        except BackupError as exc:
            raise CommandError(str(exc)) from exc
        finally:
            if temporary:
                temporary.unlink(missing_ok=True)

    def _restore_postgres(self, archive: Path, dsn: str):
        executable = getattr(settings, 'BACKUP_PSQL_BIN', 'psql')
        environment = os.environ.copy()
        # Credentials should be supplied by .pgpass or the explicit target DSN, never printed.
        try:
            process = subprocess.Popen(
                [executable, '--set', 'ON_ERROR_STOP=1', '--dbname', dsn],
                stdin=subprocess.PIPE,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                env=environment,
            )
            assert process.stdin is not None
            with gzip.open(archive, 'rb') as source:
                shutil.copyfileobj(source, process.stdin, length=1024 * 1024)
            process.stdin.close()
            _stdout, stderr = process.communicate(timeout=900)
        except (OSError, subprocess.TimeoutExpired) as exc:
            raise CommandError('ابزار psql برای restore PostgreSQL در دسترس نیست یا زمان عملیات تمام شد.') from exc
        if process.returncode != 0:
            raise CommandError(f'restore PostgreSQL ناموفق بود: {stderr.decode("utf-8", errors="replace")[:1000]}')

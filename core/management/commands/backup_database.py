"""Create a local database backup, optionally encrypt/copy it remotely, and apply retention."""
from pathlib import Path

from django.core.management.base import BaseCommand, CommandError

from core.services.backups import (
    BackupError,
    apply_retention,
    backup_directory,
    create_database_backup,
    encrypt_backup,
    upload_encrypted_backup,
)


class Command(BaseCommand):
    help = 'Create a verified gzip SQL database backup; optionally encrypt, upload and prune old artifacts.'

    def add_arguments(self, parser):
        parser.add_argument('--output-dir', help='Private local destination directory; defaults to BACKUP_DIR.')
        parser.add_argument('--encrypt', action='store_true', help='Create an AES-256-CBC/PBKDF2 encrypted copy.')
        parser.add_argument('--upload', action='store_true', help='Upload only the encrypted copy to configured remote storage.')
        parser.add_argument('--keep-days', type=int, help='Retention age in days.')
        parser.add_argument('--keep-count', type=int, help='Minimum number of newest artifacts to keep.')
        parser.add_argument('--retention-only', action='store_true', help='Only apply retention; do not create a new backup.')

    def handle(self, *args, **options):
        directory = Path(options['output_dir']) if options.get('output_dir') else backup_directory()
        if options['upload'] and not options['encrypt']:
            raise CommandError('--upload requires --encrypt; plain backups are never transferred.')
        try:
            if options['retention_only']:
                removed = apply_retention(directory=directory, keep_days=options.get('keep_days'), keep_count=options.get('keep_count'))
                self.stdout.write(self.style.SUCCESS(f'{len(removed)} فایل قدیمی با retention حذف شد.'))
                return
            artifact = create_database_backup(output_dir=directory)
            self.stdout.write(self.style.SUCCESS(f'backup محلی ساخته شد: {artifact.path}'))
            self.stdout.write(f'SHA256: {artifact.checksum}')
            if options['encrypt']:
                encrypted = encrypt_backup(artifact.path)
                self.stdout.write(self.style.SUCCESS(f'نسخهٔ رمزنگاری‌شده ساخته شد: {encrypted.path}'))
                self.stdout.write(f'Encrypted SHA256: {encrypted.checksum}')
                if options['upload']:
                    remote_location = upload_encrypted_backup(encrypted.path)
                    self.stdout.write(self.style.SUCCESS(f'نسخهٔ رمزنگاری‌شده انتقال یافت: {remote_location}'))
            removed = apply_retention(directory=directory, keep_days=options.get('keep_days'), keep_count=options.get('keep_count'))
            if removed:
                self.stdout.write(f'{len(removed)} فایل قدیمی با retention حذف شد.')
        except BackupError as exc:
            raise CommandError(str(exc)) from exc

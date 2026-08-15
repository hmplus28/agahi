"""Database backup, encryption, remote-copy, validation and retention primitives."""
from __future__ import annotations

import fcntl
import gzip
import hashlib
import hmac
import json
import os
import shutil
import sqlite3
import subprocess
from contextlib import contextmanager
from dataclasses import asdict, dataclass
from datetime import datetime, timedelta, timezone as datetime_timezone
from ftplib import FTP, FTP_TLS
from pathlib import Path
from tempfile import NamedTemporaryFile
from typing import Iterator

from django.conf import settings
from django.db import connection
from django.utils import timezone


class BackupError(RuntimeError):
    """Raised for a backup operation that did not complete safely."""


@dataclass(frozen=True)
class BackupArtifact:
    path: Path
    checksum: str
    size_bytes: int
    created_at: str
    database_engine: str
    encrypted: bool = False


def backup_directory() -> Path:
    directory = Path(getattr(settings, 'BACKUP_DIR', settings.BASE_DIR / 'var' / 'backups'))
    directory.mkdir(parents=True, exist_ok=True)
    try:
        directory.chmod(0o700)
    except OSError:
        pass
    return directory


@contextmanager
def backup_lock(directory: Path) -> Iterator[None]:
    """Prevent overlapping cron/manual jobs from producing inconsistent backups."""
    lock_path = directory / '.backup.lock'
    with lock_path.open('w') as lock_file:
        try:
            fcntl.flock(lock_file.fileno(), fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BlockingIOError as exc:
            raise BackupError('یک عملیات backup دیگر در حال اجرا است.') from exc
        try:
            yield
        finally:
            fcntl.flock(lock_file.fileno(), fcntl.LOCK_UN)


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open('rb') as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b''):
            digest.update(chunk)
    return digest.hexdigest()


def _hmac_file(path: Path, key: str) -> str:
    digest = hmac.new(key.encode('utf-8'), digestmod=hashlib.sha256)
    with path.open('rb') as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b''):
            digest.update(chunk)
    return digest.hexdigest()


def _mac_path(path: Path) -> Path:
    return path.with_suffix(path.suffix + '.hmac')


def _timestamp_name(engine: str) -> str:
    return f'agahi-{timezone.now().strftime("%Y%m%dT%H%M%S%fZ")}-{engine}.sql.gz'


def _database_config():
    return settings.DATABASES['default']


def _dump_sqlite(target: Path, database_name: str) -> None:
    """Write a portable SQL dump using SQLite iterdump, without requiring sqlite3 CLI."""
    if database_name in {':memory:', ''} or database_name.startswith('file:'):
        raw_connection = connection.connection
        if raw_connection is None:
            connection.ensure_connection()
            raw_connection = connection.connection
        source = raw_connection
        close_source = False
    else:
        source = sqlite3.connect(database_name)
        close_source = True
    try:
        with gzip.open(target, 'wt', encoding='utf-8', newline='\n') as archive:
            for line in source.iterdump():
                archive.write(line)
                archive.write('\n')
    finally:
        if close_source:
            source.close()


def _dump_postgresql(target: Path, config: dict) -> None:
    """Stream a plain pg_dump through gzip; no cleartext credentials appear in argv."""
    executable = getattr(settings, 'BACKUP_PG_DUMP_BIN', 'pg_dump')
    database_url = ' '.join([
        f"host={config.get('HOST') or 'localhost'}",
        f"port={config.get('PORT') or 5432}",
        f"dbname={config.get('NAME')}",
        f"user={config.get('USER')}",
    ])
    environment = os.environ.copy()
    if config.get('PASSWORD'):
        environment['PGPASSWORD'] = str(config['PASSWORD'])
    command = [executable, '--no-owner', '--no-privileges', '--format=plain', '--dbname', database_url]
    with target.open('wb') as output_file:
        process = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, env=environment)
        assert process.stdout is not None
        with gzip.GzipFile(fileobj=output_file, mode='wb', compresslevel=6) as archive:
            shutil.copyfileobj(process.stdout, archive, length=1024 * 1024)
        stderr = process.stderr.read().decode('utf-8', errors='replace') if process.stderr else ''
        exit_code = process.wait()
    if exit_code != 0:
        target.unlink(missing_ok=True)
        raise BackupError(f'pg_dump ناموفق بود: {stderr[:1000]}')


def create_database_backup(*, output_dir: Path | None = None) -> BackupArtifact:
    """Create a timestamped gzip SQL dump and adjacent immutable manifest/checksum."""
    directory = output_dir or backup_directory()
    directory.mkdir(parents=True, exist_ok=True)
    config = _database_config()
    engine = config['ENGINE'].rsplit('.', 1)[-1]
    final_path = directory / _timestamp_name(engine)
    temporary_path = directory / f'.{final_path.name}.tmp'
    with backup_lock(directory):
        if engine == 'sqlite3':
            _dump_sqlite(temporary_path, str(config.get('NAME', '')))
        elif engine in {'postgresql', 'postgresql_psycopg', 'postgresql_psycopg2'}:
            _dump_postgresql(temporary_path, config)
        else:
            raise BackupError(f'Engine پشتیبانی‌نشده برای backup: {config["ENGINE"]}')
        validate_backup_archive(temporary_path)
        os.replace(temporary_path, final_path)
        artifact = BackupArtifact(
            path=final_path,
            checksum=sha256_file(final_path),
            size_bytes=final_path.stat().st_size,
            created_at=timezone.now().isoformat(),
            database_engine=engine,
        )
        manifest_path = final_path.with_suffix(final_path.suffix + '.json')
        manifest_path.write_text(json.dumps({**asdict(artifact), 'path': final_path.name}, ensure_ascii=False, indent=2), encoding='utf-8')
        try:
            manifest_path.chmod(0o600)
            final_path.chmod(0o600)
        except OSError:
            pass
    return artifact


def validate_backup_archive(path: Path) -> None:
    """Validate gzip framing and require a non-empty SQL-like payload before use/upload."""
    if not path.exists() or path.stat().st_size == 0:
        raise BackupError('فایل backup وجود ندارد یا خالی است.')
    try:
        with gzip.open(path, 'rt', encoding='utf-8', errors='replace') as archive:
            prefix = archive.read(4096)
    except (OSError, EOFError) as exc:
        raise BackupError('فایل backup فشرده معتبر نیست.') from exc
    if not prefix.strip() or not any(marker in prefix for marker in ('CREATE ', 'BEGIN TRANSACTION', 'PRAGMA ')):
        raise BackupError('محتوای backup SQL معتبر به نظر نمی‌رسد.')


def encrypt_backup(path: Path, *, encryption_key: str | None = None) -> BackupArtifact:
    """Create an authenticated-password-derived encrypted copy with OpenSSL PBKDF2/salt."""
    key = encryption_key or getattr(settings, 'BACKUP_ENCRYPTION_KEY', '')
    if not key:
        raise BackupError('BACKUP_ENCRYPTION_KEY برای رمزنگاری backup تنظیم نشده است.')
    if not path.exists():
        raise BackupError('فایل backup برای رمزنگاری پیدا نشد.')
    encrypted_path = path.with_suffix(path.suffix + '.enc')
    temporary_path = encrypted_path.with_suffix(encrypted_path.suffix + '.tmp')
    environment = os.environ.copy()
    environment['BACKUP_ENCRYPTION_KEY'] = key
    command = [
        getattr(settings, 'BACKUP_OPENSSL_BIN', 'openssl'), 'enc', '-aes-256-cbc', '-pbkdf2', '-iter', '600000', '-salt',
        '-in', str(path), '-out', str(temporary_path), '-pass', 'env:BACKUP_ENCRYPTION_KEY',
    ]
    try:
        completed = subprocess.run(command, env=environment, capture_output=True, text=True, timeout=300, check=False)
    except (OSError, subprocess.TimeoutExpired) as exc:
        raise BackupError('ابزار OpenSSL برای رمزنگاری backup در دسترس نیست.') from exc
    if completed.returncode != 0:
        temporary_path.unlink(missing_ok=True)
        raise BackupError(f'رمزنگاری backup ناموفق بود: {completed.stderr[:1000]}')
    os.replace(temporary_path, encrypted_path)
    mac_path = _mac_path(encrypted_path)
    mac_path.write_text(_hmac_file(encrypted_path, key) + '\n', encoding='ascii')
    encrypted_path.chmod(0o600)
    mac_path.chmod(0o600)
    return BackupArtifact(
        path=encrypted_path,
        checksum=sha256_file(encrypted_path),
        size_bytes=encrypted_path.stat().st_size,
        created_at=timezone.now().isoformat(),
        database_engine=_database_config()['ENGINE'].rsplit('.', 1)[-1],
        encrypted=True,
    )


def decrypt_backup(path: Path, *, output_dir: Path | None = None, encryption_key: str | None = None) -> Path:
    """Decrypt an encrypted archive into a temporary local gzip path for validation/restore."""
    key = encryption_key or getattr(settings, 'BACKUP_ENCRYPTION_KEY', '')
    if not key:
        raise BackupError('BACKUP_ENCRYPTION_KEY برای رمزگشایی backup تنظیم نشده است.')
    mac_path = _mac_path(path)
    if not mac_path.exists() or not hmac.compare_digest(mac_path.read_text(encoding='ascii').strip(), _hmac_file(path, key)):
        raise BackupError('صحت نسخهٔ رمزنگاری‌شده با HMAC تأیید نشد.')
    directory = output_dir or backup_directory()
    with NamedTemporaryFile(prefix='restore-', suffix='.sql.gz', dir=directory, delete=False) as temporary:
        output_path = Path(temporary.name)
    environment = os.environ.copy()
    environment['BACKUP_ENCRYPTION_KEY'] = key
    command = [
        getattr(settings, 'BACKUP_OPENSSL_BIN', 'openssl'), 'enc', '-d', '-aes-256-cbc', '-pbkdf2', '-iter', '600000',
        '-in', str(path), '-out', str(output_path), '-pass', 'env:BACKUP_ENCRYPTION_KEY',
    ]
    try:
        completed = subprocess.run(command, env=environment, capture_output=True, text=True, timeout=300, check=False)
    except (OSError, subprocess.TimeoutExpired) as exc:
        output_path.unlink(missing_ok=True)
        raise BackupError('ابزار OpenSSL برای رمزگشایی backup در دسترس نیست.') from exc
    if completed.returncode != 0:
        output_path.unlink(missing_ok=True)
        raise BackupError('رمزگشایی backup ناموفق بود؛ کلید یا فایل را بررسی کنید.')
    try:
        output_path.chmod(0o600)
        validate_backup_archive(output_path)
    except Exception:
        output_path.unlink(missing_ok=True)
        raise
    return output_path


def upload_encrypted_backup(path: Path) -> str:
    """Copy only an already encrypted artifact to a configured remote target.

    Supported transports are `ftps`, `ftp`, and a mounted `filesystem` path, which
    is useful for testable storage mounts. Plain archives are rejected by design.
    """
    if getattr(settings, 'OFFLINE_MODE', False):
        raise BackupError('سامانه در حالت آفلاین است؛ نسخهٔ local ایجاد می‌شود اما انتقال برون‌سایتی انجام نمی‌گیرد.')
    if not path.name.endswith('.enc'):
        raise BackupError('فقط فایل رمزنگاری‌شده اجازهٔ انتقال برون‌سایتی دارد.')
    mac_path = _mac_path(path)
    if not mac_path.exists():
        raise BackupError('فایل HMAC نسخهٔ رمزنگاری‌شده موجود نیست.')
    provider = getattr(settings, 'BACKUP_REMOTE_PROVIDER', '').lower().strip()
    if provider == 'filesystem':
        destination_dir = Path(getattr(settings, 'BACKUP_REMOTE_PATH', ''))
        if not str(destination_dir):
            raise BackupError('BACKUP_REMOTE_PATH تنظیم نشده است.')
        destination_dir.mkdir(parents=True, exist_ok=True)
        destination = destination_dir / path.name
        shutil.copy2(path, destination)
        shutil.copy2(mac_path, _mac_path(destination))
        return str(destination)
    if provider not in {'ftp', 'ftps'}:
        raise BackupError('provider برون‌سایتی backup باید ftps، ftp یا filesystem باشد.')
    host = getattr(settings, 'BACKUP_REMOTE_HOST', '')
    username = getattr(settings, 'BACKUP_REMOTE_USERNAME', '')
    password = getattr(settings, 'BACKUP_REMOTE_PASSWORD', '')
    remote_dir = getattr(settings, 'BACKUP_REMOTE_PATH', '').strip('/')
    if not all([host, username, password]):
        raise BackupError('اطلاعات دسترسی remote backup کامل نیست.')
    client = FTP_TLS() if provider == 'ftps' else FTP()
    try:
        client.connect(host, int(getattr(settings, 'BACKUP_REMOTE_PORT', 21)), timeout=30)
        client.login(username, password)
        if provider == 'ftps':
            client.prot_p()
        if remote_dir:
            for segment in remote_dir.split('/'):
                try:
                    client.mkd(segment)
                except Exception:
                    pass
                client.cwd(segment)
        with path.open('rb') as stream:
            client.storbinary(f'STOR {path.name}', stream, blocksize=1024 * 1024)
        with mac_path.open('rb') as stream:
            client.storbinary(f'STOR {mac_path.name}', stream, blocksize=1024 * 1024)
    except Exception as exc:
        raise BackupError(f'انتقال remote backup ناموفق بود: {str(exc)[:500]}') from exc
    finally:
        try:
            client.quit()
        except Exception:
            client.close()
    return f'{provider}://{host}/{remote_dir}/{path.name}'


def apply_retention(*, directory: Path | None = None, keep_days: int | None = None, keep_count: int | None = None) -> list[Path]:
    """Delete old backups only when both age/count rules permit deletion; keep manifests in sync."""
    directory = directory or backup_directory()
    keep_days = int(keep_days if keep_days is not None else getattr(settings, 'BACKUP_RETENTION_DAYS', 30))
    keep_count = int(keep_count if keep_count is not None else getattr(settings, 'BACKUP_RETENTION_COUNT', 30))
    cutoff = timezone.now() - timedelta(days=max(0, keep_days))
    artifacts = sorted(
        [path for path in directory.glob('agahi-*.sql.gz*') if path.name.endswith('.sql.gz') or path.name.endswith('.sql.gz.enc')],
        key=lambda path: path.stat().st_mtime,
        reverse=True,
    )
    removed = []
    for index, path in enumerate(artifacts):
        modified = datetime.fromtimestamp(path.stat().st_mtime, tz=datetime_timezone.utc)
        if index < max(0, keep_count) or modified >= cutoff:
            continue
        path.unlink(missing_ok=True)
        path.with_suffix(path.suffix + '.json').unlink(missing_ok=True)
        _mac_path(path).unlink(missing_ok=True)
        removed.append(path)
    return removed


def restore_sqlite_backup(path: Path, *, target_database: Path) -> None:
    """Restore a validated gzip SQL archive only to an explicitly supplied SQLite target."""
    validate_backup_archive(path)
    target_database.parent.mkdir(parents=True, exist_ok=True)
    database = sqlite3.connect(target_database)
    try:
        with gzip.open(path, 'rt', encoding='utf-8') as archive:
            database.executescript(archive.read())
        database.commit()
    except sqlite3.DatabaseError as exc:
        database.rollback()
        raise BackupError(f'restore SQLite ناموفق بود: {exc}') from exc
    finally:
        database.close()

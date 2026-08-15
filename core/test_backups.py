import gzip
import os
import sqlite3
from io import StringIO
from pathlib import Path
from tempfile import TemporaryDirectory

from django.core.management import call_command
from django.test import SimpleTestCase, override_settings

from core.services.backups import (
    BackupError,
    apply_retention,
    create_database_backup,
    decrypt_backup,
    encrypt_backup,
    restore_sqlite_backup,
    sha256_file,
    upload_encrypted_backup,
    validate_backup_archive,
)


class SQLiteBackupTests(SimpleTestCase):
    @override_settings(BACKUP_ENCRYPTION_KEY='test-only-encryption-key-with-sufficient-length')
    def test_backup_encrypt_upload_restore_and_retention(self):
        with TemporaryDirectory() as temporary_directory:
            directory = Path(temporary_directory)
            artifact = create_database_backup(output_dir=directory)
            self.assertTrue(artifact.path.exists())
            self.assertTrue(artifact.path.name.endswith('.sql.gz'))
            self.assertTrue(artifact.path.with_suffix(artifact.path.suffix + '.json').exists())
            validate_backup_archive(artifact.path)
            with gzip.open(artifact.path, 'rt', encoding='utf-8') as archive:
                self.assertIn('CREATE ', archive.read(4096))

            target = directory / 'restored.sqlite3'
            restore_sqlite_backup(artifact.path, target_database=target)
            restored = sqlite3.connect(target)
            try:
                tables = {row[0] for row in restored.execute("SELECT name FROM sqlite_master WHERE type='table'")}
            finally:
                restored.close()
            self.assertIn('django_migrations', tables)

            encrypted = encrypt_backup(artifact.path)
            self.assertTrue(encrypted.path.name.endswith('.enc'))
            decrypted = decrypt_backup(encrypted.path, output_dir=directory)
            try:
                self.assertEqual(sha256_file(decrypted), sha256_file(artifact.path))
            finally:
                decrypted.unlink(missing_ok=True)

            remote_directory = directory / 'remote'
            with self.settings(BACKUP_REMOTE_PROVIDER='filesystem', BACKUP_REMOTE_PATH=str(remote_directory)):
                remote = upload_encrypted_backup(encrypted.path)
            self.assertTrue(Path(remote).exists())
            self.assertTrue(Path(f'{remote}.hmac').exists())

            os.utime(artifact.path, (1, 1))
            removed = apply_retention(directory=directory, keep_days=0, keep_count=0)
            self.assertIn(artifact.path, removed)

    @override_settings(BACKUP_ENCRYPTION_KEY='test-only-encryption-key-with-sufficient-length', OFFLINE_MODE=True)
    def test_offline_mode_refuses_remote_backup_transfer(self):
        with TemporaryDirectory() as temporary_directory:
            directory = Path(temporary_directory)
            artifact = create_database_backup(output_dir=directory)
            encrypted = encrypt_backup(artifact.path)
            remote_directory = directory / 'remote'
            with self.settings(BACKUP_REMOTE_PROVIDER='filesystem', BACKUP_REMOTE_PATH=str(remote_directory)):
                with self.assertRaisesMessage(BackupError, 'حالت آفلاین'):
                    upload_encrypted_backup(encrypted.path)
                output = StringIO()
                call_command('backup_database', output_dir=str(directory), encrypt=True, upload=True, stdout=output)
            self.assertIn('انتقال remote انجام نشد', output.getvalue())
            self.assertFalse(remote_directory.exists())

    @override_settings(BACKUP_ENCRYPTION_KEY='test-only-encryption-key-with-sufficient-length')
    def test_backup_and_restore_management_commands(self):
        with TemporaryDirectory() as temporary_directory:
            directory = Path(temporary_directory)
            remote_directory = directory / 'remote'
            with self.settings(BACKUP_REMOTE_PROVIDER='filesystem', BACKUP_REMOTE_PATH=str(remote_directory)):
                output = StringIO()
                call_command('backup_database', output_dir=str(directory), encrypt=True, upload=True, stdout=output)
            encrypted_files = list(directory.glob('agahi-*.sql.gz.enc'))
            self.assertEqual(len(encrypted_files), 1)
            self.assertTrue(remote_directory.joinpath(encrypted_files[0].name).exists())
            self.assertTrue(remote_directory.joinpath(f'{encrypted_files[0].name}.hmac').exists())

            restore_output = StringIO()
            call_command('restore_database', input=str(encrypted_files[0]), stdout=restore_output)
            self.assertIn('dry-run کامل شد', restore_output.getvalue())

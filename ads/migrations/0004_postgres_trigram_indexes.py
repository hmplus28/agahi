"""Add PostgreSQL trigram indexes while remaining a no-op on SQLite."""
from django.db import DatabaseError, migrations


TITLE_INDEX = 'ads_ad_normalized_title_trgm_idx'
DESCRIPTION_INDEX = 'ads_ad_normalized_description_trgm_idx'


def create_trigram_indexes(apps, schema_editor):
    if schema_editor.connection.vendor != 'postgresql':
        return
    try:
        with schema_editor.connection.cursor() as cursor:
            cursor.execute('CREATE EXTENSION IF NOT EXISTS pg_trgm')
            cursor.execute(
                f'CREATE INDEX IF NOT EXISTS {TITLE_INDEX} '
                'ON ads_ad USING GIN (normalized_title gin_trgm_ops)'
            )
            cursor.execute(
                f'CREATE INDEX IF NOT EXISTS {DESCRIPTION_INDEX} '
                'ON ads_ad USING GIN (normalized_description gin_trgm_ops)'
            )
    except DatabaseError:
        # Some shared hosts disallow CREATE EXTENSION. The portable search
        # fallback remains active and deployment must not be blocked.
        return


def drop_trigram_indexes(apps, schema_editor):
    if schema_editor.connection.vendor != 'postgresql':
        return
    try:
        with schema_editor.connection.cursor() as cursor:
            cursor.execute(f'DROP INDEX IF EXISTS {TITLE_INDEX}')
            cursor.execute(f'DROP INDEX IF EXISTS {DESCRIPTION_INDEX}')
    except DatabaseError:
        return


class Migration(migrations.Migration):
    dependencies = [('ads', '0003_ad_ads_public_list_idx')]

    operations = [migrations.RunPython(create_trigram_indexes, drop_trigram_indexes)]

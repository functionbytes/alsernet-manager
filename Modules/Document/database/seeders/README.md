# Document Module Seeders

This directory contains all database seeders for the Document module.

## Seeders Overview

### Data Configuration Seeders
- `DocumentTypeSeeder.php` - Document types (Invoice, Report, etc.)
- `DocumentStatusSeeder.php` - Document statuses (Draft, Approved, etc.)
- `DocumentStatusTransitionSeeder.php` - Allowed status transitions
- `DocumentGroupSeeder.php` - Document grouping categories
- `DocumentSourceSeeder.php` - Document sources (API, Upload, etc.)
- `DocumentLoadSeeder.php` - Document load/batch configurations
- `DocumentUploadTypeSeeder.php` - Upload type configurations
- `DocumentValidationConditionSeeder.php` - Validation rules and conditions
- `DocumentValidatorGroupSeeder.php` - Validator group definitions
- `DocumentValidatorGroupConfigurationSeeder.php` - Validator configurations
- `DocumentConfigurationSeeder.php` - General document settings
- `DocumentSettingsSeeder.php` - Advanced document settings
- `DocumentEmailTemplateSeeder.php` - Email templates for documents (complete templates with translations)
- `MigrateDocumentTemplatesSeeder.php` - Legacy/migration document templates from Mailer module
- `DocumentStageEmailActionSeeder.php` - Email actions for document stages
- `DocumentSyncSeeder.php` - Synchronization settings

### Permissions & Access Control
- `CreateDocumentPermissionsSeeder.php` - **Spatie permission definitions and role assignments**

## Quick Start

### Run All Document Seeders
```bash
php artisan db:seed --class=Database\\Seeders\\DatabaseSeeder
```

### Run Only Document Permissions
```bash
php artisan db:seed --class=Database\\Seeders\\Documents\\DocumentPermissionsSeeder
```

### Fresh Migration with All Seeders
```bash
php artisan migrate:fresh --seed
```

## Adding New Document Seeders

1. Create a new seeder file in this directory
2. Follow the naming convention: `Document{Feature}Seeder.php`
3. Add to `DatabaseSeeder.php` call list if needed:

```php
public function run(): void
{
    $this->call([
        Documents\CreateDocumentPermissionsSeeder::class,
        Documents\DocumentTypeSeeder::class,
        // ... add new seeder here
    ]);
}
```

4. Run the seeder:
```bash
php artisan db:seed --class=Database\\Seeders\\Documents\\YourNewSeeder
```

## Permission Seeder Details

The `CreateDocumentPermissionsSeeder` manages:

### Defined Permissions
- **Core**: documents.* (view, create, update, delete, approve, reject, etc.)
- **Files**: documents.files.* (create, update, delete, download)
- **Notes**: documents.notes.* (create, update, delete)
- **Types**: document_types.* (view, create, update, delete)
- **Groups**: document_groups.* (view, create, update, delete, configure)
- **Conditions**: document_conditions.* (view, create, update, delete)
- **SLA Policies**: document_sla_policies.* (view, create, update, delete)
- **Storage**: document_storage.* (view, update, test)
- **Settings**: document_settings.* (view, update, reset)
- **Blockades**: document_blockades.* (view, create, update, delete, sync)

### Role Assignments
- **super-admin** - All document permissions
- **manager** - View and configuration permissions
- **administrative** - CRUD and validation permissions

### Key Features
- **Editable & Incremental**: Easy to add new permissions
- **Idempotent**: Safe to run multiple times
- **Wildcard Support**: Bulk permission assignment with `resource.*` pattern
- **Clear Descriptions**: Human-readable permission names

## Documentation

Complete documentation available at:
- `/docs/permissions/document-permissions-seeder.md` - Seeder guide
- `/docs/permissions/document-permissions-usage-examples.md` - Code examples

## Common Tasks

### Add a New Permission
1. Edit `CreateDocumentPermissionsSeeder.php`
2. Add to `definePermissions()` method
3. Run seeder: `php artisan db:seed --class=...CreateDocumentPermissionsSeeder`

### Change Role Permissions
1. Edit `assignPermissionsToRoles()` method in `CreateDocumentPermissionsSeeder.php`
2. Clear cache: `php artisan cache:clear`
3. Run seeder: `php artisan db:seed --class=...CreateDocumentPermissionsSeeder`

### Check Current Permissions
```bash
php artisan tinker
Permission::all();
Permission::where('name', 'like', 'documents.%')->get();
```

## Notes

- Seeders run in the order defined in `DatabaseSeeder.php`
- Each seeder should be idempotent (safe to run multiple times)
- Use `firstOrCreate()` to avoid duplicate entries
- Document configuration is environment-independent
- Permissions are cached - clear with `php artisan cache:clear` if needed

## Related Modules

- Returns Module (`/database/seeders/Returns/`)
- Helpdesk Module (`/database/seeders/Helpdesk/`)
- Warehouse Module (`/database/seeders/Warehouse/`)
- Permissions Module (`/database/seeders/Permissions/`)

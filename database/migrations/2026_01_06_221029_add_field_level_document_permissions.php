<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create field-level document permissions
        $fieldPermissions = [
            'edit-document-status' => 'Editar estado del documento',
            'edit-document-source' => 'Editar origen del documento',
            'edit-document-load' => 'Editar método de carga del documento',
            'edit-document-sync' => 'Editar tipo de sincronización del documento',
            'edit-document-upload' => 'Editar tipo de subida del documento',
            'edit-document-financing' => 'Editar si el documento requiere financiación',
        ];

        foreach ($fieldPermissions as $name => $description) {
            \Spatie\Permission\Models\Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['description' => $description]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove field-level document permissions
        $fieldPermissions = [
            'edit-document-status',
            'edit-document-source',
            'edit-document-load',
            'edit-document-sync',
            'edit-document-upload',
            'edit-document-financing',
        ];

        \Spatie\Permission\Models\Permission::whereIn('name', $fieldPermissions)->delete();
    }
};

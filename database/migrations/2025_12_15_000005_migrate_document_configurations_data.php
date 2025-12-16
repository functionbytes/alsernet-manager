<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get default language (Spanish)
        $defaultLangId = DB::table('langs')
            ->where('iso_code', 'es')
            ->value('id') ?? 1;

        // Get all old configurations
        $oldConfigurations = DB::table('request_document_configurations')->get();

        foreach ($oldConfigurations as $oldConfig) {
            // Create DocumentType
            $documentTypeId = DB::table('document_types')->insertGetId([
                'uid' => Str::uuid(),
                'slug' => $oldConfig->document_type,
                'is_active' => true,
                'sort_order' => 0,
                'sla_multiplier' => $this->getSlaMultiplier($oldConfig->document_type),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create translation for DocumentType
            DB::table('document_type_translations')->insert([
                'uid' => Str::uuid(),
                'document_type_id' => $documentTypeId,
                'lang_id' => $defaultLangId,
                'label' => $oldConfig->document_type_label ?? ucfirst($oldConfig->document_type),
                'description' => null,
                'instructions' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Parse required documents from JSON
            $requiredDocs = json_decode($oldConfig->required_documents, true) ?? [];
            $sortOrder = 0;

            foreach ($requiredDocs as $key => $name) {
                // Create DocumentRequirement
                $requirementId = DB::table('document_requirements')->insertGetId([
                    'uid' => Str::uuid(),
                    'document_type_id' => $documentTypeId,
                    'key' => $key,
                    'is_required' => true,
                    'accepts_multiple' => false,
                    'max_file_size' => 10240, // 10MB default
                    'allowed_extensions' => json_encode(['pdf', 'jpg', 'jpeg', 'png']),
                    'sort_order' => $sortOrder++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create translation for DocumentRequirement
                DB::table('document_requirement_translations')->insert([
                    'uid' => Str::uuid(),
                    'document_requirement_id' => $requirementId,
                    'lang_id' => $defaultLangId,
                    'name' => $name,
                    'help_text' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all new records
        DB::table('document_requirement_translations')->truncate();
        DB::table('document_requirements')->truncate();
        DB::table('document_type_translations')->truncate();
        DB::table('document_types')->truncate();
    }

    /**
     * Get SLA multiplier based on document type
     */
    private function getSlaMultiplier(string $type): float
    {
        return match ($type) {
            'corta' => 0.75,
            'rifle' => 1.0,
            'escopeta' => 1.0,
            'dni' => 0.5,
            'general' => 1.0,
            default => 1.0,
        };
    }
};

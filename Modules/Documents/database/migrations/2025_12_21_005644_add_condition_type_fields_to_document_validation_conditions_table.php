<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Extends validation conditions to support multiple condition types:
     * - sale_type_match: Match document sale types (original behavior)
     * - model_field: Validate against model field values
     * - custom_expression: Custom validation logic/expressions
     */
    public function up(): void
    {
        Schema::table('document_validation_conditions', function (Blueprint $table) {
            $table->string('condition_type', 50)
                ->default('sale_type_match')
                ->after('key')
                ->comment('Type: sale_type_match, model_field, custom_expression');

            $table->string('model_field', 100)
                ->nullable()
                ->after('sale_types')
                ->comment('Model field to validate (for model_field type)');

            $table->json('expected_value')
                ->nullable()
                ->after('model_field')
                ->comment('Expected value(s) for model field validation');

            $table->text('validation_expression')
                ->nullable()
                ->after('expected_value')
                ->comment('Custom validation expression/logic');

            $table->index('condition_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_validation_conditions', function (Blueprint $table) {
            $table->dropIndex(['condition_type']);
            $table->dropColumn([
                'condition_type',
                'model_field',
                'expected_value',
                'validation_expression',
            ]);
        });
    }
};

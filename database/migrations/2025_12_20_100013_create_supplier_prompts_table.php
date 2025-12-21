<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_prompts', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('cascade')->comment('FK to suppliers (nullable)');
            $table->unsignedInteger('category_id')->nullable()->comment('FK to category (nullable)');
            $table->foreignId('source_id')->nullable()->constrained('supplier_sources')->onDelete('cascade')->comment('FK to supplier_sources (nullable)');
            $table->enum('scope', ['global', 'supplier', 'category', 'source'])->comment('Prompt scope');
            $table->string('label', 255)->comment('Prompt name');
            $table->text('prompt_template')->comment('Prompt content template');
            $table->string('output_language', 10)->default('es-ES')->comment('Output language: es-ES, en-GB');
            $table->string('tone', 50)->default('commercial')->comment('Tone: technical, commercial, formal');
            $table->integer('priority')->default(1)->comment('Priority (1 = highest). Determines which to use');
            $table->boolean('seo_focus')->default(false)->comment('Optimize for SEO?');
            $table->json('required_sections')->nullable()->comment('Required sections');
            $table->integer('version')->default(1)->comment('Prompt version');
            $table->boolean('is_default')->default(false)->comment('Is default prompt?');
            $table->boolean('is_active')->default(true)->comment('Active/inactive');
            $table->timestamps();

            $table->index(['supplier_id', 'category_id', 'source_id'], 'prompts_supplier_cat_src_idx');
            $table->index(['scope', 'is_active'], 'prompts_scope_active_idx');
            $table->index(['scope', 'priority'], 'prompts_scope_priority_idx');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_prompts');
    }
};

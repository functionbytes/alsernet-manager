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
        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('document_type_id');
            $table->string('key')->index();
            $table->boolean('is_required')->default(true);
            $table->boolean('accepts_multiple')->default(false);
            $table->integer('max_file_size')->nullable()->comment('In KB');
            $table->json('allowed_extensions')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['document_type_id', 'key']);

            // Add foreign key with shorter name
            $table->foreign('document_type_id', 'doc_req_type_fk')
                ->references('id')
                ->on('document_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requirements');
    }
};

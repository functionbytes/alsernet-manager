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
        Schema::create('supplier_product_images', function (Blueprint $table) {
            $table->id();

            // References
            $table->foreignId('extraction_result_id')->nullable()->constrained('supplier_extraction_results')->onDelete('cascade')->comment('FK to extraction results');
            $table->unsignedBigInteger('supplier_id')->nullable()->onDelete('cascade')->comment('FK to suppliers');
            $table->string('reference', 100)->nullable()->comment('Product reference');

            // Original Image
            $table->text('source_url')->comment('Original image URL from supplier');
            $table->string('source_hash', 64)->nullable()->comment('Hash for duplicate detection');

            // Processed Image
            $table->string('local_path', 500)->nullable()->comment('Local storage path');
            $table->string('cdn_url', 500)->nullable()->comment('CDN URL for processed image');

            // Metadata
            $table->integer('original_width')->nullable()->comment('Original image width in pixels');
            $table->integer('original_height')->nullable()->comment('Original image height in pixels');
            $table->integer('processed_width')->nullable()->comment('Processed image width in pixels');
            $table->integer('processed_height')->nullable()->comment('Processed image height in pixels');
            $table->bigInteger('file_size_bytes')->nullable()->comment('File size in bytes');
            $table->string('mime_type', 50)->nullable()->comment('Image MIME type');

            // Variants
            $table->json('variants')->nullable()->comment('Image variants: {thumb: url, medium: url, large: url}');

            // Status
            $table->enum('status', ['pending', 'downloading', 'processing', 'completed', 'failed'])->default('pending')->comment('Processing status');
            $table->text('error_message')->nullable()->comment('Error message if processing failed');

            // Quality & Position
            $table->decimal('quality_score', 5, 2)->nullable()->comment('Image quality analysis score');
            $table->boolean('is_primary')->default(false)->comment('Is this the primary product image');
            $table->integer('position')->default(0)->comment('Image position in gallery');

            // Timestamps
            $table->timestamp('downloaded_at')->nullable()->comment('When image was downloaded');
            $table->timestamp('processed_at')->nullable()->comment('When processing completed');
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('extraction_result_id');
            $table->index('supplier_id');
            $table->index(['supplier_id', 'reference'], 'images_supplier_ref_idx');
            $table->index('source_hash');
            $table->index('status');
            $table->index(['is_primary', 'position'], 'images_primary_position_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_product_images');
    }
};

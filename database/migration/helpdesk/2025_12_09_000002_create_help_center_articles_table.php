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
        Schema::connection('mysql')->create('helpdesk_helpcenter_articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('body')->nullable();
            $table->text('description')->nullable();
            $table->boolean('draft')->default(true)->index();
            $table->integer('views')->default(0);
            $table->integer('was_helpful')->default(0);
            $table->bigInteger('author_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Note: Cross-database foreign keys are not supported
            // The author_id references users.id from the main database
            // Enforce referential integrity at the application level

            // Indexes for better performance
            $table->index('author_id', 'hc_art_author_idx');
            $table->index(['draft', 'created_at'], 'hc_art_draft_created_idx');
            $table->index('views', 'hc_art_views_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_helpcenter_articles');
    }
};

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
        Schema::create('helpdesk_helpcenter_category_article', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('category_id')->unsigned();
            $table->bigInteger('article_id')->unsigned();
            $table->integer('position')->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('category_id')->references('id')->on('helpdesk_helpcenter_categories')->onDelete('cascade');
            $table->foreign('article_id')->references('id')->on('helpdesk_helpcenter_articles')->onDelete('cascade');

            // Composite index for better performance
            $table->index(['category_id', 'position'], 'hc_cat_art_cat_pos_idx');
            $table->index(['article_id'], 'hc_cat_art_article_idx');

            // Ensure unique article-category combinations
            $table->unique(['category_id', 'article_id'], 'hc_cat_art_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_helpcenter_category_article');
    }
};

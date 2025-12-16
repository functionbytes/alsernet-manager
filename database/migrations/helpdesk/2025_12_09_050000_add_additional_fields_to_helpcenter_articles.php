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
        Schema::table('helpdesk_helpcenter_articles', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('slug');
            $table->text('meta_description')->nullable()->after('description');
            $table->boolean('hide_from_structure')->default(false)->after('draft');

            // Add index for position
            $table->index('position', 'hc_art_position_idx');
        });

        // Create tags table
        Schema::create('helpdesk_helpcenter_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Create article_tag pivot table
        Schema::create('helpdesk_helpcenter_article_tag', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('article_id')->unsigned();
            $table->bigInteger('tag_id')->unsigned();
            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('helpdesk_helpcenter_articles')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('helpdesk_helpcenter_tags')->onDelete('cascade');
            $table->unique(['article_id', 'tag_id'], 'hc_art_tag_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helpdesk_helpcenter_articles', function (Blueprint $table) {
            $table->dropIndex('hc_art_position_idx');
            $table->dropColumn(['position', 'meta_description', 'hide_from_structure']);
        });

        Schema::dropIfExists('helpdesk_helpcenter_article_tag');
        Schema::dropIfExists('helpdesk_helpcenter_tags');
    }
};

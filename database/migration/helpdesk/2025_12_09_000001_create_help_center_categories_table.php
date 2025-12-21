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
        Schema::create('helpdesk_helpcenter_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('position')->default(0)->index();
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->boolean('is_section')->default(false)->index();
            $table->string('visible_to_role')->nullable();
            $table->string('managed_by_role')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key
            $table->foreign('parent_id')->references('id')->on('helpdesk_helpcenter_categories')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['parent_id', 'is_section', 'position'], 'hc_cat_parent_section_pos_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_helpcenter_categories');
    }
};

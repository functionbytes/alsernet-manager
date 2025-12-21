<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('helpdesk_ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('icon')->nullable(); // Tabler icon name (ti ti-icon)
            $table->string('color', 7)->default('#5D87FF'); // Hex color code
            $table->text('description')->nullable();
            $table->integer('order')->default(0);

            // SLA defaults for this category
            $table->unsignedBigInteger('default_sla_policy_id')->nullable();

            // Settings
            $table->boolean('active')->default(true);
            $table->boolean('is_system')->default(false); // Cannot be deleted
            $table->json('required_fields')->nullable(); // ['email', 'phone', 'order_number']
            $table->json('custom_form_fields')->nullable(); // Additional fields for this category

            $table->timestamps();

            $table->index('active');
            $table->index('order');
        });

        // Insert default categories
        DB::table('helpdesk_ticket_categories')->insert([
            [
                'name' => 'Technical Support',
                'slug' => 'technical-support',
                'icon' => 'ti-headset',
                'color' => '#5D87FF',
                'description' => 'Technical issues and questions about products or services',
                'is_system' => true,
                'active' => true,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bug Report',
                'slug' => 'bug-report',
                'icon' => 'ti-bug',
                'color' => '#FA896B',
                'description' => 'Report software bugs and technical issues',
                'is_system' => true,
                'active' => true,
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Feature Request',
                'slug' => 'feature-request',
                'icon' => 'ti-bulb',
                'color' => '#FFAE1F',
                'description' => 'Request new features or enhancements',
                'is_system' => false,
                'active' => true,
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Billing',
                'slug' => 'billing',
                'icon' => 'ti-credit-card',
                'color' => '#13DEB9',
                'description' => 'Billing, payments, and invoicing questions',
                'is_system' => false,
                'active' => true,
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General Inquiry',
                'slug' => 'general-inquiry',
                'icon' => 'ti-help',
                'color' => '#6C757D',
                'description' => 'General questions and information requests',
                'is_system' => false,
                'active' => true,
                'order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_ticket_categories');
    }
};

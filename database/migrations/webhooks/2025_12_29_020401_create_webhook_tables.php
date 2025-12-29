<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // webhook_integrations
        Schema::create('webhook_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('name');
            $table->string('status')->default('active');
            $table->string('plan')->default('basic');
            $table->integer('daily_limit')->default(10000);
            $table->json('allowed_ips')->nullable();
            $table->json('allowed_domains')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // webhook_api_keys
        Schema::create('webhook_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained('webhook_integrations')->onDelete('cascade');
            $table->string('key')->unique();
            $table->string('secret');
            $table->string('name');
            $table->json('permissions')->nullable();
            $table->integer('rate_limit_per_minute')->default(1000);
            $table->datetime('revoked_at')->nullable();
            $table->datetime('last_used_at')->nullable();
            $table->timestamps();
        });

        // webhook_event_catalog
        Schema::create('webhook_event_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('version')->default('1.0');
            $table->text('description')->nullable();
            $table->json('json_schema')->nullable();
            $table->json('example_payload')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // webhook_events
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('integration_id')->constrained('webhook_integrations')->onDelete('cascade');
            $table->string('event_key');
            $table->string('event_version')->default('1.0');
            $table->string('external_event_id')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->json('payload')->nullable();
            $table->string('payload_hash')->nullable();
            $table->datetime('received_at');
            $table->datetime('processed_at')->nullable();
            $table->timestamps();
        });

        // webhook_subscriptions
        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('integration_id')->constrained('webhook_integrations')->onDelete('cascade');
            $table->string('name');
            $table->string('url');
            $table->boolean('is_active')->default(true);
            $table->json('subscribed_events')->nullable();
            $table->string('auth_type')->default('none');
            $table->json('auth_config')->nullable();
            $table->string('signing_secret')->nullable();
            $table->integer('timeout_ms')->default(30000);
            $table->integer('max_attempts')->default(5);
            $table->json('backoff_policy')->nullable();
            $table->timestamps();
        });

        // webhook_subscription_rules
        Schema::create('webhook_subscription_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('webhook_subscriptions')->onDelete('cascade');
            $table->string('rule_type');
            $table->json('conditions')->nullable();
            $table->json('transform_template')->nullable();
            $table->timestamps();
        });

        // webhook_deliveries
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('integration_id')->constrained('webhook_integrations')->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('webhook_subscriptions')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('webhook_events')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->integer('attempt_count')->default(0);
            $table->datetime('next_retry_at')->nullable();
            $table->text('last_error')->nullable();
            $table->integer('last_http_status')->nullable();
            $table->integer('last_latency_ms')->nullable();
            $table->timestamps();
        });

        // webhook_delivery_logs
        Schema::create('webhook_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('webhook_deliveries')->onDelete('cascade');
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->integer('response_status')->nullable();
            $table->json('response_headers')->nullable();
            $table->json('response_body')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_delivery_logs');
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_subscription_rules');
        Schema::dropIfExists('webhook_subscriptions');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('webhook_event_catalog');
        Schema::dropIfExists('webhook_api_keys');
        Schema::dropIfExists('webhook_integrations');
    }
};

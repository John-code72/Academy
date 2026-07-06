<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peopleseek_source_registry', function (Blueprint $table) {
            $table->id();
            $table->string('source_key')->unique();
            $table->string('source_name');
            $table->string('source_url', 2048)->nullable();
            $table->string('country_region', 64)->nullable();
            $table->string('source_category', 64)->nullable();
            $table->string('adapter', 64);
            $table->string('access_method', 64)->default('public_directory');
            $table->string('legal_status', 32)->default('dry_run_only');
            $table->string('compliance_risk', 16)->default('medium');
            $table->unsignedInteger('rate_limit_ms')->default(3000);
            $table->text('terms_notes')->nullable();
            $table->text('robots_txt_snapshot')->nullable();
            $table->timestamp('robots_checked_at')->nullable();
            $table->timestamp('last_preflight_at')->nullable();
            $table->string('last_preflight_result', 32)->nullable();
            $table->string('priority_tier', 16)->default('Tier 2');
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('staged_interpreter_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('source_key');
            $table->string('source_record_id', 128)->nullable();
            $table->string('content_hash', 64);
            $table->json('profile');
            $table->boolean('dry_run')->default(true);
            $table->string('legal_status', 32)->nullable();
            $table->json('provenance')->nullable();
            $table->boolean('suppressed')->default(false);
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['source_key', 'content_hash']);
            $table->index(['source_key', 'source_record_id']);
            $table->foreign('source_key')
                ->references('source_key')
                ->on('peopleseek_source_registry')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staged_interpreter_profiles');
        Schema::dropIfExists('peopleseek_source_registry');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_source_documents', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('source_url', 2048)->nullable();
            $table->string('source_type', 32);
            $table->string('license', 64)->nullable();
            $table->boolean('commercial_use')->default(false);
            $table->json('departments')->nullable();
            $table->string('priority', 16)->default('medium');
            $table->string('citation', 512)->nullable();
            $table->string('raw_path', 512)->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('chunk_count')->default(0);
            $table->unsignedInteger('char_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_source_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('knowledge_source_documents')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->longText('content');
            $table->unsignedInteger('char_count')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'chunk_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_source_chunks');
        Schema::dropIfExists('knowledge_source_documents');
    }
};

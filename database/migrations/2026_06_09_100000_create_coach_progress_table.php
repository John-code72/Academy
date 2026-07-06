<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('track', 64);
            $table->unsignedSmallInteger('module_index')->default(0);
            $table->unsignedSmallInteger('step_index')->default(0);
            $table->json('completed_step_ids')->nullable();
            $table->string('status', 16)->default('active');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'track']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_progress');
    }
};

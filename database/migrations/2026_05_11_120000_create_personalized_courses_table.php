<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personalized_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('source_quiz_id')->nullable();
            $table->unsignedBigInteger('source_submission_id')->nullable();
            $table->unsignedBigInteger('source_course_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->mediumText('weak_topics')->nullable();
            $table->string('language', 16)->default('en');
            $table->string('level', 32)->nullable();
            $table->string('status', 32)->default('ready');
            $table->string('source', 32)->default('ai');
            $table->timestamps();

            $table->index('user_id');
            $table->index('source_quiz_id');
        });

        Schema::create('personalized_course_lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personalized_course_id');
            $table->integer('sort')->default(0);
            $table->string('section_title')->nullable();
            $table->string('title');
            $table->mediumText('content')->nullable();
            $table->text('key_points')->nullable();
            $table->text('practice_prompt')->nullable();
            $table->timestamps();

            $table->index('personalized_course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personalized_course_lessons');
        Schema::dropIfExists('personalized_courses');
    }
};

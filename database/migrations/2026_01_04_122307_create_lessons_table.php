<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->string('title', 255)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('course_id')->nullable();
            $table->bigInteger('section_id')->nullable();
            $table->string('lesson_type', 255)->nullable();
            $table->string('duration', 255)->nullable();
            $table->integer('total_mark')->nullable();
            $table->integer('pass_mark')->nullable();
            $table->integer('retake')->nullable();
            $table->string('lesson_src', 255)->nullable();
            $table->longText('attachment')->nullable();
            $table->string('attachment_type', 255)->nullable();
            $table->text('video_type')->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->integer('is_free')->nullable();
            $table->integer('sort')->nullable();
            $table->longText('description')->nullable();
            $table->longText('summary')->nullable();
            $table->integer('status')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lessons');
    }
}
<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWatchHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watch_histories', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('course_id')->nullable();
            $table->integer('student_id')->nullable();
            $table->longText('completed_lesson')->nullable();
            $table->string('watching_lesson_id', 255)->nullable();
            $table->integer('course_progress')->nullable();
            $table->string('completed_date', 255)->nullable();
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
        Schema::dropIfExists('watch_histories');
    }
}
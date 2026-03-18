<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWatchDurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watch_durations', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->integer('watched_student_id')->nullable();
            $table->integer('watched_course_id')->nullable();
            $table->integer('watched_lesson_id')->nullable();
            $table->integer('current_duration')->nullable();
            $table->longText('watched_counter')->nullable();
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
        Schema::dropIfExists('watch_durations');
    }
}
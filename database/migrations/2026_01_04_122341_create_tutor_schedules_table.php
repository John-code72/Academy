<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTutorSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutor_schedules', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('tutor_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('subject_id')->nullable();
            $table->string('start_time', 255)->nullable();
            $table->string('end_time', 255)->nullable();
            $table->integer('tution_type')->nullable();
            $table->integer('duration')->nullable();
            $table->longText('description')->nullable();
            $table->integer('status')->nullable();
            $table->integer('booking_id')->nullable();
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
        Schema::dropIfExists('tutor_schedules');
    }
}
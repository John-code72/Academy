<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTutorBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutor_bookings', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('invoice', 255)->nullable();
            $table->integer('schedule_id')->nullable();
            $table->integer('student_id')->nullable();
            $table->integer('tutor_id')->nullable();
            $table->string('start_time', 255)->nullable();
            $table->string('end_time', 255)->nullable();
            $table->longText('joining_data')->nullable();
            $table->double('price')->nullable();
            $table->double('admin_revenue')->nullable();
            $table->double('instructor_revenue')->nullable();
            $table->double('tax')->nullable();
            $table->string('payment_method', 255)->nullable();
            $table->text('payment_details')->nullable();
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
        Schema::dropIfExists('tutor_bookings');
    }
}
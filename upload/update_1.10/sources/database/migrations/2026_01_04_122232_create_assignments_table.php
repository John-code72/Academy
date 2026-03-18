<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->bigInteger('course_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->longText('questions')->nullable();
            $table->string('question_file', 255)->nullable();
            $table->integer('total_marks')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->string('note', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->timestamp('created_at')->nullable(false);
            $table->timestamp('updated_at')->nullable(false);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assignments');
    }
}
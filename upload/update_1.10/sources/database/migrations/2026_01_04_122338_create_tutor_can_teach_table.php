<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTutorCanTeachTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutor_can_teach', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('instructor_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('subject_id')->nullable();
            $table->longText('description')->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->string('price', 255)->nullable();
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
        Schema::dropIfExists('tutor_can_teach');
    }
}
<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->integer('section_id')->nullable();
            $table->string('duration', 255)->nullable();
            $table->integer('total_mark')->nullable();
            $table->integer('pass_mark')->nullable();
            $table->integer('drip_rule')->nullable();
            $table->longText('summary')->nullable();
            $table->longText('attempts')->nullable();
            $table->integer('sort')->nullable();
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
        Schema::dropIfExists('quizzes');
    }
}
<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamTrainingPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_training_packages', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->double('price')->nullable();
            $table->string('course_privacy', 255)->nullable();
            $table->integer('course_id')->nullable();
            $table->integer('allocation')->nullable();
            $table->string('expiry_type', 255)->nullable();
            $table->integer('start_date')->nullable();
            $table->integer('expiry_date')->nullable();
            $table->longText('features')->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->integer('pricing_type')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('team_training_packages');
    }
}
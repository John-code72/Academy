<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->string('role', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->integer('status')->nullable();
            $table->string('name', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->text('skills')->nullable();
            $table->text('facebook')->nullable();
            $table->string('twitter', 255)->nullable();
            $table->string('linkedin', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->text('about')->nullable();
            $table->longText('biography')->nullable();
            $table->longText('educations')->nullable();
            $table->string('photo', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255)->nullable();
            $table->string('remember_token', 255)->nullable();
            $table->longText('paymentkeys')->nullable();
            $table->string('video_url', 255)->nullable();
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
        Schema::dropIfExists('users');
    }
}
<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBootcampModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bootcamp_modules', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('bootcamp_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->integer('publish_date')->nullable();
            $table->integer('expiry_date')->nullable();
            $table->string('restriction', 255)->nullable();
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
        Schema::dropIfExists('bootcamp_modules');
    }
}
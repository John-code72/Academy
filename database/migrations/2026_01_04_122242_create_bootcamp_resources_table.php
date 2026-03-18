<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBootcampResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bootcamp_resources', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('module_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('upload_type', 255)->nullable();
            $table->string('file', 255)->nullable();
            $table->timestamp('created_at')->nullable(false);
            $table->timestamp('uploaded_at')->nullable(false);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bootcamp_resources');
    }
}
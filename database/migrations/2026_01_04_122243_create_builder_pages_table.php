<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuilderPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('builder_pages', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('name', 255)->nullable();
            $table->longText('html')->nullable();
            $table->string('identifier', 255)->nullable();
            $table->integer('is_permanent')->nullable();
            $table->integer('status')->nullable();
            $table->integer('edit_home_id')->nullable();
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
        Schema::dropIfExists('builder_pages');
    }
}
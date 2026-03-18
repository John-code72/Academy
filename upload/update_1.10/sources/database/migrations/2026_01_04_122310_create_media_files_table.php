<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('post_id')->nullable();
            $table->integer('story_id')->nullable();
            $table->integer('album_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('page_id')->nullable();
            $table->integer('group_id')->nullable();
            $table->integer('chat_id')->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('file_type', 255)->nullable();
            $table->string('privacy', 255)->nullable();
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
        Schema::dropIfExists('media_files');
    }
}
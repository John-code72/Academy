<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeoFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seo_fields', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->integer('course_id')->nullable();
            $table->integer('blog_id')->nullable();
            $table->integer('bootcamp_id')->nullable();
            $table->string('route', 255)->nullable();
            $table->string('name_route', 255)->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_robot')->nullable();
            $table->string('canonical_url', 255)->nullable();
            $table->string('custom_url', 255)->nullable();
            $table->longText('json_ld')->nullable();
            $table->string('og_title', 255)->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image', 255)->nullable();
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
        Schema::dropIfExists('seo_fields');
    }
}
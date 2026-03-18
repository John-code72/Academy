<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->text('short_description')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('category_id')->nullable();
            $table->string('course_type', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->string('level', 255)->nullable();
            $table->string('language', 255)->nullable();
            $table->integer('is_paid')->nullable();
            $table->integer('is_best')->nullable();
            $table->double('price')->nullable();
            $table->double('discounted_price')->nullable();
            $table->integer('discount_flag')->nullable();
            $table->integer('enable_drip_content')->nullable();
            $table->longText('drip_content_settings')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->string('banner', 255)->nullable();
            $table->string('preview', 255)->nullable();
            $table->mediumText('description')->nullable();
            $table->mediumText('requirements')->nullable();
            $table->mediumText('outcomes')->nullable();
            $table->mediumText('faqs')->nullable();
            $table->text('instructor_ids')->nullable();
            $table->integer('average_rating')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('expiry_period')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
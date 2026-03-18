<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBootcampsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bootcamps', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->integer('category_id')->nullable();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->integer('is_paid')->nullable();
            $table->double('price')->nullable();
            $table->integer('discount_flag')->nullable();
            $table->double('discounted_price')->nullable();
            $table->integer('publish_date')->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->longText('faqs')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('outcomes')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->longText('meta_description')->nullable();
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
        Schema::dropIfExists('bootcamps');
    }
}
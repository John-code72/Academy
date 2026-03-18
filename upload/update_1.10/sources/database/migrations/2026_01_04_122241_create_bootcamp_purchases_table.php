<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBootcampPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bootcamp_purchases', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('invoice', 255)->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('bootcamp_id')->nullable();
            $table->double('price')->nullable();
            $table->double('tax')->nullable();
            $table->string('payment_method', 255)->nullable();
            $table->text('payment_details')->nullable();
            $table->integer('status')->nullable();
            $table->timestamp('created_at')->nullable(false);
            $table->timestamp('updated_at')->nullable(false);
            $table->double('admin_revenue')->nullable();
            $table->double('instructor_revenue')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bootcamp_purchases');
    }
}
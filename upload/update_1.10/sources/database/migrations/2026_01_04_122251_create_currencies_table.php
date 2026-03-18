<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('name', 255)->nullable();
            $table->string('code', 255)->nullable();
            $table->string('symbol', 255)->nullable();
            $table->integer('paypal_supported')->nullable();
            $table->integer('stripe_supported')->nullable();
            $table->integer('ccavenue_supported')->nullable();
            $table->integer('iyzico_supported')->nullable();
            $table->integer('paystack_supported')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
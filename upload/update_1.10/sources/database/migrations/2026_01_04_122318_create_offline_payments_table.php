<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfflinePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offline_payments', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('item_type', 255)->nullable();
            $table->string('items', 255)->nullable();
            $table->double('tax')->nullable();
            $table->double('total_amount')->nullable();
            $table->string('coupon', 255)->nullable();
            $table->string('phone_no', 255)->nullable();
            $table->string('bank_no', 255)->nullable();
            $table->string('doc', 255)->nullable();
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
        Schema::dropIfExists('offline_payments');
    }
}
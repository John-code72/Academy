<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEbookPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ebook_purchases', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('session_id', 255)->nullable();
            $table->longText('transaction_id')->nullable();
            $table->string('invoice', 255)->nullable();
            $table->integer('ebook_id')->nullable();
            $table->string('tax', 255)->nullable();
            $table->double('amount')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('payment_type', 255)->nullable();
            $table->integer('status')->nullable();
            $table->float('admin_revenue')->nullable();
            $table->float('instructor_revenue')->nullable();
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
        Schema::dropIfExists('ebook_purchases');
    }
}
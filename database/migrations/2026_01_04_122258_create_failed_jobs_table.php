<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFailedJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->string('uuid', 255)->nullable();
            $table->text('connection')->nullable(false);
            $table->text('queue')->nullable(false);
            $table->longText('payload')->nullable(false);
            $table->longText('exception')->nullable(false);
            $table->timestamp('failed_at')->nullable(false);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failed_jobs');
    }
}
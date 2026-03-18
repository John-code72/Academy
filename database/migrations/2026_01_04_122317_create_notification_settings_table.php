<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('type', 255)->nullable();
            $table->integer('is_editable')->nullable();
            $table->string('addon_identifier', 255)->nullable();
            $table->string('user_types', 255)->nullable();
            $table->string('system_notification', 255)->nullable();
            $table->string('email_notification', 255)->nullable();
            $table->string('subject', 255)->nullable();
            $table->longText('template')->nullable();
            $table->string('setting_title', 255)->nullable();
            $table->string('setting_sub_title', 255)->nullable();
            $table->string('date_updated', 255)->nullable();
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
        Schema::dropIfExists('notification_settings');
    }
}
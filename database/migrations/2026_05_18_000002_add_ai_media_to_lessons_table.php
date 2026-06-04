<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('ai_lesson_format', 16)->nullable()->after('lesson_type');
            $table->mediumText('video_script')->nullable()->after('summary');
            $table->string('video_provider', 32)->nullable()->after('video_script');
            $table->string('video_status', 32)->nullable()->after('video_provider');
            $table->string('video_external_id', 128)->nullable()->after('video_status');
            $table->text('video_error')->nullable()->after('video_external_id');
            $table->timestamp('video_requested_at')->nullable()->after('video_error');
            $table->text('audio_url')->nullable()->after('video_requested_at');
            $table->string('audio_status', 32)->nullable()->after('audio_url');
            $table->string('audio_provider', 32)->nullable()->after('audio_status');
            $table->json('key_points')->nullable()->after('audio_provider');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn([
                'ai_lesson_format',
                'video_script',
                'video_provider',
                'video_status',
                'video_external_id',
                'video_error',
                'video_requested_at',
                'audio_url',
                'audio_status',
                'audio_provider',
                'key_points',
            ]);
        });
    }
};

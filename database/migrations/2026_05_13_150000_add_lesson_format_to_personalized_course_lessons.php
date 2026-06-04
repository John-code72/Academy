<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalized_course_lessons', function (Blueprint $table) {
            $table->string('lesson_format', 20)->default('text')->after('practice_prompt');
            $table->string('audio_url', 500)->nullable()->after('video_requested_at');
            $table->string('audio_status', 30)->nullable()->after('audio_url');
            $table->string('audio_provider', 30)->nullable()->after('audio_status');
        });
    }

    public function down(): void
    {
        Schema::table('personalized_course_lessons', function (Blueprint $table) {
            $table->dropColumn(['lesson_format', 'audio_url', 'audio_status', 'audio_provider']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalized_course_lessons', function (Blueprint $table) {
            $table->string('video_provider', 32)->nullable()->after('practice_prompt');
            $table->string('video_id', 128)->nullable()->after('video_provider');
            $table->string('video_url', 512)->nullable()->after('video_id');
            $table->string('video_embed', 512)->nullable()->after('video_url');
            $table->string('video_title', 512)->nullable()->after('video_embed');
            $table->string('video_thumbnail', 512)->nullable()->after('video_title');
            $table->string('video_query', 512)->nullable()->after('video_thumbnail');
        });
    }

    public function down(): void
    {
        Schema::table('personalized_course_lessons', function (Blueprint $table) {
            $table->dropColumn([
                'video_provider',
                'video_id',
                'video_url',
                'video_embed',
                'video_title',
                'video_thumbnail',
                'video_query',
            ]);
        });
    }
};

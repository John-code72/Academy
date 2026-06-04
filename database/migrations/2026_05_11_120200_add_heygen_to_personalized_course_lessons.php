<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalized_course_lessons', function (Blueprint $table) {
            $table->string('video_status', 32)->nullable()->after('video_query');
            $table->string('video_external_id', 128)->nullable()->after('video_status');
            $table->mediumText('video_script')->nullable()->after('video_external_id');
            $table->string('video_error', 512)->nullable()->after('video_script');
            $table->timestamp('video_requested_at')->nullable()->after('video_error');
        });
    }

    public function down(): void
    {
        Schema::table('personalized_course_lessons', function (Blueprint $table) {
            $table->dropColumn([
                'video_status',
                'video_external_id',
                'video_script',
                'video_error',
                'video_requested_at',
            ]);
        });
    }
};

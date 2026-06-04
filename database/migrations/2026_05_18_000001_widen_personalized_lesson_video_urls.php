<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE personalized_course_lessons MODIFY video_url TEXT NULL');
        DB::statement('ALTER TABLE personalized_course_lessons MODIFY video_thumbnail TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE personalized_course_lessons MODIFY video_url VARCHAR(512) NULL');
        DB::statement('ALTER TABLE personalized_course_lessons MODIFY video_thumbnail VARCHAR(512) NULL');
    }
};

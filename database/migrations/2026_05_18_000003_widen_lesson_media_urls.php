<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE lessons MODIFY thumbnail TEXT NULL');
        DB::statement('ALTER TABLE lessons MODIFY lesson_src TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE lessons MODIFY thumbnail VARCHAR(255) NULL');
        DB::statement('ALTER TABLE lessons MODIFY lesson_src VARCHAR(255) NULL');
    }
};

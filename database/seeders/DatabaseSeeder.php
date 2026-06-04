<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TestPractice3ComputerLiteracySeeder::class,
            TestPractice4AiLiteracySeeder::class,
            TestPractice5CybersecurityLiteracySeeder::class,
            TestPractice6RemoteWorkLiteracySeeder::class,
            TestPractice7ProfessionalismJudgmentSeeder::class,
            TestPractice8AttentionToDetailSeeder::class,
            TestPractice9TypingSpeedAccuracySeeder::class,
            TestPractice31InterpreterReadinessSeeder::class,
            TestPractice341SalesReadinessSeeder::class,
            TestPractice351MarketingReadinessSeeder::class,
            TestPractice361OperationsReadinessSeeder::class,
            TestPractice371LeadershipManagementReadinessSeeder::class,
            TestPractice381FinanceReadinessSeeder::class,
            TestPractice391ItCybersecurityReadinessSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@defrilex.local';
        $plain = 'DefrilexAdmin2026!';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'password' => Hash::make($plain),
                'role' => 'admin',
                'status' => 1,
                'email_verified_at' => now(),
            ]
        );

        // Accès back-office: has_permission() exige une ligne ici, sauf pour le 1er user (root).
        // NULL = accès complet (voir Common_helper::has_permission).
        Permission::updateOrCreate(
            ['admin_id' => $user->id],
            ['permissions' => null]
        );
    }
}

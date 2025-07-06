<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->init();
    }

    public function initUsers(): void
    {
        $users = [];

        // Create 10 users
        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'name'           => "User $i",
                'email'          => "user$i@example.com",
                'phone'          => '2547' . rand(10000000, 99999999),
                'password'       => Hash::make('password'),
                'balance'        => rand(1000, 10000),
                'token_balance'  => rand(20, 200),
                'lichess_link'   => "https://lichess.org/@/user$i",
                'chess_com_link' => "https://chess.com/member/user$i",
                'account_status' => 'active',
                'roles'          => json_encode($i <= 3 ? ['moderator'] : ['player']),
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        User::insert($users);
    }
}

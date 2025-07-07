<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Platform;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            [
                'id'         => 1,
                'name'       => 'Chess.com',
                'link'       => 'https://chess.com',
                'status'     => 'active',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'id'         => 2,
                'name'       => 'lichess',
                'link'       => 'https://lichess.com',
                'status'     => 'active',
                'created_at' => now()->subDay()->addSeconds(10),
                'updated_at' => now()->subDay()->addSeconds(10),
            ],
        ];

        // Use upsert to avoid duplicate keys on re-seed
        Platform::upsert($platforms, ['id'], ['name','link','status','updated_at']);
    }
}

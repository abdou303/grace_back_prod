<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypePjSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // \App\Models\TypePj::factory()->count(5)->create();
        // 1. Clear existing data if you want a fresh start (Optional)
        // DB::table('typespjs')->truncate(); 

        // 2. Fix the counter to start exactly after your last known ID
        // If your last ID is 6, we set the seed to 6.
        DB::statement("DBCC CHECKIDENT ('typespjs', RESEED, 6)");

        // 3. Now insert your new record
        DB::table('typespjs')->insert([
            'libelle' => 'نسخة من الطلب',
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Result: This new record will now naturally be ID 7.
    }
}

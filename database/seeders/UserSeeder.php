<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        /* User::factory()->create(

            [

                [
                    'name' => 'مستعمل 1',
                    'username' => 'carabat',
                    'email' => 'carabat@example.com',
                    'tribunal_id' => 92,
                ],
                [
                    'name' => ' مدير النظام',
                    'username' => 'admin',
                    'email' => 'admin@example.com',
                ]
            ]
        );*/

        $users = [
            [
                'name' => 'مستعمل 1',
                'username' => 'carabat',
                'email' => 'carabat@example.com',
                'tribunal_id' => 92,
            ],
            [
                'name' => 'مستعمل 1',
                'username' => 'cacasa',
                'email' => 'cacasa@example.com',
                'tribunal_id' => 106,
            ],
            [
                'name' => 'مدير النظام',
                'username' => 'admin',
                'email' => 'admin@example.com',

            ],
        ];

        foreach ($users as $user) {
            User::factory()->create($user);
        }
    }
}

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
                'role_id' => 1,
                'groupe_id' => 3
            ],
            [
                'name' => 'مستعمل 1',
                'username' => 'cacasa',
                'email' => 'cacasa@example.com',
                'tribunal_id' => 106,
                'role_id' => 1,
                'groupe_id' => 3
            ],
            [
                'name' => 'وزارة العدل',
                'username' => 'dapg',
                'email' => 'dapg@example.com',
                'role_id' => 3,
                'groupe_id' => 2

            ],
            [
                'name' => 'مستعمل وزارة العدل',
                'username' => 'dapg_user',
                'email' => 'dapg_user@example.com',
                'role_id' => 2,
                'groupe_id' => 3

            ],
            [
                'name' => 'مدير النظام',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'role_id' => 3,
                'groupe_id' => 1

            ],
        ];

        /*$users = [
            [
                'name' => 'TEST TRIBUNAL',
                'username' => 'testtribunal',
                'email' => 'testtribunal@example.com',
                'tribunal_id' => 92,
                'role_id' => 1,
                'groupe_id' => 3
            ],

            [
                'name' => 'TEST DAPG',
                'username' => 'testdapg',
                'email' => 'testdapg@example.com',
                'role_id' => 3,
                'groupe_id' => 2

            ],

        ];*/

        /*$users = [
            [
                'name' => 'CA AGADIR',
                'username' => 'caagadir',
                'email' => 'caagadir@example.com',
                'tribunal_id' => 128,
                'role_id' => 1,
                'groupe_id' => 3
            ],

   

        ];*/

        foreach ($users as $user) {
            User::factory()->create($user);
        }
    }
}

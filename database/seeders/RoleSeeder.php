<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /*public function run(): void
    {
        \App\Models\Role::factory()->count(3)->create();
    }*/

    /*juste pour ajouter d'autres roles" */

    public function run(): void
    {
        // 1. Activer l'insertion d'identité (spécifique à SQL Server)
        //DB::statement('SET IDENTITY_INSERT roles ON');

        $roles = [
            ['libelle' => 'TR-PARQUET', 'active' => true],
            ['libelle' => 'TR-GREFFE', 'active' => true],
            ['libelle' => 'DAPG-GRACES', 'active' => true],
            ['libelle' => 'DAPG-LC', 'active' => true],
        ];

        foreach ($roles as $roleData) {
            // Utiliser forceCreate pour insérer l'ID explicitement
            Role::forceCreate($roleData);
        }

        // 2. Désactiver l'insertion d'identité
        //DB::statement('SET IDENTITY_INSERT roles OFF');
    }
}

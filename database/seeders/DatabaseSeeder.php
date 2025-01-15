<?php

namespace Database\Seeders;

use App\Models\Partenaire;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /*User::factory()->create([
            'name' => 'Test User',
            'username' => 'مستعمل النظام',
            'email' => 'test@example.com',
        ]);*/

        $this->call([
            GarantSeeder::class,
            RoleSeeder::class,
            GroupeSeeder::class,
            PartenaireSeeder::class,
            TypeRequetteSeeder::class,
            DetenuSeeder::class,
            DossierSeeder::class,
            PeineSeeder::class,
            AffaireSeeder::class,
            DossierAffaireSeeder::class,
            DossierGarantSeeder::class,
            PeinePrisonSeeder::class,
            UserSeeder::class

        ]);
    }
}

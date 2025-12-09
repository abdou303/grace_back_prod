<?
//Code pour selectionner les roles : (tinker)

// Remplacez 'App\Models\Role' par le chemin exact de votre modèle Role si différent
$roles = App\Models\Role::all();

// Affiche les rôles récupérés
$roles;

/*********************************************************/




DB::table('roles')->insert([
   'id'=>4,
    'libelle' => 'TR-PARQUET',
    'active' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);



DB::table('roles')->insert([
   'id'=>5,
    'libelle' => 'TR-GREFFE',
    'active' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);



DB::table('roles')->insert([
   'id'=>6,
    'libelle' => 'DAPG-GRACES',
    'active' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);


DB::table('roles')->insert([
   'id'=>7,
    'libelle' => 'DAPG-LC',
    'active' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);

//code du seeder : 

public function run(): void
    {
        // 1. Activer l'insertion d'identité (spécifique à SQL Server)
        DB::statement('SET IDENTITY_INSERT roles ON');

        $roles = [
            ['id' => 4, 'libelle' => 'TR-PARQUET', 'active' => true],
            ['id' => 5, 'libelle' => 'TR-GREFFE', 'active' => true],
            ['id' => 6, 'libelle' => 'DAPG-GRACES', 'active' => true],
            ['id' => 7, 'libelle' => 'DAPG-LC', 'active' => true],
        ];

        foreach ($roles as $roleData) {
            // Utiliser forceCreate pour insérer l'ID explicitement
            Role::forceCreate($roleData);
        }

        // 2. Désactiver l'insertion d'identité
        DB::statement('SET IDENTITY_INSERT roles OFF');
    }

php artisan db:seed --class=RoleSeeder

php artisan db:seed --class=UserSeeder
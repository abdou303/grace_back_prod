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

php artisan make:migration add_fields_to_requettes_table

App\Models\Pj::query()->delete();
App\Models\Affaire::query()->delete();
App\Models\Requette::query()->delete();
App\Models\Dossier::query()->delete();


DB::statement("DBCC CHECKIDENT ('dossiers', RESEED, 0)");



/********************* 26/03/2026 ***************************/
DB::table('roles')->max('id');
DB::statement("DBCC CHECKIDENT ('roles', RESEED, 7)");
DB::table('roles')->insert([
    'libelle' => 'DAPG-BO',
    'active' => 1
]);

DB::table('roles')->insert([
    'libelle' => 'TR-LC',
    'active' => 1
]);

/***************************08/05/2026************************/
DB::unprepared(" SET IDENTITY_INSERT [dbo].[statut_requettes] ON; INSERT INTO [dbo].[statut_requettes] (id, libelle, code, active, created_at, updated_at) VALUES (5, N'لازال رائجا', 'EN-COURS', 1, GETDATE(), GETDATE()); SET IDENTITY_INSERT [dbo].[statut_requettes] OFF; ");

/**********************************************************************/
$data = [
    ['code' => 'DAPG-GET-DEMANDE', 'libelle' => 'تحميل الطلب', 'niveau' => 'DAPG'],
    ['code' => 'DAPG-ENVOYER-DEMANDE', 'libelle' => 'إرسال الطلب للنيابة العامة', 'niveau' => 'DAPG'],
    ['code' => 'DAPG-EDIT-INFOS-DOSSIER', 'libelle' => 'تعديل معلومات الملف', 'niveau' => 'DAPG'],
    ['code' => 'DAPG-JOINT-D-SP-DOSSIER', 'libelle' => 'إرفاق الطلب او الحالة الجنائية', 'niveau' => 'DAPG'],
    ['code' => 'TR-CONSULTER-REQUETTE', 'libelle' => 'الاطلاع على الاجراء', 'niveau' => 'TR'],
    ['code' => 'TR-TRAITER-REQUETTE', 'libelle' => 'معالجة الاجراء', 'niveau' => 'TR'],
    ['code' => 'TR-ENVOI-TO-GREFFE', 'libelle' => 'الاحالة على الرئاسة', 'niveau' => 'TR'],
    ['code' => 'TR-TRAITE-PAR-GREFFE', 'libelle' => 'أنجز من طرف الرئاسة', 'niveau' => 'TR'],
    ['code' => 'TR-ENVOI-TO-PARQUET', 'libelle' => 'الاحالة على ممثل النيابة العامة', 'niveau' => 'TR'],
    ['code' => 'TR-TRAITE-PAR-PARQUET', 'libelle' => 'أنجز من طرف ممثل النيابة العامة', 'niveau' => 'TR'],
    ['code' => 'TR-TERMINER-REQUETTE', 'libelle' => 'إتمام الاجراء', 'niveau' => 'TR'],
    ['code' => 'TR-OPEN-DEMANDE', 'libelle' => 'فتح الطلب', 'niveau' => 'TR'],
    ['code' => 'TR-TERMINER-DOSSIER', 'libelle' => 'اتمام الملف', 'niveau' => 'TR'],
    ['code' => 'TR-TERMINER-ET-ENVOYER-DOSSIER', 'libelle' => 'إتمام وإرسال الملف', 'niveau' => 'TR'],
    ['code' => 'DAPG-RECEVOIR-DOSSIER', 'libelle' => 'استلام الملف/ الاجراء', 'niveau' => 'DAPG'],
    ['code' => 'TR-EDIT-INFOS-DOSSIER', 'libelle' => 'تعديل معلومات الملف', 'niveau' => 'TR']
];

DB::table('operations')->insert(array_map(fn($row) => array_merge($row, ['created_at' => now(), 'updated_at' => now()]), $data));
/**********************************************************************/
DB::unprepared("
    SET IDENTITY_INSERT typespjs ON;
    INSERT INTO typespjs (id, libelle, active, created_at, updated_at) 
    VALUES (9, N'نسخة تنازل أو صلح', 1, GETDATE(), GETDATE());
    SET IDENTITY_INSERT typespjs OFF;
");
DB::unprepared("
    SET IDENTITY_INSERT typespjs ON;
    INSERT INTO typespjs (id, libelle, active, created_at, updated_at) 
    VALUES (10, N'نسخة من شهادة دراسية أو دبلوم', 1, GETDATE(), GETDATE());
    SET IDENTITY_INSERT typespjs OFF;
");
DB::unprepared("
    SET IDENTITY_INSERT typespjs ON;
    INSERT INTO typespjs (id, libelle, active, created_at, updated_at) 
    VALUES (11, N'نسخة من وصل الأداء', 1, GETDATE(), GETDATE());
    SET IDENTITY_INSERT typespjs OFF;
");
DB::unprepared("
    SET IDENTITY_INSERT typespjs ON;
    INSERT INTO typespjs (id, libelle, active, created_at, updated_at) 
    VALUES (12, N'نسخة من تقرير طبي', 1, GETDATE(), GETDATE());
    SET IDENTITY_INSERT typespjs OFF;
");
DB::unprepared("
    SET IDENTITY_INSERT typespjs ON;
    INSERT INTO typespjs (id, libelle, active, created_at, updated_at) 
    VALUES (13, N'نسخة من شهادة إدارية', 1, GETDATE(), GETDATE());
    SET IDENTITY_INSERT typespjs OFF;
");
DB::unprepared("
    SET IDENTITY_INSERT typespjs ON;
    INSERT INTO typespjs (id, libelle, active, created_at, updated_at) 
    VALUES (99, N'آخر', 1, GETDATE(), GETDATE());
    SET IDENTITY_INSERT typespjs OFF;
");
/***************************************************************************************/
APP_LOCATION=office

upload_max_filesize = 128M
post_max_size = 128M
php artisan jwt:secret

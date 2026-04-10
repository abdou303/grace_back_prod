<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. On crée la colonne en type 'timestamp' pour correspondre à created_at
        Schema::table('dossiers', function (Blueprint $table) {
            $table->timestamp('date_etat_ok')->nullable()->after('created_at');
        });

        // 2. On copie les valeurs de created_at vers date_etat_ok pour l'existant
        // DB::raw permet de dire à la base de données d'utiliser la valeur d'une autre colonne
        DB::table('dossiers')->update([
            'date_etat_ok' => DB::raw('created_at')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('date_etat_ok');
        });
    }
};

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
        Schema::table('requettes', function (Blueprint $table) {
            //
            // Champs de date (peuvent être nuls si l'information n'est pas encore disponible)
            $table->dateTime('date_etat_tribunal')->nullable();
            $table->dateTime('date_etat_greffe')->nullable();
            $table->dateTime('date_envoi_greffe')->nullable();

            // Champ de texte pour l'état du greffe
            $table->string('etat_greffe')->nullable();

            // Champs pour les IDs d'utilisateurs (clés étrangères vers la table 'users')
            // Assurez-vous que la table 'users' existe et a une colonne 'id'
            // J'utilise 'foreignId' qui est le standard moderne pour les clés étrangères.
            $table->foreignId('user_tribunal')->nullable()->constrained('users');
            $table->foreignId('user_greffe')->nullable()->constrained('users');
            $table->foreignId('user_parquet')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requettes', function (Blueprint $table) {
            //

            // Suppression des clés étrangères avant les colonnes (si vous les avez ajoutées)
            // C'est important pour éviter des erreurs lors du rollback.
            if (Schema::hasColumn('requettes', 'user_tribunal')) {
                $table->dropConstrainedForeignId('user_tribunal');
            }
            if (Schema::hasColumn('requettes', 'user_greffe')) {
                $table->dropConstrainedForeignId('user_greffe');
            }
            if (Schema::hasColumn('requettes', 'user_parquet')) {
                $table->dropConstrainedForeignId('user_parquet');
            }

            // Suppression des autres colonnes
            $table->dropColumn([
                'date_etat_tribunal',
                'etat_greffe',
                'date_etat_greffe',
                'date_envoi_greffe',
            ]);
        });
    }
};

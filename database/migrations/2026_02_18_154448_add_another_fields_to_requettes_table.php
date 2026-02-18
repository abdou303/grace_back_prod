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


            $table->dateTime('date_envoi_parquet')->nullable();
            $table->dateTime('date_etat_parquet')->nullable();

            // Champ de texte pour l'état du greffe
            $table->string('etat_parquet')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requettes', function (Blueprint $table) {
            //

            // Suppression des autres colonnes
            $table->dropColumn([
                'date_envoi_parquet',
                'date_etat_parquet',
                'etat_parquet',
            ]);
        });
    }
};

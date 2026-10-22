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
        foreach (['dossiers', 'requettes'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('etat_retour_dapg_tr', 10)->nullable();
                $t->dateTime('date_retour_dapg_tr')->nullable();
            });
        }
    }
    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        foreach (['dossiers', 'requettes'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['etat_retour_dapg_tr', 'date_retour_dapg_tr']);
            });
        }
    }
};

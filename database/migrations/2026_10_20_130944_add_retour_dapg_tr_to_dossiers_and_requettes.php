<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['dossiers', 'requettes'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->text('observation_retour_dapg_tr')->nullable();
                $t->integer('nbr_redirections_dapg_tr')->default(0);
            });
        }
    }

    public function down(): void
    {
        foreach (['dossiers', 'requettes'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['observation_retour_dapg_tr', 'nbr_redirections_dapg_tr']);
            });
        }
    }
};

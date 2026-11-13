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
                $t->boolean('redirecton_greffe_sglb_tr')->default(false);
                $t->dateTime('date_redirecton_greffe_sglb_tr', 3)->nullable();
                $t->integer('nbr_redirecton_greffe_sglb_tr')->default(1);
                $t->text('observation_redirecton_greffe_sglb_tr')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['dossiers', 'requettes'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn([
                    'redirecton_greffe_sglb_tr',
                    'date_redirecton_greffe_sglb_tr',
                    'nbr_redirecton_greffe_sglb_tr',
                    'observation_redirecton_greffe_sglb_tr',
                ]);
            });
        }
    }
};

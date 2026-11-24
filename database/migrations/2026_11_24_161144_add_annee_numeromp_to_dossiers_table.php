<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->unsignedSmallInteger('annee_numeromp')->nullable();
        });

        DB::statement("
            CREATE UNIQUE INDEX uq_dossiers_tribunal_numeromp
            ON dossiers (user_tribunal_id, numeromp)
            WHERE annee_numeromp IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX uq_dossiers_tribunal_numeromp ON dossiers");

        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('annee_numeromp');
        });
    }
};

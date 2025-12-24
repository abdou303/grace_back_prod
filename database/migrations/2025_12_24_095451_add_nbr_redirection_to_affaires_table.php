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
        Schema::table('affaires', function (Blueprint $table) {
            //

            // Ajout du champ entier avec une valeur par défaut de 1
            $table->integer('nbr_redirection')->default(1)->after('tribunal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affaires', function (Blueprint $table) {
            //
            $table->dropColumn('nbr_redirection');
        });
    }
};

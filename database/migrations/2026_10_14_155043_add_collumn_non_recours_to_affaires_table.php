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
            // Utilisateur du greffe qui a choisi "نعم" (الشهادة الضبطية) + date de la validation (TR greffe)
            $table->unsignedBigInteger('user_choix_non_recours_id')->nullable();
            $table->dateTime('date_choix_non_recours')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affaires', function (Blueprint $table) {
            $table->dropColumn(['user_choix_non_recours_id', 'date_choix_non_recours']);
        });
    }
};

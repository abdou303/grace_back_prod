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

            $table->boolean('copie_demande_envoyee')->default(false);
            $table->text('observation_redirection')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requettes', function (Blueprint $table) {
            //

            $table->dropColumn('copie_demande_envoyee');
            $table->dropColumn('observation_redirection');
        });
    }
};

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
            Schema::table('affaires', function (Blueprint $table) {
                // Radio oui / non
                $table->boolean('has_non_recours')
                    ->default(true)
                    ->after('id');

                // Champs cassation
                $table->string('numero_cassation')->nullable()->after('has_non_recours');
                $table->string('numero_envoi_cassation')->nullable()->after('numero_cassation');
                $table->date('date_envoi_cassation')->nullable()->after('numero_envoi_cassation');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affaires', function (Blueprint $table) {
            $table->dropColumn([
                'has_non_recours',
                'numero_cassation',
                'numero_envoi_cassation',
                'date_envoi_cassation',
            ]);
        });
    }
};

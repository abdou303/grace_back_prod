<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compteurs_numeromp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tribunal_id');
            $table->unsignedBigInteger('typedossier_id');
            $table->unsignedSmallInteger('annee');
            $table->unsignedInteger('dernier_numero')->default(0);
            $table->timestamps();

            $table->unique(['tribunal_id', 'typedossier_id', 'annee'], 'uq_compteur_numeromp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compteurs_numeromp');
    }
};

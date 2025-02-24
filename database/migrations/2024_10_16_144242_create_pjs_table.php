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
        Schema::create('pjs', function (Blueprint $table) {
            $table->id();
            $table->longText('contenu');
            $table->longText('observation');
            //$table->foreignId('requette_id')->constrained('requettes')->onDelete('cascade');
            $table->foreignId('typepj_id')->constrained('typespjs')->onDelete('cascade');
            $table->unsignedBigInteger('requette_id')->nullable();
            $table->foreign('requette_id')->references('id')->on('requettes');
            $table->unsignedBigInteger('affaire_id')->nullable();
            $table->foreign('affaire_id')->references('id')->on('affaires');
            $table->unsignedBigInteger('dossier_id')->nullable();
            $table->foreign('dossier_id')->references('id')->on('dossiers');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pjs');
    }
};

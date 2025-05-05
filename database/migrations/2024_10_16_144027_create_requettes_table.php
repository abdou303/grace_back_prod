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
        Schema::create('requettes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable();
            $table->dateTime('date')->nullable();
            $table->dateTime('date_importation')->nullable();
            $table->string('etat', 10)->nullable();
            $table->string('etat_tribunal', 10)->nullable();
            $table->text('contenu')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('dossier_id')->constrained('dossiers')->onDelete('cascade');
            $table->unsignedBigInteger('partenaire_id')->nullable();
            $table->foreign('partenaire_id')->references('id')->on('partenaires')->onDelete('set null');
            $table->unsignedBigInteger('tribunal_id')->nullable();
            $table->foreign('tribunal_id')->references('id')->on('tribunaux')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requettes');
    }
};

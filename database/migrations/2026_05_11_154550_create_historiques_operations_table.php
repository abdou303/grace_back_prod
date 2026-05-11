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
        Schema::create('historiques_operations', function (Blueprint $table) {
            $table->id();
            // Clés étrangères (assurez-vous que les tables parentes existent)
            $table->foreignId('dossier_id')->constrained();

            // nullable() car vous avez précisé que c'est possible
            $table->foreignId('requette_id')->nullable()->constrained();

            $table->foreignId('user_id')->constrained();
            $table->foreignId('operation_id')->constrained();
            $table->unsignedBigInteger('tribunal_id')->nullable();
            $table->foreign('tribunal_id')->references('id')->on('tribunaux')->onDelete('cascade');


            // Date de l'action
            $table->dateTime('date_action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historiques_operations');
    }
};

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
        Schema::create('requette_statut_requette', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requette_id')->constrained('requettes')->onDelete('cascade');
            $table->foreignId('statut_requette_id')->constrained('statut_requettes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requette_statut_requette');
    }
};

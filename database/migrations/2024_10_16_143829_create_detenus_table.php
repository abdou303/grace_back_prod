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
        Schema::create('detenus', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 500);
            $table->string('prenom', 500);
            $table->string('nompere', 500);
            $table->string('nommere', 500);
            $table->date('datenaissance');
            $table->string('cin', 30);
            $table->string('genre', 1)->default('M');
            $table->longText('adresse');
            $table->foreignId('profession_id')->default(101)->constrained('professions')->onDelete('cascade');
            $table->foreignId('nationalite_id')->default(158)->constrained('nationalites')->onDelete('cascade');
            $table->foreignId('ville_id')->default(219)->constrained('villes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detenus');
    }
};

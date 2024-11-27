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
        Schema::create('affaires', function (Blueprint $table) {
            $table->id();
            $table->string('numeromp', 50);
            $table->string('numero', 50);
            $table->string('code', 50);
            $table->string('annee', 50);
            $table->date('datejujement');
            $table->longText('conenujugement');
            $table->integer('nbrannees');
            $table->integer('nbrmois');
            $table->foreignId('peine_id')->constrained('peines')->onDelete('cascade');
            $table->foreignId('tribunal_id')->constrained('tribunaux')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affaires');
    }
};

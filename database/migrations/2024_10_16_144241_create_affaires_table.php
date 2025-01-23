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
            $table->string('numeromp', 50)->nullable();
            $table->string('numero', 50)->nullable();
            $table->string('code', 50)->nullable();
            $table->string('annee', 50)->nullable();
            $table->string('numeroaffaire', 50);
            $table->date('datejujement')->nullable();
            $table->longText('conenujugement')->nullable();
            $table->integer('nbrannees')->nullable();
            $table->integer('nbrmois')->nullable();
            //$table->foreignId('peine_id')->constrained('peines')->onDelete('cascade');
            $table->unsignedBigInteger('peine_id')->nullable();
            $table->foreign('peine_id')->references('id')->on('peines')->onDelete('cascade');
            // $table->foreignId('tribunal_id')->constrained('tribunaux')->onDelete('cascade');
            $table->unsignedBigInteger('tribunal_id')->nullable();
            $table->foreign('tribunal_id')->references('id')->on('tribunaux')->onDelete('cascade');


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

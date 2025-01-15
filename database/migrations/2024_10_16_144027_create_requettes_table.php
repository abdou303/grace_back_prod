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
            $table->string('numero');
            $table->date('date');
            $table->text('contenu');
            $table->text('observations');
            $table->foreignId('dossier_id')->constrained('dossiers')->onDelete('cascade');
            //$table->foreignId('partenaire_id')->constrained('partenaires')->onDelete('cascade');
            $table->unsignedBigInteger('partenaire_id')->nullable();
            $table->foreign('partenaire_id')->references('id')->on('partenaires')->onDelete('set null'); // Set null if the company is deleted
            $table->unsignedBigInteger('tribunal_id')->nullable()->after('username');
            $table->foreign('tribunal_id')->references('id')->on('tribunaux')->onDelete('set null'); // Set null if the company is deleted


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

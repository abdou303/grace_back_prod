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
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50)->nullable();
            $table->string('numeromp', 100)->nullable();
            $table->date('date_enregistrement')->nullable();
            $table->text('observation')->nullable();
            $table->integer('avis_mp')->nullable();
            $table->integer('avis_dgapr')->nullable();
            $table->integer('avis_gouverneur')->nullable();
            $table->integer('user_tribunal_id')->nullable();
            $table->string('user_tribunal_libelle', 500)->nullable();
            $table->integer('numero_detention')->default(0)->nullable();
            $table->foreignId('comportement_id')->default(4)->nullable()->constrained('comportements')->onDelete('cascade');
            $table->unsignedBigInteger('objetdemande_id')->nullable();
            $table->foreign('objetdemande_id')->references('id')->on('objetsdemandes')->onDelete('cascade');
            $table->unsignedBigInteger('sourcedemande_id')->nullable();
            $table->foreign('sourcedemande_id')->references('id')->on('sourcesdemandes')->onDelete('cascade');
            $table->unsignedBigInteger('prison_id')->nullable();
            $table->foreign('prison_id')->references('id')->on('prisons')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('typedossier_id')->constrained('typesdossiers')->onDelete('cascade');
            $table->foreignId('detenu_id')->constrained('detenus')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};

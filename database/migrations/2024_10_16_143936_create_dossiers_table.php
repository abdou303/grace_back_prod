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
            $table->string('numero', 50);
            $table->date('date_enregistrement');
            $table->text('observation');
            $table->string('avis_mp', 250);
            $table->string('avis_dgapr', 250);
            $table->string('avis_gouverneur', 250);
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

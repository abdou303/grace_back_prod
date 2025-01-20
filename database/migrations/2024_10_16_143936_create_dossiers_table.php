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
            $table->text('observation')->nullable();;
            $table->integer('avis_mp');
            $table->integer('avis_dgapr');
            $table->integer('avis_gouverneur');
            /* $table->foreignId('avis_mp')->nullable()->constrained('avis')->onDelete('cascade');     
            $table->foreignId('avis_dgapr')->nullable()->constrained('avis')->onDelete('cascade');
            $table->foreignId('avis_gouverneur')->nullable()->constrained('avis')->onDelete('cascade');
            $table->foreignId('comportement_id')->nullable()->constrained('comportements')->onDelete('cascade');
            $table->foreign('avis_mp')->references('id')->on('avis')->onDelete('set null');
            $table->foreign('avis_dgapr')->references('id')->on('avis')->onDelete('set null');
            $table->foreign('avis_gouverneur')->references('id')->on('avis')->onDelete('set null');
            $table->foreign('comportement_id')->references('id')->on('comportements')->onDelete('set null');*/
            $table->foreignId('comportement_id')->default(4)->nullable()->constrained('comportements')->onDelete('cascade');
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

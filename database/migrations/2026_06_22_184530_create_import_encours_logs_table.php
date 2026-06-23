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
        Schema::create('import_encours_logs', function (Blueprint $table) {
            $table->id();
            $table->string('nomdufichier');
            $table->timestamp('date');
            $table->enum('statut', ['EN_COURS', 'SUCCES', 'ECHEC'])->default('EN_COURS');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('tribunal_id')->nullable();
            $table->integer('nb_lignes_total')->default(0);
            $table->integer('nb_lignes_importees')->default(0);
            $table->integer('nb_lignes_ignorees')->default(0);
            $table->text('message_erreur')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_encours_logs');
    }
};

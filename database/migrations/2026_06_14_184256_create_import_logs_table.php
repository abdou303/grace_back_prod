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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('nomdufichier');
            $table->timestamp('date')->useCurrent();
            $table->string('statut')->default('EN_COURS');
            $table->integer('nb_lignes_total')->default(0);
            $table->integer('nb_lignes_importees')->default(0);
            $table->integer('nb_lignes_ignorees')->default(0);
            $table->text('message_erreur')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('tribunal_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};

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
        Schema::create('historiques_operations', function (Blueprint $table) {
            $table->id();

            // On garde un seul CASCADE (le parent principal)
            $table->foreignId('dossier_id')->constrained()->onDelete('cascade');

            // Pour tous les autres, on utilise 'no action' (ou on retire simplement onDelete)
            // SQL Server acceptera car il n'y a plus de risques de cycles automatiques
            $table->foreignId('requette_id')->nullable()->constrained()->onDelete('no action');
            $table->foreignId('user_id')->constrained()->onDelete('no action');
            $table->foreignId('operation_id')->constrained()->onDelete('no action');

            $table->unsignedBigInteger('tribunal_id')->nullable();
            $table->foreign('tribunal_id')
                ->references('id')
                ->on('tribunaux')
                ->onDelete('no action');

            $table->dateTime('date_action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historiques_operations');
    }
};

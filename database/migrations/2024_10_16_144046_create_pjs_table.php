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
        Schema::create('pjs', function (Blueprint $table) {
            $table->id();
            $table->longText('contenu');
            $table->longText('observation');
            $table->foreignId('requette_id')->constrained('requettes')->onDelete('cascade');
            $table->foreignId('typepj_id')->constrained('typespjs')->onDelete('cascade');



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pjs');
    }
};

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
        Schema::create('dossier_occasion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dossier_id');
            $table->unsignedBigInteger('occasion_id');
            $table->timestamps();

            $table->foreign('dossier_id')->references('id')->on('dossiers')->onDelete('cascade');
            $table->foreign('occasion_id')->references('id')->on('occasions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossier_occasion');
    }
};

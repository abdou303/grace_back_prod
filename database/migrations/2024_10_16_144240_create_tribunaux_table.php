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
        Schema::create('tribunaux', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 1000);
            $table->string('libelle_fr', 1000);
            $table->string('type_tribunal', 10)->nullable();
            $table->integer('ordre');
            $table->foreignId('ca_id')->constrained('cas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tribunaux');
    }
};

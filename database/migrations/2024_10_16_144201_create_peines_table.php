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
        Schema::create('peines', function (Blueprint $table) {
            $table->id();
            $table->string('numerolocal', 30);
            $table->string('numeronational', 30);
            $table->date('datedebut');
            $table->date('datefin')->nullable();
            $table->date('datesortie')->nullable();
            $table->date('dateprescription')->nullable();
            $table->longText('observation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peines');
    }
};

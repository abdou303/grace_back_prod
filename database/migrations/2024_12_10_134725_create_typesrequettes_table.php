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
        Schema::create('typesrequettes', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 500);
            $table->integer('min_pjs')->default(1);
            $table->string('code', 500)->nullable();
            $table->string('cat', 500)->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('typesrequettes');
    }
};

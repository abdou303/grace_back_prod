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
        Schema::table('requettes', function (Blueprint $table) {
            //
            $table->foreignId('typerequette_id')->constrained('typesrequettes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requettes', function (Blueprint $table) {
            $table->dropForeign(['typerequette_id']);

            //
        });
    }
};

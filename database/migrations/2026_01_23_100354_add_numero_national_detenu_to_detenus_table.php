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
        Schema::table('detenus', function (Blueprint $table) {
            //
            $table->string('numero_national_detenu')->nullable()->after('cin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detenus', function (Blueprint $table) {
            //
            $table->dropColumn('numero_national_detenu');
        });
    }
};

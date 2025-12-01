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
        Schema::table('pjs', function (Blueprint $table) {
            //
			            $table->string('openbee_url')->nullable()->after('contenu');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pjs', function (Blueprint $table) {
            //
			            $table->dropColumn('openbee_url');

        });
    }
};

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
            $table->string('tr_dapg', 10)->nullable();
            $table->dateTime('date_tr_dapg')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requettes', function (Blueprint $table) {
            //

            $table->dropColumn([
                'tr_dapg',
                'date_tr_dapg',

            ]);
        });
    }
};

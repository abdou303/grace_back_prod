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
        Schema::table('dossiers', function (Blueprint $table) {

            $table->unsignedBigInteger('categoriedossiers_id')->nullable();
            $table->foreign('categoriedossiers_id')->references('id')->on('categoriesdossiers')->onDelete('cascade');

            //$table->foreignId('categoriedossiers_id')->constrained('categoriesdossiers')->onDelete('cascade');
            $table->foreignId('naturedossiers_id')->constrained('naturesdossiers')->onDelete('cascade');

            $table->unsignedBigInteger('typemotifdossiers_id')->nullable();
            $table->foreign('typemotifdossiers_id')->references('id')->on('typesmotifsdossiers')->onDelete('cascade');
            // $table->foreignId('typemotifdossiers_id')->constrained('typesmotifsdossiers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            //
            // Drop the foreign key and column
            $table->dropForeign(['categoriedossiers_id']);
            $table->dropColumn('categoriedossiers_id');
            $table->dropForeign(['naturedossiers_id']);
            $table->dropColumn('naturedossiers_id');
            $table->dropForeign(['typemotifdossiers_id']);
            $table->dropColumn('typemotifdossiers_id');
        });
    }
};

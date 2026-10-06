<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('choix_non_recours', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('affaires', function (Blueprint $table) {
            $table->boolean('has_choix_non_recours')->default(false);
            $table->foreignId('choix_non_recours_id')->nullable()->constrained('choix_non_recours');
            $table->text('observation_non_recours')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('affaires', function (Blueprint $table) {
            $table->dropForeign(['choix_non_recours_id']);
            $table->dropColumn(['has_choix_non_recours', 'choix_non_recours_id', 'observation_non_recours']);
        });
        Schema::dropIfExists('choix_non_recours');
    }
};

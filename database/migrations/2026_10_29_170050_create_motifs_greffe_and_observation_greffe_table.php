<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motifs_greffe', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('code')->unique();
            $table->timestamps();
        });

        // Ajoutez ici vos autres motifs (libelle + code unique)
        DB::table('motifs_greffe')->insert([
            ['libelle' => 'آخر', 'code' => 'AUTRE', 'created_at' => now(), 'updated_at' => now()],
        ]);

        foreach (['dossiers', 'requettes'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->foreignId('motif_greffe_id')->nullable()->constrained('motifs_greffe');
                $table->text('observation_greffe')->nullable();
                $table->dateTime('date_observation_greffe', 3)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['dossiers', 'requettes'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropForeign(['motif_greffe_id']);
                $table->dropColumn(['motif_greffe_id', 'observation_greffe', 'date_observation_greffe']);
            });
        }
        Schema::dropIfExists('motifs_greffe');
    }
};

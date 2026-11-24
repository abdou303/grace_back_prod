<?php

use App\Models\TypeDossier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = (new TypeDossier)->getTable();

        Schema::table($table, function (Blueprint $t) {
            $t->string('libelle_mp', 50)->nullable();
        });

        DB::table($table)->where('id', 1)->update(['libelle_mp' => 'عفو']);
        DB::table($table)->where('id', 2)->update(['libelle_mp' => 'إ م']);
    }

    public function down(): void
    {
        $table = (new TypeDossier)->getTable();

        Schema::table($table, function (Blueprint $t) {
            $t->dropColumn('libelle_mp');
        });
    }
};

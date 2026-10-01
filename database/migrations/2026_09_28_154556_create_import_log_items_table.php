<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_log_items', function (Blueprint $table) {
            $table->id();
            $table->string('import_type', 20);            // CLASSIQUE | ENCOURS
            $table->unsignedBigInteger('import_log_id');
            $table->string('model', 50);                  // Requette | Affaire | Dossier | Detenu
            $table->unsignedBigInteger('model_id');
            $table->timestamps();

            $table->index(['import_type', 'import_log_id', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_log_items');
    }
};

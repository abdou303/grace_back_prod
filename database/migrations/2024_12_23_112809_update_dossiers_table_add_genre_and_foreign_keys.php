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
            //
            $table->string('genre', 1)->default('M'); // Replace 'some_existing_column' with the column after which you want to add 'genre'
            // Add foreign key constraints
            $table->unsignedBigInteger('typemotifdossiers_id')->nullable(); // Replace 'related_table_id' with the actual column
            $table->foreign('typemotifdossiers_id')
                ->references('id')
                ->on('type_motif_dossiers') // Replace 'related_table_name' with the referenced table name
                ->onDelete('cascade');

            $table->unsignedBigInteger('naturedossiers_id')->nullable(); // Replace 'related_table_id' with the actual column
            $table->foreign('naturedossiers_id')
                ->references('id')
                ->on('nature_dossiers') // Replace 'related_table_name' with the referenced table name
                ->onDelete('cascade');


            $table->unsignedBigInteger('categoriedossiers_id')->nullable(); // Replace 'related_table_id' with the actual column
            $table->foreign('categoriedossiers_id')
                ->references('id')
                ->on('categorie_dossiers') // Replace 'related_table_name' with the referenced table name
                ->onDelete('cascade');
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

            $table->dropColumn('genre');
        });
    }
};

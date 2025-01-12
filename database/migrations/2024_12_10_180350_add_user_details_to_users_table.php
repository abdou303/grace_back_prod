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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('groupe_id')->constrained('groupes')->onDelete('cascade');
            
            $table->unsignedBigInteger('tribunal_id')->nullable()->after('username');
                        // Foreign key constraint
                        $table->foreign('tribunal_id')
                        ->references('id')
                        ->on('tribunaux')
                        ->onDelete('set null'); // Set null if the tribunaux is deleted
                        $table->unsignedBigInteger('partenaire_id')->nullable();
                        // Foreign key constraint
                        $table->foreign('partenaire_id')
                        ->references('id')
                        ->on('partenaires')
                        ->onDelete('set null'); // Set null if the partenaires is deleted
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('username','role_id','groupe_id','tribunal_id','partenaire_id');
            $table->dropForeign(['role_id']);
            $table->dropForeign(['groupe_id']);
            $table->dropForeign(['tribunal_id']);
            $table->dropForeign(['partenaire_id']);

        });
    }
};

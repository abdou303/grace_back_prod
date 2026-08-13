<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('typespjs', function (Blueprint $table) {
            $table->boolean('active_dapg')->default(true)->after('active');
            $table->boolean('active_tr')->default(true)->after('active_dapg');
        });
    }

    public function down(): void
    {
        Schema::table('typespjs', function (Blueprint $table) {
            $table->dropColumn(['active_dapg', 'active_tr']);
        });
    }
};

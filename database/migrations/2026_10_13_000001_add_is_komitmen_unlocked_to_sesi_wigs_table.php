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
        if (!Schema::hasColumn('sesi_wigs', 'is_komitmen_unlocked')) {
            Schema::table('sesi_wigs', function (Blueprint $table) {
                $table->boolean('is_komitmen_unlocked')->default(false)->after('evaluasi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sesi_wigs', 'is_komitmen_unlocked')) {
            Schema::table('sesi_wigs', function (Blueprint $table) {
                $table->dropColumn('is_komitmen_unlocked');
            });
        }
    }
};

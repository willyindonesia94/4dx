<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('lm_id')->constrained('master_units')->nullOnDelete();
        });

        // Migrate existing realisasis unit_id from their user
        DB::statement('UPDATE realisasis r JOIN users u ON r.user_id = u.id SET r.unit_id = u.unit_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};

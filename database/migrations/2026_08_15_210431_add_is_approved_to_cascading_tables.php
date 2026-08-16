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
        Schema::table('breakdown_wigs', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('target_tahunan');
        });

        Schema::table('breakdown_lms', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('tahun');
        });

        // Set all existing data to true
        \Illuminate\Support\Facades\DB::table('breakdown_wigs')->update(['is_approved' => true]);
        \Illuminate\Support\Facades\DB::table('breakdown_lms')->update(['is_approved' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('breakdown_wigs', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });

        Schema::table('breakdown_lms', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};

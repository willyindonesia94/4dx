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
        Schema::table('breakdown_lms', function (Blueprint $table) {
            $table->integer('bulan')->nullable()->after('satuan_id');
            $table->integer('tahun')->nullable()->after('bulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('breakdown_lms', function (Blueprint $table) {
            $table->dropColumn(['bulan', 'tahun']);
        });
    }
};

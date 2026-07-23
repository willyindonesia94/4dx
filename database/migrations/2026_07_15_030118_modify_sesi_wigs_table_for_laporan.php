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
        Schema::table('sesi_wigs', function (Blueprint $table) {
            // Because rentang_waktu_mulai and rentang_waktu_selesai were added in the previous migration, we can drop them here and add the new columns.
            if (Schema::hasColumn('sesi_wigs', 'rentang_waktu_mulai')) {
                $table->dropColumn('rentang_waktu_mulai');
            }
            if (Schema::hasColumn('sesi_wigs', 'rentang_waktu_selesai')) {
                $table->dropColumn('rentang_waktu_selesai');
            }
            
            $table->foreignId('unit_id')->nullable()->constrained('master_units')->nullOnDelete();
            $table->foreignId('wig_id')->nullable()->constrained('master_wigs')->nullOnDelete();
            $table->date('periode_mulai')->nullable()->after('tanggal_rapat');
            $table->date('periode_selesai')->nullable()->after('periode_mulai');
            $table->string('tipe_sesi')->nullable()->after('periode_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_wigs', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['wig_id']);
            $table->dropColumn(['unit_id', 'wig_id', 'periode_mulai', 'periode_selesai', 'tipe_sesi']);
            
            $table->date('rentang_waktu_mulai')->nullable()->after('tanggal_rapat');
            $table->date('rentang_waktu_selesai')->nullable()->after('rentang_waktu_mulai');
        });
    }
};

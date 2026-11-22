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
        Schema::table('master_lms', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('satuan_id');
            $table->unsignedTinyInteger('bulan_mulai')->default(1)->after('is_active');
            $table->unsignedTinyInteger('bulan_nonaktif')->nullable()->after('bulan_mulai');
        });

        // Nonaktifkan LM-3 Melaksanakan Input Dokumen K3 mulai bulan November (11)
        DB::table('master_lms')
            ->where('id', 44)
            ->orWhere('judul_lm', 'like', '%Melaksanakan Input Dokumen K3%')
            ->update([
                'is_active' => false,
                'bulan_mulai' => 1,
                'bulan_nonaktif' => 11,
            ]);

        // Pembulatan maksimal 2 desimal untuk LM Penambahan Daya Tersambung (MVA) dan Smart P2TL (kWh)
        DB::statement("UPDATE breakdown_lms SET angka_target = ROUND(angka_target, 2) WHERE lm_id IN (SELECT id FROM master_lms WHERE satuan_id IN (7, 8) OR judul_lm LIKE '%Penambahan Daya%' OR judul_lm LIKE '%Smart P2TL%')");
        DB::statement("UPDATE realisasis SET angka_realisasi = ROUND(angka_realisasi, 2) WHERE lm_id IN (SELECT id FROM master_lms WHERE satuan_id IN (7, 8) OR judul_lm LIKE '%Penambahan Daya%' OR judul_lm LIKE '%Smart P2TL%')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_lms', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'bulan_mulai', 'bulan_nonaktif']);
        });
    }
};

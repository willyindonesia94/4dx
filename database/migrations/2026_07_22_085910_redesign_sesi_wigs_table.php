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
        // Drop the old data/columns by re-creating or dropping existing columns
        // Let's drop the table and recreate it entirely for the new flow.
        Schema::dropIfExists('sesi_wigs');

        Schema::create('sesi_wigs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sesi'); // e.g., "Sesi Mingguan 1 - Agustus 2026"
            $table->integer('tahun');
            $table->integer('bulan');
            $table->integer('minggu_ke')->nullable(); // 1, 2, 3, 4, 5
            $table->enum('tipe_sesi', ['Mingguan', 'Bulanan']);
            $table->date('tanggal_pelaksanaan');
            $table->json('level_terlibat')->nullable(); // e.g. ["UID", "UP3"]
            $table->text('komitmen')->nullable();
            $table->text('evaluasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_wigs');
    }
};

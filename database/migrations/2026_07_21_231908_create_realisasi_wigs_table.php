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
        Schema::create('realisasi_wigs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wig_id')->constrained('master_wigs')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('master_units')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('bulan');
            $table->integer('tahun');
            $table->decimal('angka_realisasi', 15, 2);
            $table->string('bukti_file')->nullable();
            $table->text('keterangan_tambahan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_wigs');
    }
};

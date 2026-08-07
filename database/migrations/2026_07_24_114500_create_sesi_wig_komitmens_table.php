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
        Schema::create('sesi_wig_komitmens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_wig_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lm_id')->constrained('master_lms')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('master_units')->cascadeOnDelete();
            
            $table->string('pic_lm')->nullable();
            
            // Kolom JSON untuk menyimpan multi-row
            $table->json('hambatans')->nullable();
            $table->json('aksi_konkrits')->nullable();
            
            $table->timestamps();
            
            // Mencegah ada duplikat form komitmen untuk sesi, lm, dan unit yang sama
            $table->unique(['sesi_wig_id', 'lm_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_wig_komitmens');
    }
};

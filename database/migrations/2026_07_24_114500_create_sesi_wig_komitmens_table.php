<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_wig_komitmens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_wig_id')->constrained('sesi_wigs')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('master_units')->cascadeOnDelete();
            $table->foreignId('lm_id')->constrained('master_lms')->cascadeOnDelete();
            $table->decimal('komitmen', 15, 2)->nullable();
            $table->decimal('carry_over', 15, 2)->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate entries for the same session, unit, and LM
            $table->unique(['sesi_wig_id', 'unit_id', 'lm_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_wig_komitmens');
    }
};

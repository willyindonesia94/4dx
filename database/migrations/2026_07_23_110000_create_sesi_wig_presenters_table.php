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
        Schema::create('sesi_wig_presenters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_wig_id')->constrained('sesi_wigs')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('master_units')->cascadeOnDelete();
            $table->timestamps();
            
            // Ensure a unit can only be recorded once per session just in case
            $table->unique(['sesi_wig_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_wig_presenters');
    }
};

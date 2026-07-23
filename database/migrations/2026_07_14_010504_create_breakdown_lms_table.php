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
        Schema::create('breakdown_lms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('master_units')->onDelete('cascade');
            $table->foreignId('lm_id')->constrained('master_lms')->onDelete('cascade');
            $table->string('bidang')->nullable();
            $table->decimal('angka_target', 15, 2);
            $table->foreignId('satuan_id')->constrained('master_satuans')->onDelete('cascade');
            $table->date('periode_start');
            $table->date('periode_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breakdown_lms');
    }
};

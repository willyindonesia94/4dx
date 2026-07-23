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
        Schema::create('breakdown_wigs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('master_units')->onDelete('cascade');
            $table->foreignId('wig_id')->constrained('master_wigs')->onDelete('cascade');
            $table->integer('tahun');
            $table->decimal('target_jan', 15, 2)->default(0);
            $table->decimal('target_feb', 15, 2)->default(0);
            $table->decimal('target_mar', 15, 2)->default(0);
            $table->decimal('target_apr', 15, 2)->default(0);
            $table->decimal('target_mei', 15, 2)->default(0);
            $table->decimal('target_jun', 15, 2)->default(0);
            $table->decimal('target_jul', 15, 2)->default(0);
            $table->decimal('target_agu', 15, 2)->default(0);
            $table->decimal('target_sep', 15, 2)->default(0);
            $table->decimal('target_okt', 15, 2)->default(0);
            $table->decimal('target_nov', 15, 2)->default(0);
            $table->decimal('target_des', 15, 2)->default(0);
            $table->decimal('target_tahunan', 15, 2)->default(0);
            $table->foreignId('satuan_id')->constrained('master_satuans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breakdown_wigs');
    }
};

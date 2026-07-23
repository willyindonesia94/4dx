<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sesi_wigs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lm_id')->constrained('master_lms')->onDelete('cascade');
            $table->date('tanggal_rapat');
            $table->text('komitmen_minggu_ini');
            $table->text('evaluasi_minggu_lalu')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('sesi_wigs');
    }
};

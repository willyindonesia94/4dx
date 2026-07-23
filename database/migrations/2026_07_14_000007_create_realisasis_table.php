<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('realisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lm_id')->constrained('master_lms')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('angka_realisasi', 15, 2);
            $table->dateTime('tanggal_input');
            $table->string('bukti_file')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('realisasis');
    }
};

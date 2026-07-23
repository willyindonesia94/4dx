<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('master_lms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wig_id')->constrained('master_wigs')->onDelete('cascade');
            $table->enum('tujuan_unit_role', ['Divisi UID', 'Divisi UP3', 'TL ULP']);
            $table->string('judul_lm');
            $table->date('periode_start');
            $table->date('periode_end');
            $table->decimal('angka_target', 15, 2);
            $table->foreignId('satuan_id')->constrained('master_satuans')->onDelete('cascade');
            $table->enum('polaritas', ['positif', 'negatif']);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('master_lms');
    }
};

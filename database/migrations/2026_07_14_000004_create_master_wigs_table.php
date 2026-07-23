<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('master_wigs', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->foreignId('unit_pemilik_id')->constrained('master_units')->onDelete('cascade');
            $table->decimal('angka_target', 15, 2);
            $table->foreignId('satuan_id')->constrained('master_satuans')->onDelete('cascade');
            $table->string('divisi');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('master_wigs');
    }
};

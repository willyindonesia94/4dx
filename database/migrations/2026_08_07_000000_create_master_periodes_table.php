<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('master_periodes', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->integer('bulan');
            $table->date('start_m1');
            $table->date('end_m1');
            $table->date('start_m2')->nullable();
            $table->date('end_m2')->nullable();
            $table->date('start_m3')->nullable();
            $table->date('end_m3')->nullable();
            $table->date('start_m4')->nullable();
            $table->date('end_m4')->nullable();
            $table->date('start_m5')->nullable();
            $table->date('end_m5')->nullable();
            $table->timestamps();

            // Kombinasi tahun dan bulan harus unik
            $table->unique(['tahun', 'bulan']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_periodes');
    }
};

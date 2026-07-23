<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            $table->text('keterangan_tambahan')->nullable()->after('bukti_file');
        });
    }

    public function down(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            $table->dropColumn('keterangan_tambahan');
        });
    }
};

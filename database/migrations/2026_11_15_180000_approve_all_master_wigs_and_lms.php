<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('master_lms')
            ->where('is_approved', false)
            ->orWhereNull('is_approved')
            ->update(['is_approved' => true]);

        DB::table('master_wigs')
            ->where('is_approved', false)
            ->orWhereNull('is_approved')
            ->update(['is_approved' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu dibalik karena semua Master WIG dan LM bersifat langsung aktif tanpa draft
    }
};

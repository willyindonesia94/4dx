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
        Schema::table('master_wigs', function (Blueprint $table) {
            $table->enum('polaritas', ['positif', 'negatif'])->default('positif')->after('satuan_id');
        });

        // Set all existing WIGs to positif
        DB::table('master_wigs')->update(['polaritas' => 'positif']);

        Schema::table('master_lms', function (Blueprint $table) {
            $table->dropColumn('polaritas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_wigs', function (Blueprint $table) {
            //
        });
    }
};

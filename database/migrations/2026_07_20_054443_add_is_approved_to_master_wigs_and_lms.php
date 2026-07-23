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
        Schema::table('master_wigs', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('divisi');
        });

        Schema::table('master_lms', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('polaritas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_wigs', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });

        Schema::table('master_lms', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};

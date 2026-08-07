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
        Schema::table('sesi_wig_komitmens', function (Blueprint $table) {
            $table->string('komitmen')->nullable()->after('pic_lm');
            $table->string('carry_over')->nullable()->after('komitmen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_wig_komitmens', function (Blueprint $table) {
            $table->dropColumn(['komitmen', 'carry_over']);
        });
    }
};

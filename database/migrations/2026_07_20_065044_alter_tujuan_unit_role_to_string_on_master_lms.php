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
        Schema::table('master_lms', function (Blueprint $table) {
            $table->string('tujuan_unit_role')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_lms', function (Blueprint $table) {
            $table->enum('tujuan_unit_role', ['Divisi UID', 'Divisi UP3', 'TL ULP'])->change();
        });
    }
};

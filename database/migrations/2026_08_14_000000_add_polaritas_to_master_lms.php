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
        Schema::table('master_lms', function (Blueprint $table) {
            // Check if column already exists just in case
            if (!Schema::hasColumn('master_lms', 'polaritas')) {
                $table->enum('polaritas', ['positif', 'negatif'])->default('positif')->after('is_approved');
            }
        });
        
        // Ensure existing data has a default value just in case
        DB::table('master_lms')->update(['polaritas' => 'positif']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_lms', function (Blueprint $table) {
            if (Schema::hasColumn('master_lms', 'polaritas')) {
                $table->dropColumn('polaritas');
            }
        });
    }
};

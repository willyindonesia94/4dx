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
            $table->boolean('is_delete_pending')->default(false)->after('is_approved');
            $table->foreignId('delete_requested_by')->nullable()->constrained('users')->nullOnDelete()->after('is_delete_pending');
            $table->text('delete_reason')->nullable()->after('delete_requested_by');
        });

        Schema::table('master_lms', function (Blueprint $table) {
            $table->boolean('is_delete_pending')->default(false)->after('is_approved');
            $table->foreignId('delete_requested_by')->nullable()->constrained('users')->nullOnDelete()->after('is_delete_pending');
            $table->text('delete_reason')->nullable()->after('delete_requested_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_wigs', function (Blueprint $table) {
            $table->dropForeign(['delete_requested_by']);
            $table->dropColumn(['is_delete_pending', 'delete_requested_by', 'delete_reason']);
        });

        Schema::table('master_lms', function (Blueprint $table) {
            $table->dropForeign(['delete_requested_by']);
            $table->dropColumn(['is_delete_pending', 'delete_requested_by', 'delete_reason']);
        });
    }
};

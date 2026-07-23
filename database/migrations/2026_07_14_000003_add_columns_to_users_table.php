<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_name')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('master_units')->onDelete('set null');
            $table->string('matrix_group_id')->nullable();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['role_name', 'unit_id', 'matrix_group_id']);
        });
    }
};

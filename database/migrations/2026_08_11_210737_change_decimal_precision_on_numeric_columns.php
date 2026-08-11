<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE master_wigs MODIFY angka_target DECIMAL(15,6)');
        DB::statement('ALTER TABLE master_lms MODIFY angka_target DECIMAL(15,6)');
        DB::statement('ALTER TABLE realisasis MODIFY angka_realisasi DECIMAL(15,6)');
        DB::statement('ALTER TABLE breakdown_lms MODIFY angka_target DECIMAL(15,6)');
        
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_jan DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_feb DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_mar DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_apr DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_mei DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_jun DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_jul DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_agu DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_sep DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_okt DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_nov DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_des DECIMAL(15,6) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_tahunan DECIMAL(15,6) DEFAULT 0');
        
        DB::statement('ALTER TABLE realisasi_wigs MODIFY angka_realisasi DECIMAL(15,6)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE master_wigs MODIFY angka_target DECIMAL(15,2)');
        DB::statement('ALTER TABLE master_lms MODIFY angka_target DECIMAL(15,2)');
        DB::statement('ALTER TABLE realisasis MODIFY angka_realisasi DECIMAL(15,2)');
        DB::statement('ALTER TABLE breakdown_lms MODIFY angka_target DECIMAL(15,2)');
        
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_jan DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_feb DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_mar DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_apr DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_mei DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_jun DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_jul DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_agu DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_sep DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_okt DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_nov DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_des DECIMAL(15,2) DEFAULT 0');
        DB::statement('ALTER TABLE breakdown_wigs MODIFY target_tahunan DECIMAL(15,2) DEFAULT 0');
        
        DB::statement('ALTER TABLE realisasi_wigs MODIFY angka_realisasi DECIMAL(15,2)');
    }
};

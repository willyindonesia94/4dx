<?php

namespace App\Imports;

use App\Models\MasterWig;
use App\Models\MasterLm;
use App\Models\MasterSatuan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class LmMassImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Header mapping based on template
                $judulWig = $row['judul_wig'] ?? null;
                $judulLm = $row['judul_lm'] ?? null;
                $roleLm = $row['role_penanggung_jawab_lm'] ?? null;
                $targetLm = $row['target_lm'] ?? null;
                $namaSatuanLm = $row['nama_satuan_lm'] ?? null;
                
                // Skip if main fields are missing
                if (!$judulWig || !$judulLm) {
                    continue;
                }

                // Find the WIG. If not found, SKIP as requested.
                $wig = MasterWig::where('judul', $judulWig)->first();
                if (!$wig) {
                    continue;
                }

                $satuanLmObj = null;
                if ($namaSatuanLm) {
                    $satuanLmObj = MasterSatuan::firstOrCreate(['name' => $namaSatuanLm]);
                }

                // Update or Create LM under this WIG
                $lm = MasterLm::where('wig_id', $wig->id)
                              ->where('judul_lm', $judulLm)
                              ->first();
                
                if ($lm) {
                    $lm->update([
                        'tujuan_unit_role' => $roleLm ?? 'Divisi UID',
                        'angka_target' => $targetLm ?? $lm->angka_target,
                        'satuan_id' => $satuanLmObj ? $satuanLmObj->id : $lm->satuan_id,
                    ]);
                } else {
                    MasterLm::create([
                        'wig_id' => $wig->id,
                        'judul_lm' => $judulLm,
                        'tujuan_unit_role' => $roleLm ?? 'Divisi UID',
                        'angka_target' => $targetLm ?? 0,
                        'satuan_id' => $satuanLmObj ? $satuanLmObj->id : null,
                        'is_approved' => false,
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

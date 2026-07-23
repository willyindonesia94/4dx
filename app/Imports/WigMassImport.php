<?php

namespace App\Imports;

use App\Models\MasterWig;
use App\Models\MasterUnit;
use App\Models\MasterSatuan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class WigMassImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Header mapping based on template
                $judulWig = $row['judul_wig'] ?? null;
                $namaUnitWig = $row['nama_unit_pemilik_wig'] ?? null;
                $divisiWig = $row['divisi_wig'] ?? null;
                $targetWig = $row['target_wig'] ?? null;
                $namaSatuanWig = $row['nama_satuan_wig'] ?? null;
                $polaritasWig = $row['polaritas_wig_positifnegatif'] ?? 'positif';
                
                // Skip if main fields are missing
                if (!$judulWig) {
                    continue;
                }

                // Resolve Units and Satuans
                $unitWig = MasterUnit::where('name', $namaUnitWig)->first();
                if (!$unitWig) {
                    $unitWig = MasterUnit::first();
                }

                $satuanWigObj = null;
                if ($namaSatuanWig) {
                    $satuanWigObj = MasterSatuan::firstOrCreate(['name' => $namaSatuanWig]);
                }

                // Update or Create WIG
                $wig = MasterWig::where('judul', $judulWig)->first();
                
                if ($wig) {
                    $wig->update([
                        'divisi' => $divisiWig,
                        'unit_pemilik_id' => $unitWig ? $unitWig->id : $wig->unit_pemilik_id,
                        'angka_target' => $targetWig ?? $wig->angka_target,
                        'satuan_id' => $satuanWigObj ? $satuanWigObj->id : $wig->satuan_id,
                        'polaritas' => strtolower($polaritasWig) === 'negatif' ? 'negatif' : 'positif',
                    ]);
                } else {
                    MasterWig::create([
                        'judul' => $judulWig,
                        'divisi' => $divisiWig,
                        'unit_pemilik_id' => $unitWig ? $unitWig->id : null,
                        'angka_target' => $targetWig ?? 0,
                        'satuan_id' => $satuanWigObj ? $satuanWigObj->id : null,
                        'polaritas' => strtolower($polaritasWig) === 'negatif' ? 'negatif' : 'positif',
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

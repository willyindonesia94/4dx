<?php

namespace App\Imports;

use App\Models\RealisasiWig;
use App\Models\MasterUnit;
use App\Models\MasterWig;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class RealisasiWigImport implements ToCollection
{
    protected $tahun;

    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function collection(Collection $rows)
    {
        $headerMap = [];
        $isHeaderFound = false;
        
        $monthsName = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];

        foreach ($rows as $rowIndex => $row) {
            // Find Headers
            if (!$isHeaderFound) {
                foreach ($row as $colIndex => $cellValue) {
                    if (!$cellValue) continue;
                    $val = strtoupper(trim($cellValue));
                    
                    if ($val === 'UNIT') $headerMap['unit'] = $colIndex;
                    if (str_contains($val, 'INDIKATOR KINERJA') || str_contains($val, 'WIG')) {
                        // Ensure we get the actual column that holds WIG titles
                        // Usually it's named 'INDIKATOR KINERJA 2026'
                        $headerMap['wig'] = $colIndex;
                    }
                    
                    foreach ($monthsName as $mIndex => $mName) {
                        if (str_contains($val, 'REALISASI ' . $mName)) {
                            $headerMap['realisasi_' . ($mIndex + 1)] = $colIndex;
                        }
                    }
                }
                
                // If we found 'unit' and 'wig' and at least 'realisasi_1', we assume headers are set.
                if (isset($headerMap['unit'], $headerMap['wig'], $headerMap['realisasi_1'])) {
                    $isHeaderFound = true;
                }
                
                continue;
            }
            
            // Process Data Rows
            $unitName = $row[$headerMap['unit']] ?? null;
            $wigTitle = $row[$headerMap['wig']] ?? null;
            
            if (!$unitName || !$wigTitle) continue;
            
            // Fuzzy Match Unit
            $unitNameClean = trim(str_ireplace('UP3', '', $unitName));
            $unit = MasterUnit::where('name', 'LIKE', '%' . $unitNameClean . '%')->where('type', 'UP3')->first();
            
            // Fuzzy Match WIG
            preg_match('/WIG[\s\-]*(\d+)/i', $wigTitle, $matches);
            if (!empty($matches[1])) {
                $wigNum = $matches[1];
                $wig = MasterWig::where('judul', 'LIKE', '%WIG ' . $wigNum . '%')->orWhere('judul', 'LIKE', '%WIG-' . $wigNum . '%')->first();
            } else {
                $wig = MasterWig::where('judul', 'LIKE', '%' . trim($wigTitle) . '%')->first();
            }
            
            if ($unit && $wig) {
                // Loop through 12 months
                for ($bulanNumeric = 1; $bulanNumeric <= 12; $bulanNumeric++) {
                    $colKey = 'realisasi_' . $bulanNumeric;
                    
                    if (isset($headerMap[$colKey])) {
                        $realisasiValue = $row[$headerMap[$colKey]];
                        if (is_numeric($realisasiValue)) {
                            RealisasiWig::updateOrCreate(
                                [
                                    'wig_id' => $wig->id,
                                    'unit_id' => $unit->id,
                                    'bulan' => $bulanNumeric,
                                    'tahun' => $this->tahun,
                                ],
                                [
                                    'angka_realisasi' => $realisasiValue,
                                    'user_id' => auth()->id() ?? 1,
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}

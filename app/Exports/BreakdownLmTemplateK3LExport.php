<?php

namespace App\Exports;

use App\Models\MasterLm;
use App\Models\MasterWig;
use App\Models\MasterUnit;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BreakdownLmTemplateK3LExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        // Untuk Template K3L, kita bypass role checking dan langsung ambil SEMUA ULP
        $availableUnits = MasterUnit::where('type', 'ULP')->orderBy('name')->get();

        if ($availableUnits->isEmpty()) {
            $availableUnits = collect([(object)['name' => 'ULP CONTOH']]);
        }

        // Ambil WIG yang relevan dengan K3L
        $k3lWigs = MasterWig::where('divisi', 'LIKE', '%K3L%')->pluck('id');
        
        $wigsQuery = MasterWig::where('is_approved', true)
            ->whereIn('id', $k3lWigs)
            ->with(['masterLms' => function($q) {
                $q->where('is_approved', true);
            }]);

        $wigs = $wigsQuery->get();
        if ($wigs->isEmpty()) {
            $wigs = MasterWig::with('masterLms')->get();
        }

        // Urutkan WIG secara berurutan (WIG 1, WIG 2, dst)
        $wigs = $wigs->sort(function($a, $b) {
            preg_match('/WIG\s*-?\s*(\d+)/i', $a->judul ?? '', $mA);
            preg_match('/WIG\s*-?\s*(\d+)/i', $b->judul ?? '', $mB);
            $numA = isset($mA[1]) ? (int)$mA[1] : 999;
            $numB = isset($mB[1]) ? (int)$mB[1] : 999;
            if ($numA === $numB) {
                return strnatcasecmp($a->judul ?? '', $b->judul ?? '');
            }
            return $numA <=> $numB;
        })->values();

        $rows = collect([]);
        $hasData = false;

        foreach ($wigs as $wig) {
            if (!$wig->masterLms || $wig->masterLms->isEmpty()) {
                continue;
            }

            // Urutkan LM secara berurutan (LM 1, LM 2, dst)
            $lms = $wig->masterLms->sort(function($a, $b) {
                preg_match('/LM\s*-?\s*(\d+)/i', $a->judul_lm ?? '', $mA);
                preg_match('/LM\s*-?\s*(\d+)/i', $b->judul_lm ?? '', $mB);
                $numA = isset($mA[1]) ? (int)$mA[1] : 999;
                $numB = isset($mB[1]) ? (int)$mB[1] : 999;
                if ($numA === $numB) {
                    return strnatcasecmp($a->judul_lm ?? '', $b->judul_lm ?? '');
                }
                return $numA <=> $numB;
            })->values();

            // Pengecualian khusus: Hapus LM 4 dan LM 5 dari WIG 1
            if (preg_match('/WIG\s*-?\s*1\b/i', $wig->judul ?? '')) {
                $lms = $lms->reject(function($lm) {
                    return preg_match('/LM\s*-?\s*(4|5)\b/i', $lm->judul_lm ?? '');
                })->values();
            }

            foreach ($lms as $lm) {
                $hasData = true;

                // Tambahkan unit-unit breakdown utama (Hanya ULP untuk K3L)
                foreach ($availableUnits as $unit) {
                    $rows->push([
                        $wig->judul ?? '1',
                        $lm->judul_lm ?? '',
                        $unit->name ?? '',
                        '', '', '', '', '', ''
                    ]);
                }
            }
        }

        if (!$hasData) {
            foreach ($availableUnits as $unit) {
                $rows->push([
                    'WIG 4 - FREQUENCY RATE ACCIDENT',
                    'LM CONTOH',
                    $unit->name ?? 'ULP CONTOH',
                    '100',
                    '20',
                    '20',
                    '20',
                    '20',
                    '20',
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'NO_WIG / WIG',
            'JUDUL_LM (INDIKATOR)',
            'NAMA UNIT',
            'TARGET BULANAN',
            'TARGET MINGGU-1',
            'TARGET MINGGU-2',
            'TARGET MINGGU-3',
            'TARGET MINGGU-4',
            'TARGET MINGGU-5',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']]
            ],
        ];
    }
}

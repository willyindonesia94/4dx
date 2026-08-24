<?php

namespace App\Exports;

use App\Models\MasterWig;
use App\Models\MasterUnit;
use App\Models\BreakdownWig;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WigMassTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        $breakdowns = BreakdownWig::with(['wig', 'unit', 'satuan'])->get();
        $wigs = MasterWig::all()->sort(function($a, $b) {
            preg_match('/WIG\s*-?\s*(\d+)/i', $a->judul ?? '', $mA);
            preg_match('/WIG\s*-?\s*(\d+)/i', $b->judul ?? '', $mB);
            $numA = isset($mA[1]) ? (int)$mA[1] : 999;
            $numB = isset($mB[1]) ? (int)$mB[1] : 999;
            if ($numA === $numB) {
                return strnatcasecmp($a->judul ?? '', $b->judul ?? '');
            }
            return $numA <=> $numB;
        })->values();

        $uidUnit = MasterUnit::where('name', 'UID Jawa Barat')->first();
        $up3Units = MasterUnit::where('type', 'UP3')->orderBy('name')->get();
        $up2dUnit = MasterUnit::where('type', 'UP2D')->first();

        $rows = collect([]);

        foreach ($wigs as $wig) {
            $no = '';
            if (preg_match('/WIG\s*(\d+)/i', $wig->judul ?? '', $matches)) {
                $no = 'WIG-' . $matches[1];
            }

            $unitsToProcess = collect([]);
            if ($uidUnit) $unitsToProcess->push($uidUnit);
            foreach ($up3Units as $up3) $unitsToProcess->push($up3);
            
            // Tambahkan UP2D hanya untuk WIG 4
            if (preg_match('/WIG\s*-?\s*4\b/i', $wig->judul ?? '') && $up2dUnit) {
                $unitsToProcess->push($up2dUnit);
            }

            foreach ($unitsToProcess as $unit) {
                // Find existing breakdown
                $b = $breakdowns->where('wig_id', $wig->id)->where('unit_id', $unit->id)->first();
                
                $satuan = $wig->satuan->name ?? '';
                if ($b && $b->satuan) {
                    $satuan = $b->satuan->name;
                }

                $rows->push([
                    'Tenaga Listrik',
                    '4DX',
                    $b ? $b->tahun : date('Y'),
                    $no,
                    '4DX',
                    $wig->judul ?? '',
                    $wig->polaritas ?? '3',
                    $satuan,
                    $unit->name,
                    $b ? $b->target_jan : 0,
                    $b ? $b->target_feb : 0,
                    $b ? $b->target_mar : 0,
                    $b ? $b->target_apr : 0,
                    $b ? $b->target_mei : 0,
                    $b ? $b->target_jun : 0,
                    $b ? $b->target_jul : 0,
                    $b ? $b->target_agu : 0,
                    $b ? $b->target_sep : 0,
                    $b ? $b->target_okt : 0,
                    $b ? $b->target_nov : 0,
                    $b ? $b->target_des : 0,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'PRIMARY', 'KM', '20', 'NO', 'TYPE', 'INDIKATOR KINERJA 2026', 'POLARITAS', 'SATUAN', 'UNIT',
            'TARGET JANUARI', 'TARGET FEBRUARI', 'TARGET MARET', 'TARGET APRIL', 'TARGET MEI', 'TARGET JUNI',
            'TARGET JULI', 'TARGET AGUSTUS', 'TARGET SEPTEMBER', 'TARGET OKTOBER', 'TARGET NOVEMBER', 'TARGET DESEMBER'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']]],
        ];
    }
}

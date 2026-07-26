<?php

namespace App\Exports;

use App\Models\MasterWig;
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
        // For the template, we can provide an empty structure or existing data.
        // As per standard templates, it's usually empty with a dummy row or populated with existing breakdown.
        // We'll export existing breakdown WIGs so the user can modify and reupload if they want.
        $breakdowns = BreakdownWig::with(['wig', 'unit', 'satuan'])->get();
        $rows = collect([]);

        foreach ($breakdowns as $b) {
            $no = '';
            if (preg_match('/WIG\s*(\d+)/i', $b->wig->judul ?? '', $matches)) {
                $no = 'WIG-' . $matches[1];
            }

            $rows->push([
                'Tenaga Listrik', // PRIMARY placeholder
                '4DX',            // KM placeholder
                $b->tahun,        // 20... -> tahun
                $no,              // NO -> e.g. WIG-1
                '4DX',            // TYPE
                $b->wig->judul ?? '', // INDIKATOR KINERJA 2026
                $b->wig->polaritas ?? '3',
                $b->satuan->name ?? '',
                $b->unit->name ?? '',
                $b->target_jan,
                $b->target_feb,
                $b->target_mar,
                $b->target_apr,
                $b->target_mei,
                $b->target_jun,
                $b->target_jul,
                $b->target_agu,
                $b->target_sep,
                $b->target_okt,
                $b->target_nov,
                $b->target_des,
            ]);
        }
        
        // Add one empty row for them to understand they can add more if empty
        if ($rows->count() === 0) {
            $rows->push([
                'Tenaga Listrik', '4DX', '2026', 'WIG-1', '4DX', 'WIG 1 - PENJUALAN', '3', 'GWh', 'UID JABAR', 
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'PRIMARY',
            'KM',
            '20',
            'NO',
            'TYPE',
            'INDIKATOR KINERJA 2026',
            'POLARITAS',
            'SATUAN',
            'UNIT',
            'TARGET JANUARI',
            'TARGET FEBRUARI',
            'TARGET MARET',
            'TARGET APRIL',
            'TARGET MEI',
            'TARGET JUNI',
            'TARGET JULI',
            'TARGET AGUSTUS',
            'TARGET SEPTEMBER',
            'TARGET OKTOBER',
            'TARGET NOVEMBER',
            'TARGET DESEMBER'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']]],
        ];
    }
}

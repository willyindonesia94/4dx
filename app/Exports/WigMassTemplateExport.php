<?php

namespace App\Exports;

use App\Models\MasterWig;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WigMassTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        $wigs = MasterWig::with(['unitPemilik', 'satuan'])->get();
        $rows = collect([]);

        foreach ($wigs as $wig) {
            $rows->push([
                $wig->judul,
                $wig->unitPemilik->name ?? '',
                $wig->divisi ?? '',
                $wig->angka_target,
                $wig->satuan->name ?? '',
                $wig->polaritas ?? 'positif'
            ]);
        }
        
        // Add one empty row for them to understand they can add more if empty
        if ($rows->count() === 0) {
            $rows->push([
                'Contoh WIG Penjualan', 'UID JABAR', 'Niaga', '1000000', 'Rupiah', 'positif'
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'JUDUL WIG',
            'NAMA UNIT PEMILIK WIG',
            'DIVISI WIG',
            'TARGET WIG',
            'NAMA SATUAN WIG',
            'POLARITAS WIG (positif/negatif)'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']]],
        ];
    }
}

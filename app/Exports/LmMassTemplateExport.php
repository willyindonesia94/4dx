<?php

namespace App\Exports;

use App\Models\MasterWig;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LmMassTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        // Get all WIGs with their LMs
        $wigs = MasterWig::with(['masterLms.satuan'])->get();
        $rows = collect([]);

        foreach ($wigs as $wig) {
            if ($wig->masterLms->count() > 0) {
                $sortedLms = $wig->masterLms->sortBy(function($lm) {
                    preg_match('/LM-?(\d+)/i', $lm->judul_lm, $m);
                    return (int)($m[1] ?? 999);
                });
                foreach ($sortedLms as $lm) {
                    $rows->push([
                        $wig->judul,
                        $lm->judul_lm,
                        $lm->tujuan_unit_role,
                        $lm->angka_target,
                        $lm->satuan->name ?? '',
                    ]);
                }
            } else {
                // If WIG has no LM, just put the WIG title so they know they can add LMs under it
                $rows->push([
                    $wig->judul,
                    '', // JUDUL LM
                    '', // ROLE PENANGGUNG JAWAB LM
                    '', // TARGET LM
                    '', // NAMA SATUAN LM
                ]);
            }
        }
        
        if ($rows->count() === 0) {
            $rows->push([
                'Contoh WIG (Harus ada di database)', 
                'Contoh LM Penjualan 1', 
                'Bidang UID', 
                '500000', 
                'Rupiah'
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'JUDUL WIG',
            'JUDUL LM',
            'ROLE PENANGGUNG JAWAB LM',
            'TARGET LM',
            'NAMA SATUAN LM'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']]],
        ];
    }
}

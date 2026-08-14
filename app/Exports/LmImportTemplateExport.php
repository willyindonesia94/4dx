<?php

namespace App\Exports;

use App\Models\MasterLm;
use App\Models\MasterUnit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LmImportTemplateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        $lms = MasterLm::with(['wig', 'satuan'])->get();
        $units = MasterUnit::all();
        $rows = collect([]);

        $periodeStart = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $periodeEnd = $periodeStart->copy()->endOfMonth();

        foreach ($lms as $lm) {
            $role = $lm->tujuan_unit_role;
            $unitType = '';
            
            if ($role === 'Bidang UID' || $role === 'Sub Bidang UID') $unitType = 'UID';
            elseif ($role === 'Bidang UP3') $unitType = 'UP3';
            elseif ($role === 'TL ULP') $unitType = 'ULP';

            $applicableUnits = $units->where('type', $unitType);

            foreach ($applicableUnits as $unit) {
                // Fetch Target for this month
                $target = \App\Models\BreakdownLm::where('lm_id', $lm->id)
                    ->where('unit_id', $unit->id)
                    ->where('periode_start', '<=', $periodeEnd->format('Y-m-d'))
                    ->where('periode_end', '>=', $periodeStart->format('Y-m-d'))
                    ->first();
                
                $angkaTarget = $target ? $target->angka_target : '';

                // Fetch Realisasis for this month
                $realisasis = \App\Models\Realisasi::where('lm_id', $lm->id)
                    ->where('unit_id', $unit->id)
                    ->whereBetween('tanggal_input', [$periodeStart->format('Y-m-d 00:00:00'), $periodeEnd->format('Y-m-d 23:59:59')])
                    ->orderBy('tanggal_input')
                    ->get();
                
                $r1 = $r2 = $r3 = $r4 = $r5 = '';
                foreach ($realisasis as $r) {
                    $day = \Carbon\Carbon::parse($r->tanggal_input)->day;
                    if ($day <= 7) $r1 = (float)$r1 + $r->angka_realisasi;
                    elseif ($day <= 14) $r2 = (float)$r2 + $r->angka_realisasi;
                    elseif ($day <= 21) $r3 = (float)$r3 + $r->angka_realisasi;
                    elseif ($day <= 28) $r4 = (float)$r4 + $r->angka_realisasi;
                    else $r5 = (float)$r5 + $r->angka_realisasi;
                }

                $totalRealisasi = (float)$r1 + (float)$r2 + (float)$r3 + (float)$r4 + (float)$r5;
                $p1 = $p2 = $p3 = $p4 = $p5 = '';
                if ($angkaTarget !== '' && $angkaTarget > 0) {
                    // Simple cumulative pencapaian logic just for template export 
                    // or just leave it empty for them to fill if it's 0
                    $calculateCapaian = function($val) use ($angkaTarget, $lm) {
                        if ($val === '') return '';
                        if (($lm->polaritas ?? 'positif') === 'negatif') {
                            $diff = $angkaTarget - $val;
                            $percentage = 100 + (($diff / $angkaTarget) * 100);
                            return round(max(0, $percentage), 2) . '%';
                        }
                        return round(($val / $angkaTarget) * 100, 2) . '%';
                    };
                    
                    if ($r1 !== '') $p1 = $calculateCapaian((float)$r1);
                    if ($r2 !== '') $p2 = $calculateCapaian((float)$r1 + (float)$r2);
                    if ($r3 !== '') $p3 = $calculateCapaian((float)$r1 + (float)$r2 + (float)$r3);
                    if ($r4 !== '') $p4 = $calculateCapaian((float)$r1 + (float)$r2 + (float)$r3 + (float)$r4);
                    if ($r5 !== '') $p5 = $calculateCapaian($totalRealisasi);
                }

                $rows->push([
                    'lm' => $lm,
                    'unit' => $unit,
                    'target' => $angkaTarget,
                    'r1' => $r1, 'r2' => $r2, 'r3' => $r3, 'r4' => $r4, 'r5' => $r5,
                    'p1' => $p1, 'p2' => $p2, 'p3' => $p3, 'p4' => $p4, 'p5' => $p5
                ]);
            }
        }

        if ($rows->isEmpty()) {
            $rows->push([
                'is_example' => true,
                'wig' => 'WIG-1',
                'lm' => 'LM-1 Melaksanakan Penambahan Daya Tersambung',
                'satuan' => 'MVA',
                'unit' => 'UID JABAR',
                'target' => '159,63',
                'r1' => '27,38', 'r2' => '27,38', 'r3' => '27,38', 'r4' => '27,38', 'r5' => '27,38',
                'p1' => '85,76%', 'p2' => '86%', 'p3' => '86%', 'p4' => '86%', 'p5' => '86%'
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        $namaBulan = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F');
        return [
            ['LAPORAN BULANAN LEAD MEASURE (LM)'],
            ['PERIODE: ' . strtoupper($namaBulan) . ' ' . $this->year],
            [
                'JUDUL WIG', 'JUDUL LM', 'SATUAN', 'UNIT', 'TARGET BULANAN', 
                'REALISASI MINGGU-1', 'REALISASI MINGGU-2', 'REALISASI MINGGU-3', 'REALISASI MINGGU-4', 'REALISASI MINGGU-5',
                'PENCAPAIAN MINGGU-1', 'PENCAPAIAN MINGGU-2', 'PENCAPAIAN MINGGU-3', 'PENCAPAIAN MINGGU-4', 'PENCAPAIAN MINGGU-5'
            ]
        ];
    }

    public function map($row): array
    {
        // If it's the example row injected manually
        if (is_array($row) && isset($row['is_example']) && $row['is_example']) {
            return [
                $row['wig'], $row['lm'], $row['satuan'], $row['unit'], $row['target'],
                $row['r1'], $row['r2'], $row['r3'], $row['r4'], $row['r5'],
                $row['p1'], $row['p2'], $row['p3'], $row['p4'], $row['p5']
            ];
        }

        $lm = $row['lm'];
        $unit = $row['unit'];

        return [
            $lm->wig->judul ?? '',
            $lm->judul_lm,
            $lm->satuan->name ?? '',
            $unit->name,
            isset($row['target']) && $row['target'] !== '' ? str_replace('.', ',', (string)$row['target']) : '', // TARGET BULANAN
            isset($row['r1']) && $row['r1'] !== '' ? str_replace('.', ',', (string)$row['r1']) : '', // REALISASI M1
            isset($row['r2']) && $row['r2'] !== '' ? str_replace('.', ',', (string)$row['r2']) : '', // REALISASI M2
            isset($row['r3']) && $row['r3'] !== '' ? str_replace('.', ',', (string)$row['r3']) : '', // REALISASI M3
            isset($row['r4']) && $row['r4'] !== '' ? str_replace('.', ',', (string)$row['r4']) : '', // REALISASI M4
            isset($row['r5']) && $row['r5'] !== '' ? str_replace('.', ',', (string)$row['r5']) : '', // REALISASI M5
            $row['p1'] ?? '', // PENCAPAIAN M1
            $row['p2'] ?? '', // PENCAPAIAN M2
            $row['p3'] ?? '', // PENCAPAIAN M3
            $row['p4'] ?? '', // PENCAPAIAN M4
            $row['p5'] ?? '', // PENCAPAIAN M5
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for the title
        $sheet->mergeCells('A1:O1');
        $sheet->mergeCells('A2:O2');
        
        return [
            1    => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            2    => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            3    => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE5E7EB']]],
        ];
    }
}

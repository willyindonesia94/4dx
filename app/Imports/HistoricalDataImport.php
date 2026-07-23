<?php

namespace App\Imports;

use App\Models\BreakdownLm;
use App\Models\Realisasi;
use App\Models\MasterLm;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class HistoricalDataImport implements ToCollection, WithHeadingRow
{
    protected $tahun;
    protected $bulan;

    public function __construct($tahun, $bulan)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
    }

    public function headingRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Read columns from template: judul_lm (or indikator_kinerja_2026), unit, target_bulanan, realisasi_minggu_1..5
                $judulLm = $row['judul_lm'] ?? $row['indikator_kinerja_2026'] ?? null;
                $namaUnit = $row['unit'] ?? null;
                $target = $row['target_bulanan'] ?? null;

                if (!$judulLm || !$namaUnit) {
                    continue; // Skip invalid rows
                }

                $periodeStart = Carbon::createFromDate($this->tahun, $this->bulan, 1)->startOfMonth();
                $periodeEnd = $periodeStart->copy()->endOfMonth();

                $lm = MasterLm::where('judul_lm', $judulLm)->first();
                
                // Fuzzy match unit
                $unit = \App\Models\MasterUnit::where('name', $namaUnit)->first();
                if (!$unit) {
                    $searchName = strtoupper(trim($namaUnit));
                    if ($searchName === 'UID JABAR') {
                        $unit = \App\Models\MasterUnit::where('name', 'UID Jawa Barat')->first();
                    } else {
                        // try UP3
                        $unit = \App\Models\MasterUnit::where('name', 'UP3 ' . ucwords(strtolower($searchName)))->first();
                        if (!$unit) {
                            // try ULP
                            $unit = \App\Models\MasterUnit::where('name', 'ULP ' . ucwords(strtolower($searchName)))->first();
                        }
                    }
                }

                if (!$lm || !$unit) {
                    // Log failed match for debugging if needed
                    \Log::info("Import Skip: LM ($judulLm) or Unit ($namaUnit) not found.");
                    continue;
                }

                // 1. Create or Update Target in breakdown_lms
                if ($target !== null && $target !== '') {
                    // Clean target (remove %, dots for thousands, change comma to dot)
                    $cleanTarget = str_replace(',', '.', str_replace('.', '', str_replace('%', '', $target)));
                    
                    BreakdownLm::updateOrCreate(
                        [
                            'lm_id' => $lm->id,
                            'unit_id' => $unit->id,
                            'periode_start' => $periodeStart->format('Y-m-d'),
                        ],
                        [
                            'angka_target' => (float) $cleanTarget,
                            'periode_end' => $periodeEnd->format('Y-m-d'),
                            'satuan_id' => $lm->satuan_id,
                            'bidang' => null
                        ]
                    );
                }

                // 2. Insert Realisasi per week
                $weeks = [
                    'realisasi_minggu_1' => 7,
                    'realisasi_minggu_2' => 14,
                    'realisasi_minggu_3' => 21,
                    'realisasi_minggu_4' => 28,
                    'realisasi_minggu_5' => $periodeEnd->day // Last day of month
                ];

                foreach ($weeks as $col => $day) {
                    $realisasiVal = $row[$col] ?? null;
                    if ($realisasiVal !== null && $realisasiVal !== '') {
                        $cleanRealisasi = str_replace(',', '.', str_replace('.', '', str_replace('%', '', $realisasiVal)));
                        $tanggalInput = Carbon::createFromDate($this->tahun, $this->bulan, min($day, $periodeEnd->day))->setHour(12);

                        Realisasi::updateOrCreate(
                            [
                                'lm_id' => $lm->id,
                                'unit_id' => $unit->id,
                                'tanggal_input' => $tanggalInput->format('Y-m-d H:i:s')
                            ],
                            [
                                'user_id' => auth()->id() ?? 1, // Recorded by admin
                                'angka_realisasi' => (float) $cleanRealisasi,
                                'keterangan_tambahan' => 'Imported Historical Data'
                            ]
                        );
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

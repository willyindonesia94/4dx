<?php

namespace App\Imports;

use App\Models\MasterLm;
use App\Models\MasterUnit;
use App\Models\Realisasi;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class RealisasiLmFormatBidangImport implements ToCollection
{
    protected $bulanImport;
    protected $tahunImport;

    // Pemetaan nama minggu ke offset tanggal awal bulan
    // Minggu-1 = tanggal 1, Minggu-2 = tanggal 8, dll.
    protected $mingguTanggal = [1 => 1, 2 => 8, 3 => 15, 4 => 22, 5 => 29];

    public function __construct($bulanImport = null, $tahunImport = null)
    {
        $this->bulanImport = $bulanImport ?? (int) date('n');
        $this->tahunImport = $tahunImport ?? (int) date('Y');
    }

    public function collection(Collection $rows)
    {
        // Cari baris header (yang mengandung "INDIKATOR KINERJA" atau "UNIT" dan "REALISASI")
        $headerRow = null;
        $headerIndex = null;

        foreach ($rows as $i => $row) {
            $rowStr = strtoupper(implode(',', $row->toArray()));
            if (str_contains($rowStr, 'INDIKATOR KINERJA') || str_contains($rowStr, 'REALISASI MINGGU')) {
                $headerRow = $row;
                $headerIndex = $i;
                break;
            }
        }

        if (!$headerRow) return;

        // Peta kolom header
        $colUnit = null;
        $colLm = null;
        $colRealisasi = []; // [minggu_ke => col_index]

        foreach ($headerRow as $colIndex => $cellValue) {
            if (!$cellValue) continue;
            $val = strtoupper(trim((string)$cellValue));

            if ($val === 'UNIT') $colUnit = $colIndex;
            if (str_contains($val, 'INDIKATOR KINERJA') || ($val !== 'UNIT' && str_contains($val, 'WIG') && str_contains($val, 'LM'))) {
                // Jika ada kolom yang namanya lebih dari "INDIKATOR KINERJA 2026"
                $colLm = $colIndex;
            }

            // Realisasi Minggu-1 s/d Minggu-5
            for ($m = 1; $m <= 5; $m++) {
                if (str_contains($val, 'REALISASI') && str_contains($val, (string)$m)) {
                    $colRealisasi[$m] = $colIndex;
                }
            }
        }

        // Fallback: cari kolom LM dari kolom bernama "INDIKATOR KINERJA"
        if ($colLm === null) {
            foreach ($headerRow as $colIndex => $cellValue) {
                if (!$cellValue) continue;
                $val = strtoupper(trim((string)$cellValue));
                if (str_contains($val, 'INDIKATOR KINERJA')) {
                    $colLm = $colIndex;
                    break;
                }
            }
        }

        if ($colUnit === null || $colLm === null || empty($colRealisasi)) return;

        // Cache master_units dan master_lms untuk performa
        $allUnits = MasterUnit::all();
        $allLms = MasterLm::all();

        // Proses baris data
        foreach ($rows as $i => $row) {
            if ($i <= $headerIndex) continue;

            $unitName = trim((string)($row[$colUnit] ?? ''));
            $lmTitle  = trim((string)($row[$colLm] ?? ''));

            if (!$unitName || !$lmTitle) continue;

            // Match unit: bersihkan "UP3" atau "UID" prefix, cari by name LIKE
            $unitNameClean = trim(preg_replace('/^(UP3|UID)\s*/i', '', $unitName));
            if (strtolower($unitNameClean) === 'jabar') {
                $unitNameClean = 'jawa barat';
            }

            $unit = $allUnits->first(function ($u) use ($unitName, $unitNameClean) {
                return stripos($u->name, $unitNameClean) !== false
                    || stripos($unitName, $u->name) !== false;
            });

            if (!$unit) continue;

            // Match LM: cari by judul_lm LIKE
            $lmClean = trim($lmTitle);
            // Coba extract LM nomor dulu
            $lm = null;
            preg_match('/LM[\-\s]*(\d+)/i', $lmClean, $lmMatches);
            if (!empty($lmMatches[1])) {
                $lmNum = $lmMatches[1];
                $lm = $allLms->first(function ($l) use ($lmClean, $lmNum) {
                    return preg_match('/LM[\-\s]*' . $lmNum . '/i', $l->judul_lm)
                        && (stripos($l->judul_lm, substr($lmClean, 0, 30)) !== false);
                });
                if (!$lm) {
                    $lm = $allLms->first(function ($l) use ($lmNum) {
                        return preg_match('/LM[\-\s]*' . $lmNum . '/i', $l->judul_lm);
                    });
                }
            }
            if (!$lm) {
                $lm = $allLms->first(function ($l) use ($lmClean) {
                    return stripos($l->judul_lm, substr($lmClean, 0, 40)) !== false;
                });
            }

            if (!$lm) continue;

            // Simpan realisasi per minggu
            foreach ($colRealisasi as $minggu => $colIdx) {
                $rawVal = $row[$colIdx] ?? null;
                if ($rawVal === null || $rawVal === '') continue;

                // Bersihkan nilai dan parsing secara aman
                $angka = null;
                
                if (is_float($rawVal) || is_int($rawVal)) {
                    $angka = (float) $rawVal;
                } else {
                    $valStr = trim((string)$rawVal);
                    // Hapus tanda %, spasi
                    $valStr = str_replace(['%', ' '], '', $valStr);
                    
                    if (str_contains($valStr, ',') && str_contains($valStr, '.')) {
                        // Jika ada titik dan koma, asumsi format Indonesia (contoh: 1.234,56)
                        $valStr = str_replace('.', '', $valStr);
                        $valStr = str_replace(',', '.', $valStr);
                    } elseif (str_contains($valStr, ',')) {
                        // Jika hanya ada koma (contoh: 91,30 atau 1234,56)
                        $valStr = str_replace(',', '.', $valStr);
                    }
                    
                    if (is_numeric($valStr)) {
                        $angka = (float) $valStr;
                    }
                }

                if ($angka === null) continue;

                // Jika satuan sudah % dan nilainya <= 10 (biasanya 0.xx), kemungkinan sudah dalam desimal
                $satuan = strtolower(trim($lm->satuan ?? ''));
                if (($satuan === '%' || $satuan === 'persen' || $satuan === 'prosentase') && $angka > 0 && $angka <= 10) {
                    $angka = $angka * 100;
                }

                // Tanggal: tanggal awal minggu di bulan yang dipilih
                $tgl = $this->mingguTanggal[$minggu] ?? 1;
                // Pastikan tidak melebihi jumlah hari di bulan tersebut
                $maxDay = cal_days_in_month(CAL_GREGORIAN, $this->bulanImport, $this->tahunImport);
                if ($tgl > $maxDay) $tgl = $maxDay;
                $tanggal = sprintf('%04d-%02d-%02d', $this->tahunImport, $this->bulanImport, $tgl);

                // Cari user default (admin/superadmin) atau gunakan ID 1
                $userId = auth()->id() ?? 1;

                Realisasi::updateOrCreate(
                    [
                        'lm_id'         => $lm->id,
                        'unit_id'       => $unit->id,
                        'tanggal_input' => $tanggal,
                    ],
                    [
                        'user_id'             => $userId,
                        'angka_realisasi'     => $angka,
                        'bukti_file'          => 'Upload Massal Excel (Format Bidang)',
                        'keterangan_tambahan' => 'Import dari format Scoreboard Bidang - Minggu ' . $minggu,
                    ]
                );
            }
        }
    }
}

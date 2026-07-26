<?php

namespace App\Imports;

use App\Models\MasterWig;
use App\Models\MasterUnit;
use App\Models\RealisasiWig;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class RealisasiWigMassImport implements ToCollection, WithHeadingRow
{
    private function parseNumber($val) {
        if ($val === null || $val === "") return null; // Only update if value is present
        $val = str_replace(",", "", $val);
        return floatval($val);
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            $userId = auth()->id() ?? 1; // Default to 1 if no auth

            foreach ($rows as $row) {
                $no = null;
                $judulWig = null;
                $namaUnit = null;
                
                $rJan = null; $rFeb = null; $rMar = null; $rApr = null; $rMei = null; $rJun = null;
                $rJul = null; $rAgu = null; $rSep = null; $rOkt = null; $rNov = null; $rDes = null;

                foreach($row as $key => $val) {
                    if (str_contains($key, "no") && strlen($key) <= 5) $no = $val;
                    if (str_contains($key, "indikator") || str_contains($key, "judul_wig")) $judulWig = $val;
                    if (str_contains($key, "unit")) $namaUnit = $val;

                    if (str_contains($key, "realisasi")) {
                        if (str_contains($key, "januari") || str_contains($key, "jan")) $rJan = $this->parseNumber($val);
                        if (str_contains($key, "februari") || str_contains($key, "feb")) $rFeb = $this->parseNumber($val);
                        if (str_contains($key, "maret") || str_contains($key, "mar")) $rMar = $this->parseNumber($val);
                        if (str_contains($key, "april") || str_contains($key, "apr")) $rApr = $this->parseNumber($val);
                        if (str_contains($key, "mei")) $rMei = $this->parseNumber($val);
                        if (str_contains($key, "juni") || str_contains($key, "jun")) $rJun = $this->parseNumber($val);
                        if (str_contains($key, "juli") || str_contains($key, "jul")) $rJul = $this->parseNumber($val);
                        if (str_contains($key, "agustus") || str_contains($key, "agu")) $rAgu = $this->parseNumber($val);
                        if (str_contains($key, "september") || str_contains($key, "sep")) $rSep = $this->parseNumber($val);
                        if (str_contains($key, "oktober") || str_contains($key, "okt")) $rOkt = $this->parseNumber($val);
                        if (str_contains($key, "november") || str_contains($key, "nov")) $rNov = $this->parseNumber($val);
                        if (str_contains($key, "desember") || str_contains($key, "des")) $rDes = $this->parseNumber($val);
                    }
                }

                if (!$no && !$judulWig) {
                    continue;
                }

                $wig = null;
                if ($no) {
                    $searchNo = str_replace("-", " ", $no);
                    $wig = MasterWig::where("judul", "like", $searchNo . "%")->first();
                }

                if (!$wig && $judulWig) {
                    $wig = MasterWig::where("judul", "like", "%" . $judulWig . "%")->first();
                }

                if (!$wig) continue; // WIG not found

                $unit = null;
                if ($namaUnit) {
                    if (strtolower(trim($namaUnit)) == "uid jabar") {
                        $unit = MasterUnit::where("type", "UID")->first();
                    } else {
                        $unit = MasterUnit::where("name", "like", "%" . trim($namaUnit) . "%")->first();
                    }
                }

                if (!$unit) continue; // Unit not found

                $realisasis = [
                    1 => $rJan, 2 => $rFeb, 3 => $rMar, 4 => $rApr,
                    5 => $rMei, 6 => $rJun, 7 => $rJul, 8 => $rAgu,
                    9 => $rSep, 10 => $rOkt, 11 => $rNov, 12 => $rDes
                ];

                foreach ($realisasis as $bulan => $angka) {
                    if ($angka !== null) {
                        RealisasiWig::updateOrCreate(
                            [
                                "wig_id" => $wig->id,
                                "unit_id" => $unit->id,
                                "bulan" => $bulan,
                                "tahun" => 2026 // Asumsi tahun 2026
                            ],
                            [
                                "user_id" => $userId,
                                "angka_realisasi" => $angka
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


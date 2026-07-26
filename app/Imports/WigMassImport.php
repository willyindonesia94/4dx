<?php

namespace App\Imports;

use App\Models\MasterWig;
use App\Models\MasterUnit;
use App\Models\MasterSatuan;
use App\Models\BreakdownWig;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class WigMassImport implements ToCollection, WithHeadingRow
{
    private function parseNumber($val) {
        if (!$val) return 0;
        $val = str_replace(",", "", $val);
        return floatval($val);
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $no = null;
                $judulWig = null;
                $polaritas = "3";
                $satuan = null;
                $namaUnit = null;
                
                $tJan = 0; $tFeb = 0; $tMar = 0; $tApr = 0; $tMei = 0; $tJun = 0;
                $tJul = 0; $tAgu = 0; $tSep = 0; $tOkt = 0; $tNov = 0; $tDes = 0;

                foreach($row as $key => $val) {
                    if (str_contains($key, "no") && strlen($key) <= 5) $no = $val;
                    if (str_contains($key, "indikator") || str_contains($key, "judul_wig")) $judulWig = $val;
                    if (str_contains($key, "polaritas")) $polaritas = $val;
                    if (str_contains($key, "satuan")) $satuan = $val;
                    if (str_contains($key, "unit")) $namaUnit = $val;

                    if (str_contains($key, "target")) {
                        if (str_contains($key, "januari") || str_contains($key, "jan")) $tJan = $this->parseNumber($val);
                        if (str_contains($key, "februari") || str_contains($key, "feb")) $tFeb = $this->parseNumber($val);
                        if (str_contains($key, "maret") || str_contains($key, "mar")) $tMar = $this->parseNumber($val);
                        if (str_contains($key, "april") || str_contains($key, "apr")) $tApr = $this->parseNumber($val);
                        if (str_contains($key, "mei")) $tMei = $this->parseNumber($val);
                        if (str_contains($key, "juni") || str_contains($key, "jun")) $tJun = $this->parseNumber($val);
                        if (str_contains($key, "juli") || str_contains($key, "jul")) $tJul = $this->parseNumber($val);
                        if (str_contains($key, "agustus") || str_contains($key, "agu")) $tAgu = $this->parseNumber($val);
                        if (str_contains($key, "september") || str_contains($key, "sep")) $tSep = $this->parseNumber($val);
                        if (str_contains($key, "oktober") || str_contains($key, "okt")) $tOkt = $this->parseNumber($val);
                        if (str_contains($key, "november") || str_contains($key, "nov")) $tNov = $this->parseNumber($val);
                        if (str_contains($key, "desember") || str_contains($key, "des")) $tDes = $this->parseNumber($val);
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

                $satuanWigObj = null;
                if ($satuan) {
                    $satuanWigObj = MasterSatuan::firstOrCreate(["name" => $satuan]);
                }

                if (!$wig) {
                    $wig = MasterWig::create([
                        "judul" => $judulWig ?? $no,
                        "divisi" => "4DX",
                        "unit_pemilik_id" => null,
                        "angka_target" => 0,
                        "satuan_id" => $satuanWigObj ? $satuanWigObj->id : null,
                        "polaritas" => "positif",
                        "is_approved" => false,
                    ]);
                }

                if ($namaUnit) {
                    $searchName = trim($namaUnit);
                    $unitWig = null;
                    
                    if (strtoupper($searchName) === "UID JABAR" || strtoupper($searchName) === "UID JAWA BARAT") {
                        $unitWig = MasterUnit::where("name", "UID Jawa Barat")->first();
                    } else {
                        $unitWig = MasterUnit::where("name", $searchName)->first();
                        if (!$unitWig) {
                            $unitWig = MasterUnit::where("name", "UP3 " . ucwords(strtolower($searchName)))->first();
                        }
                        if (!$unitWig) {
                            $unitWig = MasterUnit::where("name", "ULP " . ucwords(strtolower($searchName)))->first();
                        }
                        if (!$unitWig) {
                            $unitWig = MasterUnit::where("name", "like", "%" . $searchName . "%")->first();
                        }
                    }

                    if ($unitWig) {
                        $isUID = $unitWig->type === "UID" || str_contains(strtolower($unitWig->name), "jawa barat");
                        $targetTahunan = $isUID ? $wig->angka_target : $tDes;
                        
                        BreakdownWig::updateOrCreate(
                            [
                                "wig_id" => $wig->id,
                                "unit_id" => $unitWig->id,
                                "tahun" => 2026,
                            ],
                            [
                                "satuan_id" => $satuanWigObj ? $satuanWigObj->id : $wig->satuan_id,
                                "target_tahunan" => $targetTahunan,
                                "target_jan" => $tJan,
                                "target_feb" => $tFeb,
                                "target_mar" => $tMar,
                                "target_apr" => $tApr,
                                "target_mei" => $tMei,
                                "target_jun" => $tJun,
                                "target_jul" => $tJul,
                                "target_agu" => $tAgu,
                                "target_sep" => $tSep,
                                "target_okt" => $tOkt,
                                "target_nov" => $tNov,
                                "target_des" => $tDes,
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


<?php

namespace App\Imports;

use App\Models\MasterWig;
use App\Models\MasterUnit;
use App\Models\MasterSatuan;
use App\Models\BreakdownWig;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;

class WigMassImport implements ToCollection
{
    private $lastNo = null;
    private $lastJudulWig = null;
    private $lastSatuan = null;

    private function parseNumber($val) {
        if ($val === null || $val === "") return 0;
        $valStr = (string)$val;
        $isPercent = str_contains($valStr, '%');
        $valClean = str_replace([",", "%"], "", $valStr);
        $floatVal = floatval($valClean);
        if ($isPercent) {
            return $floatVal / 100;
        }
        return $floatVal;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            $headerMap = [];
            $dataStartIdx = 0;
            
            // Find header row (scan first 20 rows)
            foreach ($rows as $idx => $row) {
                if ($idx > 20) break;
                $rowArray = $row->toArray();
                $isHeaderRow = false;
                
                foreach ($rowArray as $colIdx => $val) {
                    if ($val === null) continue;
                    $valStr = strtolower(trim((string)$val));
                    if (str_contains($valStr, "no") || str_contains($valStr, "indikator") || str_contains($valStr, "unit") || str_contains($valStr, "target") || str_contains($valStr, "jan") || str_contains($valStr, "satuan")) {
                        $isHeaderRow = true;
                        $headerMap[$colIdx] = $valStr;
                    }
                }
                
                // If we found at least 3 recognizable headers, assume this is the header row
                if ($isHeaderRow && count($headerMap) >= 3) {
                    $dataStartIdx = $idx + 1;
                    break;
                }
            }

            for ($i = $dataStartIdx; $i < count($rows); $i++) {
                $row = $rows[$i]->toArray();
                $no = null;
                $judulWig = null;
                $polaritas = "3";
                $satuan = null;
                $namaUnit = null;
                
                $tJan = 0; $tFeb = 0; $tMar = 0; $tApr = 0; $tMei = 0; $tJun = 0;
                $tJul = 0; $tAgu = 0; $tSep = 0; $tOkt = 0; $tNov = 0; $tDes = 0;

                foreach($row as $colIdx => $val) {
                    if (!isset($headerMap[$colIdx])) continue;
                    $key = $headerMap[$colIdx];
                    
                    if (str_contains($key, "no") && strlen($key) <= 5 && $val !== null && $val !== "") $no = $val;
                    if ((str_contains($key, "indikator") || str_contains($key, "judul")) && $val !== null && $val !== "") $judulWig = $val;
                    if (str_contains($key, "polaritas") && $val !== null && $val !== "") $polaritas = $val;
                    if (str_contains($key, "satuan") && $val !== null && $val !== "") $satuan = $val;
                    if (str_contains($key, "unit")) $namaUnit = $val;

                    if (str_contains($key, "realisasi") || str_contains($key, "prog") || str_contains($key, "pencapaian")) continue;

                    if (str_contains($key, "target") || str_contains($key, "jan") || str_contains($key, "feb") || str_contains($key, "mar")) {
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

                if (!$no) $no = $this->lastNo;
                if (!$judulWig) $judulWig = $this->lastJudulWig;
                if (!$satuan) $satuan = $this->lastSatuan;

                // Handle merged cell for UID Jabar (where 'satuan' gets the name, and 'unit' is empty)
                if (empty($namaUnit) && !empty($satuan)) {
                    $satuanLower = strtolower(trim($satuan));
                    if (str_contains($satuanLower, 'uid jabar') || str_contains($satuanLower, 'jawa barat')) {
                        $namaUnit = $satuan;
                        $satuan = $this->lastSatuan; // Restore real satuan from previous row if applicable
                    }
                }
                
                if (str_contains(strtolower($namaUnit ?? ''), 'uid jabar') || str_contains(strtolower($namaUnit ?? ''), 'jawa barat')) {
                    \Illuminate\Support\Facades\Log::info("Row Dump for UID JABAR: " . json_encode($row));
                    \Illuminate\Support\Facades\Log::info("Header Map Dump: " . json_encode($headerMap));
                }

                \Illuminate\Support\Facades\Log::info("Parsed variables - no: '{$no}', judulWig: '{$judulWig}', namaUnit: '{$namaUnit}', tJan: '{$tJan}'");

                if (!$judulWig || !$namaUnit) {
                    \Illuminate\Support\Facades\Log::info("Skipping row: missing wig or unit. wig=" . ($judulWig??'null') . ", unit=" . ($namaUnit??'null') . " Row: ", $row);
                    continue;
                }

                $this->lastNo = $no;
                $this->lastJudulWig = $judulWig;
                $this->lastSatuan = $satuan;

                $wig = null;
                if ($no) {
                    $searchNo = str_replace("-", " ", $no);
                    $wig = MasterWig::where("judul", "like", $searchNo . "%")->first();
                }
                
                if (!$wig && $judulWig) {
                    $wig = MasterWig::where("judul", "like", "%" . $judulWig . "%")->first();
                }

                $satuanWigObj = null;
                if ($satuan) {
                    $satuanWigObj = MasterSatuan::firstOrCreate(["name" => $satuan]);
                }

                if (!$wig) {
                    // Coba cari unit UID Jawa Barat sebagai pemilik default
                    $defaultPemilik = MasterUnit::where("type", "UID")->first();
                    $wig = MasterWig::create([
                        "judul" => $judulWig ?? $no ?? "WIG Baru",
                        "divisi" => "4DX",
                        "unit_pemilik_id" => $defaultPemilik ? $defaultPemilik->id : 1,
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
                        // Coba temukan Unit target
                        $cleanUnit = trim((string)$namaUnit);
                        $unit = MasterUnit::where("name", "like", "%" . $cleanUnit . "%")->first();
                        
                        // Fallback jika tidak ketemu (mungkin spasinya berbeda atau UP3-nya terhapus)
                        if (!$unit) {
                            $shortName = trim(str_ireplace(['UP3', 'ULP'], '', $cleanUnit));
                            $unit = MasterUnit::where("name", "like", "%" . $shortName . "%")->first();
                        }

                        if (!$unit) {
                            \Illuminate\Support\Facades\Log::info("Skipping row: unit not found in DB for namaUnit='{$namaUnit}'");
                            continue;
                        }      
                        $unitWig = $unit;
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


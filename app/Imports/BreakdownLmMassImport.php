<?php

namespace App\Imports;

use App\Models\MasterLm;
use App\Models\MasterUnit;
use App\Models\BreakdownLm;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BreakdownLmMassImport implements ToCollection, WithCalculatedFormulas
{
    protected $bulan;
    protected $tahun;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    private function parseNumber($val) {
        if ($val === null || $val === "") return null;
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
            $carbonStart = \Carbon\Carbon::create($this->tahun, $this->bulan, 1);
            $carbonEnd = $carbonStart->copy()->endOfMonth();
            $masterWeeks = \App\Models\MasterPeriode::getWeekDates($this->tahun, $this->bulan);

            $startBulan = $masterWeeks['target_m1'] ? $masterWeeks['target_m1']['start'] : $carbonStart->format('Y-m-d');
            $endWeek = isset($masterWeeks['target_m5']) && $masterWeeks['target_m5'] ? 'target_m5' : 'target_m4';
            $endBulan = $masterWeeks[$endWeek] ? $masterWeeks[$endWeek]['end'] : $carbonEnd->format('Y-m-d');

            $weeks = [
                "bulanan" => ["start" => $startBulan, "end" => $endBulan],
            ];
            if ($masterWeeks['target_m1']) $weeks["minggu_1"] = $masterWeeks['target_m1'];
            if ($masterWeeks['target_m2']) $weeks["minggu_2"] = $masterWeeks['target_m2'];
            if ($masterWeeks['target_m3']) $weeks["minggu_3"] = $masterWeeks['target_m3'];
            if ($masterWeeks['target_m4']) $weeks["minggu_4"] = $masterWeeks['target_m4'];
            if ($masterWeeks['target_m5']) $weeks["minggu_5"] = $masterWeeks['target_m5'];

            $lastJudulLm = null;
            $lastNoWig = null;
            $headerRowFound = false;
            $colMap = [];
            $skippedRows = [];

            foreach ($rows as $row) {
                if (!$headerRowFound) {
                    foreach ($row as $index => $val) {
                        $v = strtolower(trim((string)$val));
                        if (str_contains($v, "indikator") || str_contains($v, "judul_lm") || str_contains($v, "judul lm")) $colMap["judul_lm"] = $index;
                        if (str_contains($v, "unit")) $colMap["unit"] = $index;
                        if (str_contains($v, "no_wig") || (str_contains($v, "wig") && !str_contains($v, "judul"))) $colMap["no_wig"] = $index;
                        if (str_contains($v, "no") && strlen($v) <= 5 && !isset($colMap["no_wig"])) $colMap["no_wig"] = $index;
                        
                        if (str_contains($v, "target")) {
                            if (str_contains($v, "bulanan")) $colMap["bulanan"] = $index;
                            if (str_contains($v, "minggu_1") || str_contains($v, "minggu 1") || str_contains($v, "minggu i") || str_contains($v, "minggu-1")) $colMap["minggu_1"] = $index;
                            if (str_contains($v, "minggu_2") || str_contains($v, "minggu 2") || str_contains($v, "minggu ii") || str_contains($v, "minggu-2")) $colMap["minggu_2"] = $index;
                            if (str_contains($v, "minggu_3") || str_contains($v, "minggu 3") || str_contains($v, "minggu iii") || str_contains($v, "minggu-3")) $colMap["minggu_3"] = $index;
                            if (str_contains($v, "minggu_4") || str_contains($v, "minggu 4") || str_contains($v, "minggu iv") || str_contains($v, "minggu-4")) $colMap["minggu_4"] = $index;
                            if (str_contains($v, "minggu_5") || str_contains($v, "minggu 5") || str_contains($v, "minggu v") || str_contains($v, "minggu-5")) $colMap["minggu_5"] = $index;
                        }
                    }
                    if (isset($colMap["judul_lm"]) && isset($colMap["unit"])) {
                        $headerRowFound = true;
                        \Illuminate\Support\Facades\Log::info("Header row found! colMap: " . json_encode($colMap));
                    }
                    continue;
                }

                // Detect if this row is a WIG section header
                $possibleWigHeader = null;
                foreach ($row as $val) {
                    $v = strtolower(trim((string)$val));
                    if (str_contains($v, "wig") && (str_contains($v, "1") || str_contains($v, "2") || str_contains($v, "3") || str_contains($v, "4") || str_contains($v, "5"))) {
                        $possibleWigHeader = $val;
                        break;
                    }
                }
                
                if ($possibleWigHeader) {
                    $wigMatch = \App\Models\MasterWig::where("judul", "like", trim($possibleWigHeader) . "%")->orWhere("judul", "like", "%" . trim($possibleWigHeader) . "%")->first();
                    if ($wigMatch) {
                        $lastNoWig = $wigMatch->id;
                    }
                }

                $valJudul = isset($colMap["judul_lm"]) ? $row[$colMap["judul_lm"]] : null;
                $valUnit = isset($colMap["unit"]) ? $row[$colMap["unit"]] : null;
                $valNoWig = isset($colMap["no_wig"]) ? $row[$colMap["no_wig"]] : null;

                $judulLm = ($valJudul !== null && trim($valJudul) !== "") ? $valJudul : $lastJudulLm;
                $namaUnit = $valUnit;
                
                // Determine the WIG for this row
                if ($valNoWig !== null && trim($valNoWig) !== "") {
                    $noWig = $valNoWig;
                } else {
                    $noWig = $lastNoWig;
                }
                
                $targets = [
                    "bulanan" => isset($colMap["bulanan"]) ? $this->parseNumber($row[$colMap["bulanan"]]) : null,
                    "minggu_1" => isset($colMap["minggu_1"]) ? $this->parseNumber($row[$colMap["minggu_1"]]) : null,
                    "minggu_2" => isset($colMap["minggu_2"]) ? $this->parseNumber($row[$colMap["minggu_2"]]) : null,
                    "minggu_3" => isset($colMap["minggu_3"]) ? $this->parseNumber($row[$colMap["minggu_3"]]) : null,
                    "minggu_4" => isset($colMap["minggu_4"]) ? $this->parseNumber($row[$colMap["minggu_4"]]) : null,
                    "minggu_5" => isset($colMap["minggu_5"]) ? $this->parseNumber($row[$colMap["minggu_5"]]) : null,
                ];

                \Illuminate\Support\Facades\Log::info("Parsed data for $namaUnit: raw_bulanan=" . (isset($colMap["bulanan"]) ? $row[$colMap["bulanan"]] : 'null') . ", parsed=" . $targets["bulanan"]);

                if (!$judulLm || !$namaUnit) {
                    continue; 
                }

                $lm = null;
                
                // Cari prefix LM-X
                preg_match("/LM-\d+/i", $judulLm, $matches);
                $lmCode = $matches[0] ?? null;

                if ($noWig && $lmCode) {
                    $wig = null;
                    if (is_numeric($noWig) && $noWig > 0 && $noWig < 100) {
                        // It might be an ID or just a number 1, 2, 3
                        $wig = \App\Models\MasterWig::find($noWig);
                        if (!$wig) {
                            $wig = \App\Models\MasterWig::where("judul", "like", "WIG " . trim($noWig) . "%")->orWhere("judul", "like", trim($noWig) . "%")->first();
                        }
                    } else {
                        $wig = \App\Models\MasterWig::where("judul", "like", trim($noWig) . "%")->orWhere("judul", "like", "%" . trim($noWig) . "%")->first();
                    }
                    
                    if ($wig) {
                        $lm = MasterLm::where("wig_id", $wig->id)->where("judul_lm", "like", $lmCode . "%")->first();
                    }
                }

                if (!$lm) {
                    $lm = MasterLm::where("judul_lm", "like", "%" . trim($judulLm) . "%")->first();
                }

                if (!$lm && $lmCode) {
                    $candidates = MasterLm::where("judul_lm", "like", $lmCode . "%")->get();
                    $bestMatch = null;
                    $highestSim = 0;
                    foreach ($candidates as $candidate) {
                        similar_text(strtolower(trim($judulLm)), strtolower(trim($candidate->judul_lm)), $percent);
                        if ($percent > $highestSim) {
                            $highestSim = $percent;
                            $bestMatch = $candidate;
                        }
                    }
                    if ($highestSim > 70) {
                        $lm = $bestMatch;
                    }
                }

                if (!$lm) {
                    $skippedRows[] = trim($judulLm);
                    \Illuminate\Support\Facades\Log::warning("Skipped row because LM not found: " . trim($judulLm));
                    continue;
                }

                $unit = null;
                $cleanUnit = str_replace(' ', '', strtolower(trim($namaUnit)));
                if (str_contains($cleanUnit, "uidjabar") || str_contains($cleanUnit, "uidjawabarat")) {
                    $unit = MasterUnit::where("type", "UID")->first();
                } else {
                    // Coba cari persis
                    $unit = MasterUnit::where("name", "like", "%" . trim($namaUnit) . "%")->first();
                    // Jika tidak ketemu, coba hapus "UP3" atau "ULP" dari awal/akhir jika user tidak menyertakan
                    if (!$unit) {
                        $shortName = trim(str_ireplace(['UP3', 'ULP'], '', $namaUnit));
                        $unit = MasterUnit::where("name", "like", "%" . $shortName . "%")->first();
                    }
                }
                
                if (!$unit) {
                    $skippedRows[] = trim($judulLm) . " (Unit tidak ditemukan: $namaUnit)";
                    continue;
                }

                foreach ($targets as $tipe => $angka) {
                    if ($angka !== null && $angka !== "" && isset($weeks[$tipe])) {
                        $startDate = $weeks[$tipe]["start"];
                        $endDate = $weeks[$tipe]["end"];

                        BreakdownLm::updateOrCreate(
                            [
                                "lm_id" => $lm->id,
                                "unit_id" => $unit->id,
                                "periode_start" => $startDate,
                                "periode_end" => $endDate
                            ],
                            [
                                "angka_target" => $angka,
                                "satuan_id" => $lm->satuan_id,
                                "bulan" => $this->bulan,
                                "tahun" => $this->tahun
                            ]
                        );
                    }
                }

                $lastJudulLm = $judulLm;
                $lastNoWig = $noWig;
            }

            if (count($skippedRows) > 0) {
                $uniqueSkipped = array_unique($skippedRows);
                session()->flash('warning_skipped', "Beberapa data dilewati (Unit/LM tidak ditemukan): " . implode(", ", $uniqueSkipped));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}


<?php

namespace App\Imports;

use App\Models\MasterLm;
use App\Models\Realisasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class RealisasiLmMassImport implements ToCollection, WithHeadingRow
{
    protected $isProrata;
    protected $tanggalMulai;
    protected $tanggalSelesai;

    public function __construct($isProrata = false, $tanggalMulai = null, $tanggalSelesai = null)
    {
        $this->isProrata = $isProrata;
        $this->tanggalMulai = $tanggalMulai ? Carbon::parse($tanggalMulai) : null;
        $this->tanggalSelesai = $tanggalSelesai ? Carbon::parse($tanggalSelesai) : null;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Cari kolom berdasarkan kata kunci agar robust
                $judulLm = null;
                $nip = null;
                $tanggal = null;
                $angka = 0;
                $bukti = null;

                foreach($row as $key => $val) {
                    if (!$val) continue;
                    
                    if (str_contains($key, "judul") || str_contains($key, "lm")) $judulLm = $val;
                    if (str_contains($key, "nip")) $nip = $val;
                    if (str_contains($key, "tanggal")) $tanggal = $val;
                    if (str_contains($key, "angka") || str_contains($key, "realisasi")) $angka = $val;
                    if (str_contains($key, "bukti") || str_contains($key, "link")) $bukti = $val;
                }

                if (!$judulLm || !$nip) {
                    continue; // Skip jika tidak ada judul LM atau NIP
                }

                // Cari LM
                $searchLm = trim($judulLm);
                $lm = MasterLm::where("judul_lm", "like", "%" . $searchLm . "%")->first();
                if (!$lm) continue;

                // Cari User
                $searchNip = trim($nip);
                $user = User::where("nip", $searchNip)->first();
                if (!$user) continue;

                // Format angka
                $angkaRealisasi = floatval(str_replace(",", "", $angka));

                if ($this->isProrata && $this->tanggalMulai && $this->tanggalSelesai) {
                    // Logic Distribusi Pro-Rata
                    $days = $this->tanggalMulai->diffInDays($this->tanggalSelesai) + 1; // +1 to include both start and end
                    if ($days <= 0) $days = 1;

                    $angkaPerHari = $angkaRealisasi / $days;

                    for ($i = 0; $i < $days; $i++) {
                        $currentDate = $this->tanggalMulai->copy()->addDays($i)->format('Y-m-d');
                        
                        Realisasi::updateOrCreate(
                            [
                                "lm_id"        => $lm->id,
                                "user_id"      => $user->id,
                                "tanggal_input" => $currentDate,
                            ],
                            [
                                "unit_id"        => $user->unit_id,
                                "angka_realisasi" => $angkaPerHari,
                                "bukti_file"     => $bukti ?? 'Prorata dari Upload Massal',
                            ]
                        );
                    }
                } else {
                    // Logic Normal
                    $parsedDate = now()->format("Y-m-d");
                    if ($tanggal) {
                        try {
                            if (is_numeric($tanggal)) {
                                $parsedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal)->format("Y-m-d");
                            } else {
                                $parsedDate = Carbon::parse($tanggal)->format("Y-m-d");
                            }
                        } catch (\Exception $e) {
                            $parsedDate = now()->format("Y-m-d");
                        }
                    }

                    Realisasi::updateOrCreate(
                        [
                            "lm_id"        => $lm->id,
                            "user_id"      => $user->id,
                            "tanggal_input" => $parsedDate,
                        ],
                        [
                            "unit_id"        => $user->unit_id,
                            "angka_realisasi" => $angkaRealisasi,
                            "bukti_file"     => $bukti,
                        ]
                    );
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}


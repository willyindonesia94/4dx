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
                $judulWig = null;
                $judulLm = null;
                $nip = null;
                $tanggal = null;
                $angka = 0;
                $bukti = null;

                foreach ($row as $key => $val) {
                    if ($val === null || $val === '') continue;
                    
                    $cleanKey = strtolower(trim((string)$key));
                    if (str_contains($cleanKey, "wig")) $judulWig = $val;
                    if (str_contains($cleanKey, "lm") || str_contains($cleanKey, "lead") || str_contains($cleanKey, "measure")) $judulLm = $val;
                    if (str_contains($cleanKey, "nip") || str_contains($cleanKey, "email") || str_contains($cleanKey, "user") || str_contains($cleanKey, "pengguna") || str_contains($cleanKey, "penginput")) $nip = $val;
                    if (str_contains($cleanKey, "tanggal") || str_contains($cleanKey, "date") || str_contains($cleanKey, "waktu")) $tanggal = $val;
                    if (str_contains($cleanKey, "angka") || str_contains($cleanKey, "realisasi") || str_contains($cleanKey, "capaian")) $angka = $val;
                    if (str_contains($cleanKey, "bukti") || str_contains($cleanKey, "link") || str_contains($cleanKey, "keterangan") || str_contains($cleanKey, "catatan")) $bukti = $val;
                }

                if (!$judulLm) {
                    continue; // Skip jika baris tidak memiliki judul LM
                }

                // Cari LM (jika judul WIG ada, utamakan pencarian LM di dalam WIG tersebut)
                $searchLm = trim((string)$judulLm);
                $lmQuery = MasterLm::where("judul_lm", "like", "%" . $searchLm . "%");
                if ($judulWig) {
                    $searchWig = trim((string)$judulWig);
                    $lmQuery->whereHas('wig', function($q) use ($searchWig) {
                        $q->where('judul', 'like', '%' . $searchWig . '%');
                    });
                }
                $lm = $lmQuery->first();
                if (!$lm) {
                    // Jika tidak terdeteksi dari kombinasi WIG + LM, cari dari judul LM saja
                    $lm = MasterLm::where("judul_lm", "like", "%" . $searchLm . "%")->first();
                    if (!$lm) continue;
                }

                // Cari User: Jika ada NIP dicari di database, jika kosong otomatis gunakan auth user saat ini
                $user = null;
                if ($nip) {
                    $searchNip = trim((string)$nip);
                    $user = User::where("nip", $searchNip)
                                ->orWhere("email", $searchNip)
                                ->orWhere("name", "like", "%" . $searchNip . "%")
                                ->first();
                }
                if (!$user) {
                    $user = auth()->user();
                }
                if (!$user) continue;

                // Format angka realisasi
                $angkaRealisasi = floatval(str_replace(",", "", (string)$angka));
                
                // Format bukti dan keterangan
                $buktiText = $bukti ? trim((string)$bukti) : 'Diimport dari Upload Massal Excel';
                $buktiFile = (str_starts_with(strtolower($buktiText), 'http://') || str_starts_with(strtolower($buktiText), 'https://')) ? $buktiText : 'Upload Massal Excel';

                if ($this->isProrata && $this->tanggalMulai && $this->tanggalSelesai) {
                    // Logic Distribusi Pro-Rata
                    $days = $this->tanggalMulai->diffInDays($this->tanggalSelesai) + 1;
                    if ($days <= 0) $days = 1;

                    $angkaPerHari = $angkaRealisasi / $days;

                    for ($i = 0; $i < $days; $i++) {
                        $currentDate = $this->tanggalMulai->copy()->addDays($i)->format('Y-m-d');
                        
                        Realisasi::updateOrCreate(
                            [
                                "lm_id"         => $lm->id,
                                "user_id"       => $user->id,
                                "tanggal_input" => $currentDate,
                            ],
                            [
                                "unit_id"             => $user->unit_id,
                                "angka_realisasi"     => $angkaPerHari,
                                "bukti_file"          => $buktiFile,
                                "keterangan_tambahan" => $buktiText . " (Prorata)",
                            ]
                        );
                    }
                } else {
                    // Logic Normal Harian
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
                            "lm_id"         => $lm->id,
                            "user_id"       => $user->id,
                            "tanggal_input" => $parsedDate,
                        ],
                        [
                            "unit_id"             => $user->unit_id,
                            "angka_realisasi"     => $angkaRealisasi,
                            "bukti_file"          => $buktiFile,
                            "keterangan_tambahan" => $buktiText,
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


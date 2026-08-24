<?php

namespace App\Exports;

use App\Models\MasterLm;
use App\Models\MasterWig;
use App\Models\MasterUnit;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BreakdownLmTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        $user = Auth::user();
        $userRole = $user ? strtolower(trim($user->role_name ?? '')) : '';
        $unitType = ($user && $user->unit) ? strtoupper(trim((string)$user->unit->type)) : '';
        $isSuperAdmin = $user && (in_array($userRole, ['super admin', 'superadmin', 'perencanaan uid']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin', 'Perencanaan UID'])));
        $isUp3 = !$isSuperAdmin && ($unitType === 'UP3' || str_contains($userRole, 'up3'));
        $isUid = !$isSuperAdmin && !$isUp3 && ($unitType === 'UID' || str_contains($userRole, 'uid') || str_contains($userRole, 'bidang'));

        // Menyesuaikan daftar unit yang bisa di-breakdown oleh user saat ini
        $availableUnits = collect();
        if ($isSuperAdmin || $isUid) {
            // Level Bidang UID / Admin breakdown ke Level UP3
            $availableUnits = MasterUnit::where('type', 'UP3')->orderBy('name')->get();
            if ($availableUnits->isEmpty()) {
                $availableUnits = MasterUnit::orderBy('name')->get();
            }
        } elseif ($isUp3) {
            // Level UP3 breakdown ke ULP di bawah lingkup UP3-nya
            if ($user && $user->unit_id) {
                $availableUnits = MasterUnit::where('type', 'ULP')->where('parent_id', $user->unit_id)->orderBy('name')->get();
            } else {
                $availableUnits = MasterUnit::where('type', 'ULP')->orderBy('name')->get();
            }
        } else {
            $availableUnits = MasterUnit::where('type', 'UP3')->orderBy('name')->get();
            if ($availableUnits->isEmpty()) {
                $availableUnits = MasterUnit::orderBy('name')->get();
            }
        }

        if ($availableUnits->isEmpty()) {
            $placeholderName = $isUp3 ? 'ULP CONTOH (Di bawah UP3 Anda)' : 'UP3 CONTOH';
            $availableUnits = collect([(object)['name' => $placeholderName]]);
        }

        // Filter dan Ambil Data WIG beserta LM
        $wigsQuery = MasterWig::where('is_approved', true)
            ->with(['masterLms' => function($q) {
                $q->where('is_approved', true);
            }]);

        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL' && class_exists(\App\Models\MasterBidang::class)) {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            if (!empty($allowedDivisis)) {
                $wigsQuery->where(function($q) use ($allowedDivisis) {
                    foreach ($allowedDivisis as $div) {
                        $q->orWhereJsonContains('divisi', $div);
                    }
                });
            }
        }

        $wigs = $wigsQuery->get();
        if ($wigs->isEmpty()) {
            $wigs = MasterWig::with('masterLms')->get();
        }

        // Urutkan WIG secara berurutan (WIG 1, WIG 2, dst)
        $wigs = $wigs->sort(function($a, $b) {
            preg_match('/WIG\s*-?\s*(\d+)/i', $a->judul ?? '', $mA);
            preg_match('/WIG\s*-?\s*(\d+)/i', $b->judul ?? '', $mB);
            $numA = isset($mA[1]) ? (int)$mA[1] : 999;
            $numB = isset($mB[1]) ? (int)$mB[1] : 999;
            if ($numA === $numB) {
                return strnatcasecmp($a->judul ?? '', $b->judul ?? '');
            }
            return $numA <=> $numB;
        })->values();

        $rows = collect([]);
        $hasData = false;

        foreach ($wigs as $wig) {
            if (!$wig->masterLms || $wig->masterLms->isEmpty()) {
                continue;
            }

            // Urutkan LM secara berurutan (LM 1, LM 2, dst)
            $lms = $wig->masterLms->sort(function($a, $b) {
                preg_match('/LM\s*-?\s*(\d+)/i', $a->judul_lm ?? '', $mA);
                preg_match('/LM\s*-?\s*(\d+)/i', $b->judul_lm ?? '', $mB);
                $numA = isset($mA[1]) ? (int)$mA[1] : 999;
                $numB = isset($mB[1]) ? (int)$mB[1] : 999;
                if ($numA === $numB) {
                    return strnatcasecmp($a->judul_lm ?? '', $b->judul_lm ?? '');
                }
                return $numA <=> $numB;
            })->values();

            // Pengecualian khusus: Hapus LM 4 dan LM 5 dari WIG 1
            if (preg_match('/WIG\s*-?\s*1\b/i', $wig->judul ?? '')) {
                $lms = $lms->reject(function($lm) {
                    return preg_match('/LM\s*-?\s*(4|5)\b/i', $lm->judul_lm ?? '');
                })->values();
            }

            foreach ($lms as $lm) {
                $hasData = true;

                // 1. Tambahkan unit 'Induk' di paling atas
                if ($isSuperAdmin || $isUid) {
                    $uidUnit = \App\Models\MasterUnit::where('name', 'UID Jawa Barat')->first();
                    $rows->push([
                        $wig->judul ?? '1',
                        $lm->judul_lm ?? '',
                        $uidUnit ? $uidUnit->name : 'UID Jawa Barat',
                        '', '', '', '', '', ''
                    ]);
                } elseif ($isUp3 && $user && $user->unit) {
                    $rows->push([
                        $wig->judul ?? '1',
                        $lm->judul_lm ?? '',
                        $user->unit->name,
                        '', '', '', '', '', ''
                    ]);
                }

                // 2. Tambahkan unit-unit breakdown utama (UP3 atau ULP)
                foreach ($availableUnits as $unit) {
                    $rows->push([
                        $wig->judul ?? '1',
                        $lm->judul_lm ?? '',
                        $unit->name ?? '',
                        '', '', '', '', '', ''
                    ]);
                }

                // 3. Tambahkan UP2D paling bawah HANYA untuk WIG 4 dan HANYA jika bukan level UP3
                if (($isSuperAdmin || $isUid) && preg_match('/WIG\s*-?\s*4\b/i', $wig->judul ?? '')) {
                    $up2dUnit = \App\Models\MasterUnit::where('type', 'UP2D')->first();
                    $rows->push([
                        $wig->judul ?? '1',
                        $lm->judul_lm ?? '',
                        $up2dUnit ? $up2dUnit->name : 'UP2D',
                        '', '', '', '', '', ''
                    ]);
                }
            }
        }

        if (!$hasData) {
            foreach ($availableUnits as $unit) {
                $rows->push([
                    'WIG 1 - PENJUALAN',
                    'LM-1 Melaksanakan Penambahan Daya Tersambung',
                    $unit->name ?? ($isUp3 ? 'ULP CONTOH' : 'UP3 CONTOH'),
                    '100',
                    '20',
                    '20',
                    '20',
                    '20',
                    '20',
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'NO_WIG / WIG',
            'JUDUL_LM (INDIKATOR)',
            'NAMA UNIT',
            'TARGET BULANAN',
            'TARGET MINGGU-1',
            'TARGET MINGGU-2',
            'TARGET MINGGU-3',
            'TARGET MINGGU-4',
            'TARGET MINGGU-5',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']]
            ],
        ];
    }
}

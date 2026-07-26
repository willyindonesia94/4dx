<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RealisasiLmTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ["1", "LM-1 Intimasi Pelanggan (Contoh)", "9812345ZY", "Budi Santoso", "2026-07-01", "15.5", "https://drive.google.com/contoh-bukti"]
        ];
    }

    public function headings(): array
    {
        return [
            "NO",
            "JUDUL LM",
            "NIP PEGAWAI",
            "NAMA PEGAWAI",
            "TANGGAL INPUT (YYYY-MM-DD)",
            "ANGKA REALISASI",
            "BUKTI LINK"
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ["font" => ["bold" => true, "color" => ["argb" => "FFFFFFFF"]], "fill" => ["fillType" => "solid", "startColor" => ["argb" => "FF4F46E5"]]],
        ];
    }
}


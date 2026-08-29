<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MasterUnitTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [
            ['UP3 Bandung', 'UP3', 'UID Jawa Barat', '', ''],
            ['ULP Bandung Selatan', 'ULP', 'UP3 Bandung', '', ''],
            ['ULP Bandung Barat', 'ULP', 'UP3 Bandung', '', ''],
        ];
    }

    public function headings(): array
    {
        return [
            'NAMA UNIT',
            'TIPE (UID/UP3/UP2D/UP2K/ULP)',
            'NAMA INDUK UNIT',
            'LATITUDE',
            'LONGITUDE',
        ];
    }

    public function title(): string
    {
        return 'Template Master Unit';
    }
}

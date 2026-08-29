<?php

namespace App\Exports;

use App\Models\MasterUnit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class MasterUnitExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function collection()
    {
        return MasterUnit::with('parent')
            ->where('type', $this->type)
            ->get();
    }

    public function map($unit): array
    {
        return [
            $unit->name,
            $unit->type,
            $unit->parent ? $unit->parent->name : '',
            $unit->latitude,
            $unit->longitude,
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
        return 'Data Unit ' . $this->type;
    }
}

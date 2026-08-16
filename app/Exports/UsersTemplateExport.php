<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Nama', 
            'Email', 
            'Password', 
            'Role Name', 
            'Nama Unit', 
            'Nama Matrix Group'
        ];
    }

    public function array(): array
    {
        return [
            ['John Doe', 'john.doe@pln.co.id', 'pln12345', 'Manager UP3', 'UP3 Bandung', 'ALL'],
            ['Jane Doe', 'jane.doe@pln.co.id', 'pln12345', 'UP2K', 'UP2K Bandung', 'ALL']
        ];
    }
}

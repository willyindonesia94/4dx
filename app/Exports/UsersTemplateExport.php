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
            'Username',
            'Password', 
            'Role Name', 
            'Nama Unit', 
            'Nama Matrix Group'
        ];
    }

    public function array(): array
    {
        return [
            ['John Doe', 'johndoe', 'pln12345', 'Manager UP3', 'UP3 Bandung', 'ALL'],
            ['Jane Doe', 'janedoe', 'pln12345', 'UP2K', 'UP2K Bandung', 'ALL']
        ];
    }
}

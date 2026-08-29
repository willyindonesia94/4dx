<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MasterUnitImport implements ToArray, WithHeadingRow
{
    /**
    * @param array $array
    */
    public function array(array $array)
    {
        // This method is required by ToArray, but Excel::toArray() 
        // doesn't actually use it to return data. It just parses the file.
        // The data is returned directly by Excel::toArray().
    }
}

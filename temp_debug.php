<?php
use Maatwebsite\Excel\Facades\Excel;
Excel::import(new class implements \Maatwebsite\Excel\Concerns\ToCollection, \Maatwebsite\Excel\Concerns\WithHeadingRow {
    public function collection(\Illuminate\Support\Collection $rows) {
        foreach($rows as $row) {
            $unit = $row["unit"] ?? "";
            $wig = $row["no"] ?? "";
            if (str_contains(strtolower($unit), "jabar") || str_contains(strtolower($unit), "bandung")) {
                echo "\nWIG: $wig, UNIT: $unit";
                echo "\nKeys: " . implode(", ", $row->keys()->toArray());
                echo "\ntarget_jan: " . ($row["target_januari"] ?? "null");
                echo "\ntarget_des: " . ($row["target_desember"] ?? "null");
                echo "\n---\n";
            }
        }
    }
}, storage_path("app/public/uploads/wig_mass_import.xlsx"));


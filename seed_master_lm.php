<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MasterLm;
use App\Models\MasterSatuan;
use App\Models\MasterWig;

$file = fopen('data_lm_jan.csv', 'r');
if (!$file) {
    die("Failed to open file");
}

$lms = [];
$rowNum = 0;
while (($data = fgetcsv($file)) !== false) {
    $rowNum++;
    if ($rowNum < 4) continue; // Skip headers
    
    $no = trim($data[3]);
    if (!$no || strpos($no, 'WIG') === false) continue;
    
    $wigId = (int) preg_replace('/[^0-9]/', '', $no);
    $judulLm = trim($data[5]);
    $satuanName = trim($data[7]);
    
    if (!$judulLm) continue;
    
    $key = $wigId . '|' . $judulLm;
    if (!isset($lms[$key])) {
        $lms[$key] = [
            'wig_id' => $wigId,
            'judul_lm' => $judulLm,
            'satuan_name' => $satuanName
        ];
    }
}
fclose($file);

$count = 0;
foreach ($lms as $lmData) {
    // Check if WIG exists
    $wig = MasterWig::find($lmData['wig_id']);
    if (!$wig) {
        echo "WIG ID {$lmData['wig_id']} not found.\n";
        continue;
    }
    
    // Find or create Satuan
    $satuanId = null;
    if ($lmData['satuan_name']) {
        // Find existing case-insensitively
        $satuan = MasterSatuan::whereRaw('LOWER(name) = ?', [strtolower($lmData['satuan_name'])])->first();
        if (!$satuan) {
            $satuan = MasterSatuan::create(['name' => $lmData['satuan_name']]);
            echo "Created Satuan {$lmData['satuan_name']}\n";
        }
        $satuanId = $satuan->id;
    }
    
    // Check if LM exists
    $lm = MasterLm::where('wig_id', $lmData['wig_id'])
                  ->where('judul_lm', $lmData['judul_lm'])
                  ->first();
                  
    if (!$lm) {
        MasterLm::create([
            'wig_id' => $lmData['wig_id'],
            'judul_lm' => $lmData['judul_lm'],
            'satuan_id' => $satuanId,
            'polaritas' => 'positif',
            'tujuan_unit_role' => 'Divisi UP3',
            'is_approved' => true,
            'angka_target' => 0,
            'periode_start' => '2026-01-01',
            'periode_end' => '2026-12-31'
        ]);
        echo "Created LM {$lmData['judul_lm']} for WIG {$lmData['wig_id']}\n";
        $count++;
    } else {
        echo "LM {$lmData['judul_lm']} for WIG {$lmData['wig_id']} already exists.\n";
    }
}

echo "Seeded $count new LMs.\n";

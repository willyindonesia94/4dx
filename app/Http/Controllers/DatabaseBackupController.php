<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DatabaseBackupController extends Controller
{
    public function download()
    {
        // Pastikan hanya Super Admin yang bisa mengakses ini (sekalipun sudah di-protect via middleware)
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');

        $date = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "backup_4dx_{$date}.sql";
        $storagePath = storage_path('app/backups');

        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        $filePath = $storagePath . '/' . $filename;

        // Siapkan command mysqldump
        // Catatan: Jika password mengandung karakter spesial, dibungkus dengan kutip
        $passwordString = $password ? "-p\"{$password}\"" : "";
        $command = "mysqldump --user=\"{$username}\" {$passwordString} --host=\"{$host}\" --port=\"{$port}\" \"{$database}\" > \"{$filePath}\" 2>&1";

        $output = [];
        $returnVar = null;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            // Jika mysqldump gagal (bisa jadi karena exec di-disable di hosting, dsb.)
            // Kita log atau return error
            return back()->with('error', 'Gagal membackup database. Server mungkin tidak mendukung fungsi mysqldump via exec(). Pesan: ' . implode("\n", $output));
        }

        // Return file sebagai download, lalu hapus setelah di-download
        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}

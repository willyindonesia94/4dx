<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DatabaseBackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Show backup & restore dashboard.
     */
    public function index()
    {
        try {
            $stats = $this->backupService->getDatabaseStats();
            $backups = $this->backupService->getLocalBackups();

            return view('database-backup.index', compact('stats', 'backups'));
        } catch (\Exception $e) {
            Log::error('Error loading database backup page: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat halaman backup database: ' . $e->getMessage());
        }
    }

    /**
     * Export database and download or save to server.
     */
    public function export(Request $request)
    {
        try {
            $action = $request->input('action', 'download');
            $filePath = $this->backupService->exportToFile();
            $filename = basename($filePath);

            if ($action === 'download') {
                return response()->download($filePath, $filename);
            }

            return redirect()->back()->with('success', "Backup database berhasil disimpan di server: {$filename}");
        } catch (\Exception $e) {
            Log::error('Database export failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal melakukan export database: ' . $e->getMessage());
        }
    }

    /**
     * Import database from uploaded .sql file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_sql' => 'required|file',
        ]);

        $file = $request->file('file_sql');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['sql', 'txt'])) {
            return redirect()->back()->with('error', 'Format file harus berupa .sql');
        }

        try {
            $tempPath = $file->getRealPath();
            $result = $this->backupService->importFromFile($tempPath, true);

            $msg = "Database berhasil dipulihkan (Restore selesai dalam {$result['duration_seconds']} detik).";
            if (!empty($result['emergency_backup'])) {
                $msg .= " Cadangan darurat otomatis sebelum restore telah dibuat: {$result['emergency_backup']}";
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Database import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal melakukan restore database: ' . $e->getMessage());
        }
    }

    /**
     * Restore database from an existing local backup file.
     */
    public function restoreFromLocal(Request $request, string $filename)
    {
        try {
            $filePath = $this->backupService->getBackupPath($filename);
            $result = $this->backupService->importFromFile($filePath, true);

            $msg = "Database berhasil dipulihkan dari file {$filename} ({$result['duration_seconds']} detik).";
            if (!empty($result['emergency_backup'])) {
                $msg .= " Cadangan darurat otomatis dibuat: {$result['emergency_backup']}";
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            Log::error("Database restore from local failed ({$filename}): " . $e->getMessage());
            return redirect()->back()->with('error', "Gagal melakukan restore dari file {$filename}: " . $e->getMessage());
        }
    }

    /**
     * Download a local backup file.
     */
    public function downloadBackup(string $filename)
    {
        try {
            $filePath = $this->backupService->getBackupPath($filename);
            return response()->download($filePath, $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'File tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Delete a local backup file.
     */
    public function deleteBackup(string $filename)
    {
        try {
            $deleted = $this->backupService->deleteBackup($filename);
            if ($deleted) {
                return redirect()->back()->with('success', "File backup {$filename} berhasil dihapus.");
            }
            return redirect()->back()->with('error', "File backup {$filename} tidak ditemukan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus backup: ' . $e->getMessage());
        }
    }
}

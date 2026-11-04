<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Get database stats: name, table count, size in bytes and formatted string.
     */
    public function getDatabaseStats(): array
    {
        $database = config('database.connections.mysql.database');

        $tablesResult = DB::select("SHOW TABLES");
        $tables = [];
        foreach ($tablesResult as $row) {
            $tables[] = array_values((array)$row)[0];
        }

        $sizeResult = DB::selectOne("
            SELECT 
                SUM(data_length + index_length) AS total_size
            FROM information_schema.TABLES 
            WHERE table_schema = ?
        ", [$database]);

        $totalBytes = (int)($sizeResult->total_size ?? 0);

        return [
            'database' => $database,
            'tables_count' => count($tables),
            'tables' => $tables,
            'size_bytes' => $totalBytes,
            'size_formatted' => $this->formatBytes($totalBytes),
            'mysqldump_available' => $this->findBinary('mysqldump') !== null,
        ];
    }

    /**
     * Export database to a .sql file.
     *
     * @param string|null $filePath Target path. If null, a timestamped file in backup directory is generated.
     * @return string Absolute file path of generated backup.
     */
    public function exportToFile(?string $filePath = null): string
    {
        @ini_set('max_execution_time', '900');
        @set_time_limit(900);
        @ini_set('memory_limit', '512M');

        $database = config('database.connections.mysql.database');

        if (!$filePath) {
            $filename = 'backup_' . $database . '_' . date('Y-m-d_His') . '.sql';
            $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        }

        $mysqldumpPath = $this->findBinary('mysqldump');
        if ($mysqldumpPath) {
            $this->exportViaMysqldump($mysqldumpPath, $filePath);
        } else {
            $this->exportViaPhpPdo($filePath);
        }

        return $filePath;
    }

    /**
     * Import a .sql file into database.
     * Automatically creates an emergency snapshot backup before restoring.
     *
     * @param string $sourcePath Path to .sql file
     * @param bool $autoBackupBeforeRestore Whether to backup current db before restoring
     * @return array [success => bool, emergency_backup => string|null, duration_seconds => float]
     */
    public function importFromFile(string $sourcePath, bool $autoBackupBeforeRestore = true): array
    {
        @ini_set('max_execution_time', '900');
        @set_time_limit(900);
        @ini_set('memory_limit', '512M');

        if (!File::exists($sourcePath)) {
            throw new \InvalidArgumentException("File SQL tidak ditemukan: {$sourcePath}");
        }

        $startTime = microtime(true);

        // Emergency backup before restore
        $emergencyBackupPath = null;
        if ($autoBackupBeforeRestore) {
            $emergencyName = 'emergency_before_restore_' . date('Y-m-d_His') . '.sql';
            $emergencyBackupPath = $this->backupDir . DIRECTORY_SEPARATOR . $emergencyName;
            $this->exportToFile($emergencyBackupPath);
        }

        $mysqlPath = $this->findBinary('mysql');
        if ($mysqlPath) {
            $this->importViaMysqlCli($mysqlPath, $sourcePath);
        } else {
            $this->importViaPhpPdo($sourcePath);
        }

        $duration = round(microtime(true) - $startTime, 2);

        return [
            'success' => true,
            'emergency_backup' => $emergencyBackupPath ? basename($emergencyBackupPath) : null,
            'duration_seconds' => $duration,
        ];
    }

    /**
     * Export database using mysqldump CLI binary.
     */
    protected function exportViaMysqldump(string $binaryPath, string $targetPath): void
    {
        $dbConfig = config('database.connections.mysql');
        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? 3306;
        $username = $dbConfig['username'] ?? 'root';
        $password = $dbConfig['password'] ?? '';
        $database = $dbConfig['database'];

        $command = [
            $binaryPath,
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
        ];

        if (!empty($password)) {
            $command[] = "--password={$password}";
        }

        $command = array_merge($command, [
            "--quick",
            "--single-transaction",
            "--default-character-set=utf8mb4",
            "--result-file={$targetPath}",
            $database,
        ]);

        $process = new Process($command);
        $process->setTimeout(900);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("mysqldump gagal: " . $process->getErrorOutput());
        }
    }

    /**
     * Import database using mysql CLI binary.
     */
    protected function importViaMysqlCli(string $binaryPath, string $sourcePath): void
    {
        $dbConfig = config('database.connections.mysql');
        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? 3306;
        $username = $dbConfig['username'] ?? 'root';
        $password = $dbConfig['password'] ?? '';
        $database = $dbConfig['database'];

        // On Windows or Unix, run mysql binary with input redirection
        $cmdParts = [
            escapeshellarg($binaryPath),
            '--host=' . escapeshellarg($host),
            '--port=' . escapeshellarg($port),
            '--user=' . escapeshellarg($username),
        ];

        if (!empty($password)) {
            $cmdParts[] = '--password=' . escapeshellarg($password);
        }

        $cmdParts[] = '--default-character-set=utf8mb4';
        $cmdParts[] = escapeshellarg($database);
        $cmdParts[] = '<';
        $cmdParts[] = escapeshellarg($sourcePath);

        $commandLine = implode(' ', $cmdParts);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmdLine = "cmd /c \"{$commandLine}\"";
        } else {
            $cmdLine = $commandLine;
        }

        $process = Process::fromShellCommandline($cmdLine);
        $process->setTimeout(900);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("mysql CLI restore gagal: " . $process->getErrorOutput());
        }
    }

    /**
     * Export database fallback using streaming unbuffered PDO.
     */
    protected function exportViaPhpPdo(string $filePath): void
    {
        $database = config('database.connections.mysql.database');
        $fp = fopen($filePath, 'w');
        if (!$fp) {
            throw new \RuntimeException("Gagal membuka file tujuan untuk menulis backup: {$filePath}");
        }

        $now = date('Y-m-d H:i:s');
        fwrite($fp, "-- 4DX Application Database Backup (PHP Fallback)\n");
        fwrite($fp, "-- Database: {$database}\n-- Date: {$now}\n\n");
        fwrite($fp, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

        $tablesResult = DB::select("SHOW TABLES");
        $tables = [];
        foreach ($tablesResult as $row) {
            $tables[] = array_values((array)$row)[0];
        }

        $pdo = DB::connection()->getPdo();

        foreach ($tables as $table) {
            fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");
            $createResult = DB::selectOne("SHOW CREATE TABLE `{$table}`");
            $createTableSql = $createResult->{'Create Table'} ?? array_values((array)$createResult)[1] ?? null;
            if ($createTableSql) {
                fwrite($fp, $createTableSql . ";\n\n");
            }

            // Stream rows directly
            $stmt = $pdo->query("SELECT * FROM `{$table}`");
            $columns = null;
            $buffer = [];
            $batchSize = 500;

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if ($columns === null) {
                    $columns = array_keys($row);
                    $escapedCols = array_map(fn($c) => "`{$c}`", $columns);
                    $columnsList = implode(', ', $escapedCols);
                }

                $vals = [];
                foreach ($columns as $c) {
                    $val = $row[$c];
                    if (is_null($val)) {
                        $vals[] = 'NULL';
                    } elseif (is_numeric($val)) {
                        $vals[] = $val;
                    } else {
                        $vals[] = $pdo->quote($val);
                    }
                }
                $buffer[] = '(' . implode(', ', $vals) . ')';

                if (count($buffer) >= $batchSize) {
                    fwrite($fp, "INSERT INTO `{$table}` ({$columnsList}) VALUES \n" . implode(",\n", $buffer) . ";\n");
                    $buffer = [];
                }
            }

            if (!empty($buffer)) {
                fwrite($fp, "INSERT INTO `{$table}` ({$columnsList}) VALUES \n" . implode(",\n", $buffer) . ";\n");
            }

            fwrite($fp, "\n");
        }

        fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($fp);
    }

    /**
     * Import database fallback using line-by-line PDO statement execution.
     */
    protected function importViaPhpPdo(string $sourcePath): void
    {
        $fp = fopen($sourcePath, 'r');
        if (!$fp) {
            throw new \RuntimeException("Tidak dapat membaca file SQL: {$sourcePath}");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        $statement = '';
        $delimiter = ';';

        try {
            while (($line = fgets($fp)) !== false) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                    continue;
                }

                if (stripos($trimmed, 'DELIMITER ') === 0) {
                    $delimiter = trim(substr($trimmed, 10));
                    continue;
                }

                $statement .= $line;

                if (str_ends_with($trimmed, $delimiter)) {
                    $execSql = trim($statement);
                    if ($delimiter !== ';') {
                        $execSql = substr($execSql, 0, -strlen($delimiter));
                    }

                    if (!empty($execSql)) {
                        DB::unprepared($execSql);
                    }
                    $statement = '';
                }
            }

            $remaining = trim($statement);
            if (!empty($remaining)) {
                DB::unprepared($remaining);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            fclose($fp);
        }
    }

    /**
     * Get list of local backup files stored in storage/app/backups.
     */
    public function getLocalBackups(): array
    {
        if (!File::isDirectory($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['sql', 'gz', 'zip'])) {
                continue;
            }

            $filename = $file->getFilename();
            $size = $file->getSize();
            $mtime = $file->getMTime();
            $isEmergency = str_starts_with($filename, 'emergency_');

            $backups[] = [
                'filename' => $filename,
                'path' => $file->getPathname(),
                'size_bytes' => $size,
                'size_formatted' => $this->formatBytes($size),
                'created_at' => date('Y-m-d H:i:s', $mtime),
                'created_ago' => \Carbon\Carbon::createFromTimestamp($mtime)->diffForHumans(),
                'is_emergency' => $isEmergency,
            ];
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /**
     * Delete a backup file safely.
     */
    public function deleteBackup(string $filename): bool
    {
        $this->validateFilename($filename);
        $fullPath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (File::exists($fullPath)) {
            return File::delete($fullPath);
        }

        return false;
    }

    /**
     * Get absolute path for a backup file.
     */
    public function getBackupPath(string $filename): string
    {
        $this->validateFilename($filename);
        $fullPath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($fullPath)) {
            throw new \InvalidArgumentException("File backup tidak ditemukan: {$filename}");
        }

        return $fullPath;
    }

    /**
     * Find binary in system path or standard directories.
     */
    public function findBinary(string $binary): ?string
    {
        $isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $binaryName = $isWin ? "{$binary}.exe" : $binary;

        // 1. Check known directories
        $candidates = [];
        if ($isWin) {
            $candidates = [
                "C:\\Program Files\\xampp\\mysql\\bin\\{$binaryName}",
                "C:\\xampp\\mysql\\bin\\{$binaryName}",
                "D:\\xampp\\mysql\\bin\\{$binaryName}",
                "C:\\laragon\\bin\\mysql\\current\\bin\\{$binaryName}",
            ];
        } else {
            $candidates = [
                "/usr/bin/{$binaryName}",
                "/usr/local/bin/{$binaryName}",
                "/usr/local/mysql/bin/{$binaryName}",
            ];
        }

        foreach ($candidates as $cand) {
            if (file_exists($cand) && is_executable($cand)) {
                return $cand;
            }
        }

        // 2. Check PATH via where / which
        $checkCmd = $isWin ? "where {$binaryName}" : "which {$binaryName}";
        try {
            $proc = Process::fromShellCommandline($checkCmd);
            $proc->run();
            if ($proc->isSuccessful()) {
                $lines = explode("\n", trim($proc->getOutput()));
                $first = trim($lines[0] ?? '');
                if (!empty($first) && file_exists($first)) {
                    return $first;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return null;
    }

    /**
     * Prevent directory traversal attacks by validating filename.
     */
    protected function validateFilename(string $filename): void
    {
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename) || str_contains($filename, '..')) {
            throw new \InvalidArgumentException("Nama file tidak valid atau berbahaya: {$filename}");
        }
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

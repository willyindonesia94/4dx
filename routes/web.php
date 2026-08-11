<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/api/debug-sesi', function() {
    $sws = \App\Models\SesiWig::where('tahun', 2026)->where('bulan', 1)
        ->orderByRaw('CASE WHEN minggu_ke IS NULL THEN 1 ELSE 0 END, minggu_ke ASC')
        ->get(['id', 'minggu_ke', 'tipe_sesi']);
    return $sws;
});

Route::get('/run-migrations-temp', function () {
    try {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE realizations ADD COLUMN notes TEXT NULL AFTER realization_value');
        return 'Column added successfully!';
    } catch (\Exception $e) {
        // If it already exists, this might throw an error, which is fine
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/clear-all-data', function() {
    \Illuminate\Support\Facades\DB::table('realisasis')->truncate();
    return 'All realization data has been cleared. Ready for fresh upload.';
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // WIG & LM Management (Matrix Architecture)
    Route::post('/master-wigs/{id}/approve', [\App\Http\Controllers\MasterWigController::class, 'approve'])->name('master-wigs.approve');
    Route::resource('master-wigs', \App\Http\Controllers\MasterWigController::class)->except(['show']);
    
    Route::post('/master-lms/{id}/approve', [\App\Http\Controllers\LeadMeasureController::class, 'approve'])->name('master-lms.approve');
    Route::resource('master-lms', \App\Http\Controllers\LeadMeasureController::class)->except(['show']);
    
    Route::post('/master-periodes/generate', [\App\Http\Controllers\MasterPeriodeController::class, 'generate'])->name('master-periodes.generate');
    Route::resource('master-periodes', \App\Http\Controllers\MasterPeriodeController::class)->except(['show', 'create', 'store', 'destroy']);
    Route::get('/cascading/wig', [\App\Http\Controllers\CascadingController::class, 'wigIndex'])->name('cascading.wig.index');
    Route::get('/cascading/lm', [\App\Http\Controllers\CascadingController::class, 'lmIndex'])->name('cascading.lm.index');
    Route::get('/cascading/wig/template', [\App\Http\Controllers\CascadingController::class, 'wigTemplate'])->name('cascading.wig.template');
    Route::post('/cascading/wig/import', [\App\Http\Controllers\CascadingController::class, 'wigImport'])->name('cascading.wig.import');
    Route::get('/cascading/lm/template', [\App\Http\Controllers\CascadingController::class, 'lmTemplate'])->name('cascading.lm.template');
    Route::post('/cascading/lm/import', [\App\Http\Controllers\CascadingController::class, 'lmImport'])->name('cascading.lm.import');
    Route::get('/cascading/lm/breakdown/template', [\App\Http\Controllers\CascadingController::class, 'breakdownLmTemplate'])->name('cascading.breakdown.template');
    Route::post('/cascading/lm/breakdown/import', [\App\Http\Controllers\CascadingController::class, 'importBreakdownLm'])->name('cascading.breakdown.import');
    Route::post('/cascading/breakdown', [\App\Http\Controllers\CascadingController::class, 'storeBreakdown'])->name('cascading.breakdown.store');
    Route::put('/cascading/breakdown/{id}', [\App\Http\Controllers\CascadingController::class, 'updateBreakdown'])->name('cascading.breakdown.update');
    Route::delete('/cascading/breakdown/{id}', [\App\Http\Controllers\CascadingController::class, 'destroyBreakdown'])->name('cascading.breakdown.destroy');
    Route::post('/cascading/wig-breakdown', [\App\Http\Controllers\CascadingController::class, 'storeWigBreakdown'])->name('cascading.wig-breakdown.store');
    Route::put('/cascading/wig-breakdown/{id}', [\App\Http\Controllers\CascadingController::class, 'updateWigBreakdown'])->name('cascading.wig-breakdown.update');
    Route::delete('/cascading/wig-breakdown/{id}', [\App\Http\Controllers\CascadingController::class, 'destroyWigBreakdown'])->name('cascading.wig-breakdown.destroy');

    
    // Master Data Tambahan
    Route::resource('master-bidangs', \App\Http\Controllers\MasterBidangController::class)->except(['create', 'edit', 'show']);
    Route::resource('master-satuans', \App\Http\Controllers\MasterSatuanController::class)->except(['create', 'edit', 'show']);
    Route::resource('master-units', \App\Http\Controllers\MasterUnitController::class)->except(['create', 'edit', 'show']);

    // Sesi WIG & Realizations
    Route::post('sesi-wigs/generate', [\App\Http\Controllers\SesiWigController::class, 'generate'])->name('sesi-wigs.generate');
    Route::put('sesi-wigs/{sesi_wig}/notes', [\App\Http\Controllers\SesiWigController::class, 'updateNotes'])->name('sesi-wigs.update-notes');
    Route::post('sesi-wigs/{sesi_wig}/set-presenter', [\App\Http\Controllers\SesiWigController::class, 'setPresenter'])->name('sesi-wigs.set-presenter');
    Route::post('sesi-wigs/{sesi_wig}/komitmen', [\App\Http\Controllers\SesiWigController::class, 'saveKomitmen'])->name('sesi-wigs.save-komitmen');
    
    // API Routes for Form Komitmen LM (Per Unit & LM)
    Route::get('sesi-wigs/{sesi_wig}/komitmen/{lm_id}/{unit_id}', [\App\Http\Controllers\SesiWigKomitmenController::class, 'show'])->name('sesi-wigs.komitmen.show');
    Route::post('sesi-wigs/{sesi_wig}/komitmen/{lm_id}/{unit_id}', [\App\Http\Controllers\SesiWigKomitmenController::class, 'store'])->name('sesi-wigs.komitmen.store');

    Route::resource('sesi-wigs', \App\Http\Controllers\SesiWigController::class);
    

    Route::get('/realisasis/template', [\App\Http\Controllers\RealizationController::class, 'downloadTemplate'])->name('realisasis.template')->middleware('role:Super Admin|Admin UID');
    Route::post('/realisasis/import', [\App\Http\Controllers\RealizationController::class, 'import'])->name('realisasis.import')->middleware('role:Super Admin|Admin UID');
    Route::resource('realisasis', \App\Http\Controllers\RealizationController::class)->except(['show']);
    
    // Realisasi WIG
    Route::get('/realisasi-wig', [\App\Http\Controllers\RealisasiWigController::class, 'index'])->name('realisasi-wig.index');
    Route::post('/realisasi-wig', [\App\Http\Controllers\RealisasiWigController::class, 'store'])->name('realisasi-wig.store');
    Route::put('/realisasi-wig/{realisasi_wig}', [\App\Http\Controllers\RealisasiWigController::class, 'update'])->name('realisasi-wig.update');
    Route::delete('/realisasi-wig/{realisasi_wig}', [\App\Http\Controllers\RealisasiWigController::class, 'destroy'])->name('realisasi-wig.destroy');
    Route::get('/realisasi-wig/template', [\App\Http\Controllers\RealisasiWigController::class, 'downloadTemplate'])->name('realisasi-wig.template')->middleware('role:Super Admin|Admin UID');
    Route::post('/realisasi-wig/import', [\App\Http\Controllers\RealisasiWigController::class, 'import'])->name('realisasi-wig.import')->middleware('role:Super Admin|Admin UID');
    Route::get('/realisasi-wig/target', [\App\Http\Controllers\RealisasiWigController::class, 'getTargetBulanan'])->name('realisasi-wig.target');

    // Laporan Bulanan & Import Historis
    Route::get('/laporan-bulanan', [\App\Http\Controllers\LaporanBulananController::class, 'index'])->name('laporan.index');
    Route::get('/laporan-bulanan/template', [\App\Http\Controllers\LaporanBulananController::class, 'downloadTemplate'])->name('laporan.template');
    Route::post('/laporan-bulanan/import', [\App\Http\Controllers\LaporanBulananController::class, 'importData'])->name('laporan.import');
    Route::get('/laporan-bulanan/export', [\App\Http\Controllers\LaporanBulananController::class, 'exportLaporan'])->name('laporan.export');
    Route::get('/laporan-bulanan/export-wig', [\App\Http\Controllers\LaporanBulananController::class, 'exportWig'])->name('laporan.exportWig');
    Route::get('/laporan-bulanan/export-lengkap', [\App\Http\Controllers\LaporanBulananController::class, 'exportLengkap'])->name('laporan.exportLengkap');

    // User Management (Superadmin Only)
    Route::resource('users', \App\Http\Controllers\UserController::class)
        ->middleware(['role:Super Admin|Admin UID']);
        
    // Audit Log
    Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])
        ->middleware(['role:Super Admin|Admin UID|General Manager UID|Manager UP3|Manager ULP|Admin UP3|Admin ULP'])
        ->name('audit-logs.index');
});

Route::get('/run-migrate-temp', function() {
    return 'Done';
});

require __DIR__.'/auth.php';

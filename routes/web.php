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
    Route::get('/cascading/lm/breakdown/template-k3l', [\App\Http\Controllers\CascadingController::class, 'breakdownLmTemplateK3L'])->name('cascading.breakdown.template-k3l');
    Route::post('/cascading/lm/breakdown/import', [\App\Http\Controllers\CascadingController::class, 'importBreakdownLm'])->name('cascading.breakdown.import');
    Route::post('/cascading/breakdown', [\App\Http\Controllers\CascadingController::class, 'storeBreakdown'])->name('cascading.breakdown.store');
    Route::delete('/cascading/breakdown-lm/bulk', [\App\Http\Controllers\CascadingController::class, 'bulkDestroyLm'])->name('cascading.breakdown.bulk-destroy');
    Route::post('/cascading/breakdown-lm/bulk-approve', [\App\Http\Controllers\CascadingController::class, 'bulkApproveLm'])->name('cascading.breakdown.bulk-approve');
    Route::put('/cascading/breakdown/{id}', [\App\Http\Controllers\CascadingController::class, 'updateBreakdown'])->name('cascading.breakdown.update');
    Route::delete('/cascading/breakdown/{id}', [\App\Http\Controllers\CascadingController::class, 'destroyBreakdown'])->name('cascading.breakdown.destroy');
    Route::post('/cascading/breakdown/{id}/approve', [\App\Http\Controllers\CascadingController::class, 'approveLmBreakdown'])->name('cascading.breakdown.approve');
    Route::post('/cascading/wig-breakdown', [\App\Http\Controllers\CascadingController::class, 'storeWigBreakdown'])->name('cascading.wig-breakdown.store');
    Route::put('/cascading/wig-breakdown/{id}', [\App\Http\Controllers\CascadingController::class, 'updateWigBreakdown'])->name('cascading.wig-breakdown.update');
    Route::delete('/cascading/wig-breakdown/{id}', [\App\Http\Controllers\CascadingController::class, 'destroyWigBreakdown'])->name('cascading.wig-breakdown.destroy');
    Route::post('/cascading/wig-breakdown/{id}/approve', [\App\Http\Controllers\CascadingController::class, 'approveWigBreakdown'])->name('cascading.wig-breakdown.approve');
    Route::delete('/cascading/wig-breakdown-bulk', [\App\Http\Controllers\CascadingController::class, 'bulkDestroyWigBreakdown'])->name('cascading.wig-breakdown.bulk-destroy');
    Route::post('/cascading/wig-breakdown-bulk-approve', [\App\Http\Controllers\CascadingController::class, 'bulkApproveWigBreakdown'])->name('cascading.wig-breakdown.bulk-approve');

    
    // Master Data Tambahan
    Route::resource('master-bidangs', \App\Http\Controllers\MasterBidangController::class)->except(['create', 'edit', 'show']);
    Route::resource('master-satuans', \App\Http\Controllers\MasterSatuanController::class)->except(['create', 'edit', 'show']);
    Route::get('/master-units/download-template', [\App\Http\Controllers\MasterUnitController::class, 'downloadTemplate'])->name('master-units.template');
    Route::get('/master-units/export', [\App\Http\Controllers\MasterUnitController::class, 'export'])->name('master-units.export');
    Route::post('/master-units/import', [\App\Http\Controllers\MasterUnitController::class, 'import'])->name('master-units.import');
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
    

    Route::get('/realisasis/template', [\App\Http\Controllers\RealizationController::class, 'downloadTemplate'])->name('realisasis.template')->middleware('role:Super Admin|Perencanaan UID|Asman Perencanaan UP3|Asman Bidang UP3');
    Route::get('/realisasis/template-k3l', [\App\Http\Controllers\RealizationController::class, 'downloadTemplateK3L'])->name('realisasis.template-k3l')->middleware('role:Super Admin|Perencanaan UID|Asman Perencanaan UP3|Asman Bidang UP3|Bidang K3L (MSB)');
    Route::post('/realisasis/import', [\App\Http\Controllers\RealizationController::class, 'import'])->name('realisasis.import')->middleware('role:Super Admin|Perencanaan UID|Asman Perencanaan UP3|Asman Bidang UP3');
    Route::delete('/realisasis/bulk-destroy', [\App\Http\Controllers\RealizationController::class, 'bulkDestroy'])->name('realisasis.bulk-destroy');
    Route::resource('realisasis', \App\Http\Controllers\RealizationController::class)->except(['show']);
    
    // Realisasi WIG
    Route::get('/realisasi-wig', [\App\Http\Controllers\RealisasiWigController::class, 'index'])->name('realisasi-wig.index');
    Route::post('/realisasi-wig', [\App\Http\Controllers\RealisasiWigController::class, 'store'])->name('realisasi-wig.store');
    Route::put('/realisasi-wig/{realisasi_wig}', [\App\Http\Controllers\RealisasiWigController::class, 'update'])->name('realisasi-wig.update');
    Route::delete('/realisasi-wig/bulk-destroy', [\App\Http\Controllers\RealisasiWigController::class, 'bulkDestroy'])->name('realisasi-wig.bulk-destroy');
    Route::delete('/realisasi-wig/{realisasi_wig}', [\App\Http\Controllers\RealisasiWigController::class, 'destroy'])->name('realisasi-wig.destroy');
    Route::get('/realisasi-wig/template', [\App\Http\Controllers\RealisasiWigController::class, 'downloadTemplate'])->name('realisasi-wig.template')->middleware('role:Super Admin|Perencanaan UID|Asman Perencanaan UP3');
    Route::post('/realisasi-wig/import', [\App\Http\Controllers\RealisasiWigController::class, 'import'])->name('realisasi-wig.import')->middleware('role:Super Admin|Perencanaan UID|Asman Perencanaan UP3');
    Route::get('/realisasi-wig/target', [\App\Http\Controllers\RealisasiWigController::class, 'getTargetBulanan'])->name('realisasi-wig.target');

    // Laporan Bulanan & Import Historis
    Route::get('/laporan-bulanan', [\App\Http\Controllers\LaporanBulananController::class, 'index'])->name('laporan.index');
    Route::get('/laporan-bulanan/template', [\App\Http\Controllers\LaporanBulananController::class, 'downloadTemplate'])->name('laporan.template');
    Route::post('/laporan-bulanan/import', [\App\Http\Controllers\LaporanBulananController::class, 'importData'])->name('laporan.import');
    Route::get('/laporan-bulanan/export', [\App\Http\Controllers\LaporanBulananController::class, 'exportLaporan'])->name('laporan.export');
    Route::get('/laporan-bulanan/export-wig', [\App\Http\Controllers\LaporanBulananController::class, 'exportWig'])->name('laporan.exportWig');
    Route::get('/laporan-bulanan/export-lengkap', [\App\Http\Controllers\LaporanBulananController::class, 'exportLengkap'])->name('laporan.exportLengkap');
    Route::get('/laporan-bulanan/preview', [\App\Http\Controllers\LaporanBulananController::class, 'previewReport'])->name('laporan.preview');

    // User Management (Superadmin Only)
    Route::get('/users/template', [\App\Http\Controllers\UserController::class, 'template'])->name('users.template');
    Route::post('/users/preview-import', [\App\Http\Controllers\UserController::class, 'previewImport'])->name('users.preview_import');
    Route::post('/users/import', [\App\Http\Controllers\UserController::class, 'import'])->name('users.import');
    Route::resource('users', \App\Http\Controllers\UserController::class)
        ->middleware(['role:Super Admin|Perencanaan UID']);
        
    // Audit Log
    Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])
        ->middleware(['role:Super Admin|Perencanaan UID|General Manager UID|Manager UP3|Manager ULP|Perencanaan UP3|UP2D|UP2K|Staff ULP'])
        ->name('audit-logs.index');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/check', [\App\Http\Controllers\NotificationController::class, 'getUnread'])->name('notifications.check');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/clear-all', [\App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clearAll');
});

Route::get('/run-migrate-temp', function() {
    return 'Done';
});

Route::get('/debug-up2d', function() {
    $wig = \App\Models\MasterWig::find(4);
    return response()->json([
        'wig' => $wig
    ]);
});

require __DIR__.'/auth.php';

Route::get('/debug-bw', function() {
    $bw = \App\Models\BreakdownWig::where('wig_id', 1)->whereHas('unit', function($q){ $q->where('type', 'UID'); })->first();
    if ($bw) {
        $bw->target_jan = 286.97;
        $bw->target_feb = 554.84;
        $bw->target_mar = 844.69;
        $bw->target_apr = 1105.02;
        $bw->target_mei = 1409.64;
        $bw->target_jun = 1701.43;
        $bw->target_jul = 2004.17;
        $bw->target_agu = 2308.39;
        $bw->target_sep = 2604.30;
        $bw->target_okt = 2917.82;
        $bw->target_nov = 3215.11;
        $bw->target_des = 3512.28;
        $bw->target_tahunan = 3512.00;
        $bw->save();
        \Illuminate\Support\Facades\Log::info("WIG-1 targets updated manually.");
    }
    return response()->json($bw);
});

Route::get('/run-migration', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate');
    return 'Migration run successfully.';
});

Route::get('/run-migration-specific', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => 'database/migrations/2026_08_14_000000_add_polaritas_to_master_lms.php']);
    return 'Specific migration run successfully.';
});

Route::get('/check-migration', function () {
    return \Illuminate\Support\Facades\Schema::hasColumn('master_lms', 'polaritas') ? 'yes' : 'no';
});

Route::get('/add-column', function () {
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE master_lms ADD COLUMN polaritas ENUM("positif", "negatif") DEFAULT "positif"');
    return 'Column added.';
});

Route::get('/list-columns', function () {
    return implode(', ', \Illuminate\Support\Facades\Schema::getColumnListing('master_lms'));
});

Route::get('/test-lm', function () {
    return \App\Models\MasterLm::first()->polaritas ?? 'null';
});

Route::get('/check-syntax', function () {
    $file = base_path('app/Http/Controllers/DashboardController.php');
    exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $return_var);
    return implode("
", $output);
});

Route::get('/run-test', function () {
    require base_path('test_syntax.php');
});

Route::get('/check-wig4', function () {
    return \App\Models\MasterWig::find(4)->polaritas ?? 'null';
});

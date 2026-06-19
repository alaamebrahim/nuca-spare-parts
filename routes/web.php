<?php

use App\Http\Controllers\Reports\InstallationOperationsReportController;
use App\Http\Controllers\Reports\SparePartsReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to('/admin');
});

Route::middleware(['web', 'auth'])->prefix('admin/reports')->group(function () {
    Route::get('spare-parts', SparePartsReportController::class)
        ->middleware('can:page_SparePartsReport')
        ->name('reports.spare-parts');
    Route::get('installation-operations', InstallationOperationsReportController::class)
        ->middleware('can:page_InstallationOperationsReport')
        ->name('reports.installation-operations');
});

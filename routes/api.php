<?php

use Illuminate\Support\Facades\Route;
use JSandaruwan\LaravelJobMonitor\Http\Controllers\JobMonitorController;

Route::get('/jobs', [JobMonitorController::class, 'index'])->name('job-monitor.index');
Route::get('/jobs/stats', [JobMonitorController::class, 'stats'])->name('job-monitor.stats');
Route::get('/jobs/{id}', [JobMonitorController::class, 'show'])->name('job-monitor.show');
Route::post('/jobs/{id}/retry', [JobMonitorController::class, 'retry'])->name('job-monitor.retry');

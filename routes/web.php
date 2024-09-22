<?php

use App\Http\Controllers\FeedReportController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/', [FeedReportController::class, 'index'])->name('report.index')->middleware('web');
Route::post('/generate', [FeedReportController::class, 'generateReport'])->name('report.generate')->middleware('web');

Route::get('/lang/{lang}', function($lang) {
    if (in_array($lang, ['en', 'uk'])) {
        session(['locale' => $lang]);
    }
    return redirect()->back();
})->name('lang.switch')->middleware('web');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FontConverterController;

Route::get('/', [FontConverterController::class, 'index'])->name('converter.index');
Route::post('/convert', [FontConverterController::class, 'convert'])
    ->middleware('throttle:upload')
    ->name('converter.process');
Route::get('/download/{conversion}', [FontConverterController::class, 'download'])->name('converter.download');

Route::get('/google-fonts/search', [FontConverterController::class, 'searchGoogleFonts'])
    ->middleware('throttle:search')
    ->name('google-fonts.search');
Route::post('/google-fonts/convert', [FontConverterController::class, 'convertGoogleFont'])
    ->middleware('throttle:upload')
    ->name('google-fonts.convert');
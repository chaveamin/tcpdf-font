<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FontConverterController;

Route::get('/', [FontConverterController::class, 'index'])->name('converter.index');
Route::post('/convert', [FontConverterController::class, 'convert'])->name('converter.process');
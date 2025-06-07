<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConverterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Converter routes
Route::get('/', [ConverterController::class, 'index'])->name('converter.index');
Route::get('/converter', [ConverterController::class, 'index'])->name('converter.index');
Route::post('/convert', [ConverterController::class, 'convert'])->name('convert');
Route::get('/units/{type}', [ConverterController::class, 'getUnitsByType'])->name('units.by-type');

// Alternative routes for different naming conventions
Route::get('/unit-converter', [ConverterController::class, 'index'])->name('unit-converter');
Route::post('/unit-convert', [ConverterController::class, 'convert'])->name('unit-convert');
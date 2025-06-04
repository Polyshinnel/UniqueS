<?php

use App\Http\Controllers\Guides\GuidesCategories;
use App\Http\Controllers\Guides\GuidesMain;
use App\Http\Controllers\Guides\GuidesRegions;
use App\Http\Controllers\Guides\GuidesSources;
use App\Http\Controllers\Guides\GuidesUsers;
use App\Http\Controllers\Guides\GuidesWarehouses;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/guide', [GuidesMain::class, 'index']);
Route::get('/guide/users', [GuidesUsers::class, 'index'])->name('users.index');
Route::get('/guide/regions', [GuidesRegions::class, 'index'])->name('regions.index');
Route::post('/guide/regions', [GuidesRegions::class, 'store'])->name('regions.store');
Route::delete('/guide/regions/{region}', [GuidesRegions::class, 'destroy'])->name('regions.destroy');

Route::get('/guide/sources', [GuidesSources::class, 'index']);
Route::post('/guide/sources', [GuidesSources::class, 'store'])->name('sources.store');
Route::delete('/guide/sources/{source}', [GuidesSources::class, 'destroy'])->name('sources.destroy');

Route::get('/guide/categories', [GuidesCategories::class, 'index']);
Route::post('/guide/categories', [GuidesCategories::class, 'store'])->name('categories.store');
Route::delete('/guide/categories/{category}', [GuidesCategories::class, 'destroy'])->name('categories.destroy');

Route::get('/guide/warehouses', [GuidesWarehouses::class, 'index'])->name('warehouses.index');
Route::post('/guide/warehouses', [GuidesWarehouses::class, 'store'])->name('warehouses.store');
Route::delete('/guide/warehouses/{warehouse}', [GuidesWarehouses::class, 'destroy'])->name('warehouses.destroy');

Route::post('/guide/users', [GuidesUsers::class, 'store'])->name('users.store');
Route::delete('/guide/users/{user}', [GuidesUsers::class, 'destroy'])->name('users.destroy');

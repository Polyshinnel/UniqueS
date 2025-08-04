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

Route::get('/company', [\App\Http\Controllers\Company\CompanyController::class, 'index'])->name('companies.index');
Route::get('/company/create', [\App\Http\Controllers\Company\CompanyController::class, 'create'])->name('companies.create');
Route::post('/company', [\App\Http\Controllers\Company\CompanyController::class, 'store'])->name('companies.store');
Route::get('/company/{company}', [\App\Http\Controllers\Company\CompanyController::class, 'show'])->name('companies.show');
Route::patch('/company/{company}/status', [\App\Http\Controllers\Company\CompanyController::class, 'updateStatus'])->name('companies.update-status');
Route::get('/company/next-sku/{warehouseName}', [\App\Http\Controllers\Company\CompanyController::class, 'getNextSku'])->name('companies.next-sku');

Route::get('/product', [\App\Http\Controllers\Product\ProductController::class, 'index'])->name('products.index');
Route::get('/product/create', [\App\Http\Controllers\Product\ProductController::class, 'create'])->name('products.create');
Route::post('/product', [\App\Http\Controllers\Product\ProductController::class, 'store'])->name('products.store');
Route::get('/product/{product}', [\App\Http\Controllers\Product\ProductController::class, 'show'])->name('products.show');
Route::get('/product/{product}/edit', [\App\Http\Controllers\Product\ProductController::class, 'edit'])->name('products.edit');
Route::put('/product/{product}', [\App\Http\Controllers\Product\ProductController::class, 'update'])->name('products.update');
Route::patch('/product/{product}/comment', [\App\Http\Controllers\Product\ProductController::class, 'updateComment'])->name('products.update-comment');


Route::get('/adv', [\App\Http\Controllers\AdvPageController::class, 'index'])->name('adv.index');

// Роуты для объявлений
Route::get('/advertisements', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'index'])->name('advertisements.index');
Route::get('/advertisements/create', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'create'])->name('advertisements.create');
Route::post('/advertisements', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'store'])->name('advertisements.store');
Route::get('/advertisements/{advertisement}', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'show'])->name('advertisements.show');
Route::get('/advertisements/{advertisement}/edit', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'edit'])->name('advertisements.edit');
Route::put('/advertisements/{advertisement}', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'update'])->name('advertisements.update');
Route::delete('/advertisements/{advertisement}', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'destroy'])->name('advertisements.destroy');
Route::post('/advertisements/{advertisement}/publish', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'publish'])->name('advertisements.publish');
Route::post('/advertisements/copy-from-product', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'copyFromProduct'])->name('advertisements.copy-from-product');
Route::get('/advertisements/product/{product}/media', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'getProductMedia'])->name('advertisements.product-media');
Route::get('/advertisements/product-statuses', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'getProductStatuses'])->name('advertisements.product-statuses');
Route::delete('/advertisements/{advertisement}/media', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'deleteMedia'])->name('advertisements.delete-media');


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

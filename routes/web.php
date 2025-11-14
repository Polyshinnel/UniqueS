<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Guides\GuidesCategories;
use App\Http\Controllers\Guides\GuidesImportExport;
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

// Защищенные роуты для компаний
Route::middleware(['auth'])->group(function () {
    Route::get('/company', [\App\Http\Controllers\Company\CompanyController::class, 'index'])->name('companies.index');
    Route::get('/company/create', [\App\Http\Controllers\Company\CompanyController::class, 'create'])->name('companies.create');
    Route::post('/company', [\App\Http\Controllers\Company\CompanyController::class, 'store'])->name('companies.store');
    Route::get('/company/{company}', [\App\Http\Controllers\Company\CompanyController::class, 'show'])->name('companies.show');
    Route::patch('/company/{company}/status', [\App\Http\Controllers\Company\CompanyController::class, 'updateStatus'])->name('companies.update-status');
    Route::get('/company/{company}/logs', [\App\Http\Controllers\Company\CompanyController::class, 'getLogs'])->name('companies.logs');
    Route::get('/company/{company}/actions', [\App\Http\Controllers\Company\CompanyController::class, 'getActions'])->name('companies.actions');
    Route::post('/company/{company}/actions', [\App\Http\Controllers\Company\CompanyController::class, 'storeAction'])->name('companies.store-action');
    Route::post('/company/{company}/actions/{actionId}/complete', [\App\Http\Controllers\Company\CompanyController::class, 'completeAction'])->name('companies.complete-action');
    Route::get('/company/next-sku/{warehouseName}', [\App\Http\Controllers\Company\CompanyController::class, 'getNextSku'])->name('companies.next-sku');
    Route::get('/company/regionals/warehouse/{warehouseId}/{regionId?}', [\App\Http\Controllers\Company\CompanyController::class, 'getRegionalsByWarehouse'])->name('companies.regionals-by-warehouse');
    Route::get('/company/warehouse-regions/{warehouseId}', [\App\Http\Controllers\Company\CompanyController::class, 'getWarehouseRegions'])->name('companies.warehouse-regions');
    Route::get('/company/{company}/info', [\App\Http\Controllers\Company\CompanyController::class, 'getCompanyInfo'])->name('companies.info');
    Route::patch('/company/{company}/common-info', [\App\Http\Controllers\Company\CompanyController::class, 'updateCommonInfo'])->name('companies.update-common-info');
    Route::patch('/company/{company}/contact-info', [\App\Http\Controllers\Company\CompanyController::class, 'updateContactInfo'])->name('companies.update-contact-info');
    Route::patch('/company/{company}/contacts', [\App\Http\Controllers\Company\CompanyController::class, 'updateContacts'])->name('companies.update-contacts');
    Route::patch('/company/{company}/addresses', [\App\Http\Controllers\Company\CompanyController::class, 'updateAddresses'])->name('companies.update-addresses');
    Route::patch('/company/{company}/legal-info', [\App\Http\Controllers\Company\CompanyController::class, 'updateLegalInfo'])->name('companies.update-legal-info');
    Route::patch('/company/{company}/name', [\App\Http\Controllers\Company\CompanyController::class, 'updateName'])->name('companies.update-name');
    Route::patch('/company/{company}/change-owner', [\App\Http\Controllers\Company\CompanyController::class, 'changeOwner'])->name('companies.change-owner');
    Route::get('/company/{company}/available-owners', [\App\Http\Controllers\Company\CompanyController::class, 'getAvailableOwners'])->name('companies.available-owners');
    Route::patch('/company/{company}/change-regional', [\App\Http\Controllers\Company\CompanyController::class, 'changeRegional'])->name('companies.change-regional');
    Route::get('/company/{company}/available-regionals', [\App\Http\Controllers\Company\CompanyController::class, 'getAvailableRegionals'])->name('companies.available-regionals');

    Route::get('/product', [\App\Http\Controllers\Product\ProductController::class, 'index'])->name('products.index');
    Route::get('/product/create', [\App\Http\Controllers\Product\ProductController::class, 'create'])->name('products.create');
    Route::post('/product', [\App\Http\Controllers\Product\ProductController::class, 'store'])
        ->middleware('large.file.upload')
        ->name('products.store');
    Route::get('/product/{product}', [\App\Http\Controllers\Product\ProductController::class, 'show'])->name('products.show');
    Route::get('/product/{product}/edit', [\App\Http\Controllers\Product\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/product/{product}', [\App\Http\Controllers\Product\ProductController::class, 'update'])
        ->middleware('large.file.upload')
        ->name('products.update');
    Route::patch('/product/{product}/comment', [\App\Http\Controllers\Product\ProductController::class, 'updateComment'])->name('products.update-comment');
    Route::patch('/product/{product}/loading-status', [\App\Http\Controllers\Product\ProductController::class, 'updateLoadingStatus'])->name('products.update-loading-status');
    Route::patch('/product/{product}/removal-status', [\App\Http\Controllers\Product\ProductController::class, 'updateRemovalStatus'])->name('products.update-removal-status');
    Route::patch('/product/{product}/check-status', [\App\Http\Controllers\Product\ProductController::class, 'updateCheckStatus'])->name('products.update-check-status');
    Route::patch('/product/{product}/payment-variants', [\App\Http\Controllers\Product\ProductController::class, 'updatePaymentVariants'])->name('products.update-payment-variants');
    Route::patch('/product/{product}/characteristics', [\App\Http\Controllers\Product\ProductController::class, 'updateCharacteristics'])->name('products.update-characteristics');
    Route::patch('/product/{product}/status', [\App\Http\Controllers\Product\ProductController::class, 'updateStatus'])->name('products.update-status');
    Route::patch('/product/{product}/main-info', [\App\Http\Controllers\Product\ProductController::class, 'updateMainInfo'])->name('products.update-main-info');
    Route::patch('/product/{product}/title', [\App\Http\Controllers\Product\ProductController::class, 'updateTitle'])->name('products.update-title');
    Route::get('/product/{product}/download-media', [\App\Http\Controllers\Product\ProductController::class, 'downloadMedia'])
        ->middleware('large.file.upload')
        ->name('products.download-media');
    Route::get('/product/{product}/media-list', [\App\Http\Controllers\Product\ProductController::class, 'getMediaList'])->name('products.media-list');
    Route::get('/product/{product}/download-media/{mediaId}', [\App\Http\Controllers\Product\ProductController::class, 'downloadSingleMedia'])->name('products.download-single-media');
    Route::post('/product/{product}/upload-media', [\App\Http\Controllers\Product\ProductController::class, 'uploadAdditionalMedia'])
        ->middleware('large.file.upload')
        ->name('products.upload-media');
    Route::delete('/product/{product}/media/{mediaId}', [\App\Http\Controllers\Product\ProductController::class, 'deleteMedia'])->name('products.delete-media');

    // Маршруты для логов и действий товаров
    Route::get('/product/{product}/logs', [\App\Http\Controllers\Product\ProductController::class, 'getLogs'])->name('products.logs');
    Route::get('/product/{product}/actions', [\App\Http\Controllers\Product\ProductController::class, 'getActions'])->name('products.actions');
    Route::post('/product/{product}/actions', [\App\Http\Controllers\Product\ProductController::class, 'storeAction'])->name('products.store-action');
    Route::post('/product/{product}/actions/{actionId}/complete', [\App\Http\Controllers\Product\ProductController::class, 'completeAction'])->name('products.complete-action');

    Route::get('/adv', [\App\Http\Controllers\AdvPageController::class, 'index'])->name('adv.index');

    // Роуты для объявлений
    Route::get('/advertisements', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'index'])->name('advertisements.index');
    Route::get('/advertisements/create', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'create'])->name('advertisements.create');
    Route::post('/advertisements', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'store'])
        ->middleware('large.file.upload')
        ->name('advertisements.store');
    Route::get('/advertisements/{advertisement}', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'show'])->name('advertisements.show');
    Route::get('/advertisements/{advertisement}/edit', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'edit'])->name('advertisements.edit');
    Route::put('/advertisements/{advertisement}', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'update'])
        ->middleware('large.file.upload')
        ->name('advertisements.update');
    Route::delete('/advertisements/{advertisement}', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'destroy'])->name('advertisements.destroy');
    Route::post('/advertisements/{advertisement}/publish', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'publish'])->name('advertisements.publish');
    Route::post('/advertisements/copy-from-product', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'copyFromProduct'])->name('advertisements.copy-from-product');
    Route::get('/advertisements/product/{product}/media', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'getProductMedia'])->name('advertisements.product-media');
    Route::get('/advertisements/product-statuses', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'getProductStatuses'])->name('advertisements.product-statuses');
    Route::delete('/advertisements/{advertisement}/media', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'deleteMedia'])->name('advertisements.delete-media');
    Route::post('/advertisements/{advertisement}/media', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'uploadMedia'])
        ->middleware('large.file.upload')
        ->name('advertisements.upload-media');
    Route::delete('/advertisements/{advertisement}/media/{mediaId}', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'deleteMediaById'])->name('advertisements.delete-media-by-id');
    Route::patch('/advertisements/{advertisement}/main-image', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'updateMainImage'])->name('advertisements.update-main-image');
    Route::patch('/advertisements/{advertisement}/comment', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'updateComment'])->name('advertisements.update-comment');
    Route::patch('/advertisements/{advertisement}/payment-info', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'updatePaymentInfo'])->name('advertisements.update-payment-info');
    Route::patch('/advertisements/{advertisement}/check-status', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'updateCheckStatus'])->name('advertisements.update-check-status');
    Route::patch('/advertisements/{advertisement}/loading-status', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'updateLoadingStatus'])->name('advertisements.update-loading-status');
    Route::patch('/advertisements/{advertisement}/removal-status', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'updateRemovalStatus'])->name('advertisements.update-removal-status');
    Route::patch('/advertisements/{advertisement}/sale-info', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'updateSaleInfo'])->name('advertisements.update-sale-info');
    Route::patch('/advertisements/{advertisement}/characteristics', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'updateCharacteristics'])->name('advertisements.update-characteristics');
    Route::patch('/advertisements/{advertisement}/title', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'updateTitle'])->name('advertisements.update-title');
    Route::patch('/advertisements/{advertisement}/status', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'updateStatus'])->name('advertisements.update-status');
    Route::patch('/advertisements/{advertisement}/activate', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'activate'])->name('advertisements.activate');

    // Маршруты для логов и действий объявлений
    Route::get('/advertisements/{advertisement}/logs', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'getLogs'])->name('advertisements.logs');
    Route::get('/advertisements/{advertisement}/actions', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'getActions'])->name('advertisements.actions');
    Route::post('/advertisements/{advertisement}/actions', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'storeAction'])->name('advertisements.store-action');
    Route::post('/advertisements/{advertisement}/actions/{actionId}/complete', [\App\Http\Controllers\Advertisement\AdvertisementController::class, 'completeAction'])->name('advertisements.complete-action');

    // Маршруты для событий
    Route::get('/events', [\App\Http\Controllers\Event\EventController::class, 'index'])->name('events.index');
    Route::get('/events/active', [\App\Http\Controllers\Event\EventController::class, 'active'])->name('events.active');
    Route::get('/events/expired', [\App\Http\Controllers\Event\EventController::class, 'expired'])->name('events.expired');
    Route::get('/events/logs', [\App\Http\Controllers\Event\EventController::class, 'logs'])->name('events.logs');

    // Тестовый маршрут для проверки транслитерации (удалить после тестирования)
    Route::get('/test-transliterate/{string}', function($string) {
        $controller = new \App\Http\Controllers\Product\ProductController();
        $result = $controller->testTransliterate($string);
        return response()->json([
            'original' => $string,
            'transliterated' => $result
        ]);
    });

    // Тестовая страница для проверки транслитерации (удалить после тестирования)
    Route::get('/test-transliterate-page', function() {
        return view('test-transliterate');
    });
});
// Роуты авторизации
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Главная страница
Route::get('/', function () {
    return redirect('/events');
});

// Защищенные роуты для справочников (только для администраторов)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/guide', [GuidesMain::class, 'index']);
    Route::get('/guide/users', [GuidesUsers::class, 'index'])->name('users.index');
    Route::get('/guide/regions', [GuidesRegions::class, 'index'])->name('regions.index');
    Route::post('/guide/regions', [GuidesRegions::class, 'store'])->name('regions.store');
    Route::get('/guide/regions/{region}/edit', [GuidesRegions::class, 'edit'])->name('regions.edit');
    Route::put('/guide/regions/{region}', [GuidesRegions::class, 'update'])->name('regions.update');

    Route::get('/guide/sources', [GuidesSources::class, 'index']);
    Route::post('/guide/sources', [GuidesSources::class, 'store'])->name('sources.store');
    Route::get('/guide/sources/{source}/edit', [GuidesSources::class, 'edit'])->name('sources.edit');
    Route::put('/guide/sources/{source}', [GuidesSources::class, 'update'])->name('sources.update');
    Route::delete('/guide/sources/{source}', [GuidesSources::class, 'destroy'])->name('sources.destroy');

    Route::get('/guide/categories', [GuidesCategories::class, 'index']);
    Route::post('/guide/categories', [GuidesCategories::class, 'store'])->name('categories.store');
    Route::get('/guide/categories/{category}/edit', [GuidesCategories::class, 'edit'])->name('categories.edit');
    Route::put('/guide/categories/{category}', [GuidesCategories::class, 'update'])->name('categories.update');
    Route::delete('/guide/categories/{category}', [GuidesCategories::class, 'destroy'])->name('categories.destroy');

    Route::get('/guide/warehouses', [GuidesWarehouses::class, 'index'])->name('warehouses.index');
    Route::post('/guide/warehouses', [GuidesWarehouses::class, 'store'])->name('warehouses.store');
    Route::get('/guide/warehouses/{warehouse}/edit', [GuidesWarehouses::class, 'edit'])->name('warehouses.edit');
    Route::put('/guide/warehouses/{warehouse}', [GuidesWarehouses::class, 'update'])->name('warehouses.update');
    Route::delete('/guide/warehouses/{warehouse}', [GuidesWarehouses::class, 'destroy'])->name('warehouses.destroy');

    Route::get('/guide/import-export', [GuidesImportExport::class, 'index'])->name('import-export.index');
    Route::post('/guide/import-export/companies/import', [GuidesImportExport::class, 'importCompanies'])->name('import-export.companies.import');
    Route::post('/guide/import-export/companies/export', [GuidesImportExport::class, 'exportCompanies'])->name('import-export.companies.export');
    Route::post('/guide/import-export/products/export', [GuidesImportExport::class, 'exportProducts'])->name('import-export.products.export');

    Route::post('/guide/users', [GuidesUsers::class, 'store'])->name('users.store');
    Route::get('/guide/users/{user}/edit', [GuidesUsers::class, 'edit'])->name('users.edit');
    Route::put('/guide/users/{user}', [GuidesUsers::class, 'update'])->name('users.update');
    Route::delete('/guide/users/{user}', [GuidesUsers::class, 'destroy'])->name('users.destroy');
});

Route::get('/phpinfo', function () {
    phpinfo();
    return; // предотвращает вывод шаблона Laravel
});

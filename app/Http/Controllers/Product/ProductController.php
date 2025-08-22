<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategories;
use App\Models\ProductMedia;
use App\Models\ProductStatus;
use App\Models\Warehouses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ProductCheckStatuses;
use App\Models\ProductInstallStatuses;
use App\Models\ProductPriceType;
use App\Models\ProductCheck;
use App\Models\ProductLoading;
use App\Models\ProductRemoval;
use App\Models\ProductPaymentVariants;
use App\Models\User;
use App\Models\ProductLog;
use App\Models\ProductAction;
use App\Models\LogType;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with([
            'category', 
            'company.addresses', 
            'status', 
            'warehouse', 
            'regional',
            'mediaOrdered',
            'check.checkStatus',
            'loading.installStatus',
            'removal.installStatus',
            'paymentVariants.priceType',
            'activeAdvertisement',
            'actions' => function($query) {
                $query->where('status', false)
                      ->latest('expired_at')
                      ->limit(1);
            }
        ])->get();

        return view('Product.ProductPage', compact('products'));
    }

    public function create()
    {
        // Получаем только компании, принадлежащие авторизованному пользователю
        $currentUserId = auth()->id() ?? 1; // Временно используем id=1, пока нет аутентификации
        
        // Получаем ID статусов "Холд" и "Отказ"
        $holdStatusId = \App\Models\CompanyStatus::where('name', 'Холд')->value('id');
        $refuseStatusId = \App\Models\CompanyStatus::where('name', 'Отказ')->value('id');
        
        $companies = Company::with(['status', 'addresses', 'warehouses.regions'])
            ->where('owner_user_id', $currentUserId)
            ->whereNotIn('company_status_id', [$holdStatusId, $refuseStatusId])
            ->get();
            
        $categories = ProductCategories::all();
        $statuses = ProductStatus::where('active', true)->get();
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();
        $priceTypes = ProductPriceType::all();

        return view('Product.ProductCreatePage', compact(
            'companies', 
            'categories', 
            'statuses',
            'checkStatuses',
            'installStatuses',
            'priceTypes'
        ));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'product_address' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'main_chars' => 'nullable|string',
            'mark' => 'nullable|string',
            'complectation' => 'nullable|string',
            'check_status_id' => 'nullable|exists:product_check_statuses,id',
            'check_comment' => 'nullable|string',
            'loading_status_id' => 'nullable|exists:product_install_statuses,id',
            'loading_comment' => 'nullable|string',
            'removal_status_id' => 'nullable|exists:product_install_statuses,id',
            'removal_comment' => 'nullable|string',
            'payment_types' => 'nullable|array',
            'payment_types.*' => 'exists:product_price_types,id',
            'main_payment_method' => 'nullable|exists:product_price_types,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'payment_comment' => 'nullable|string',
            'common_commentary_after' => 'nullable|string',
            'media_files.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,mov,avi|max:1024000', // 1000MB max per file
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Дополнительная проверка общего размера файлов
        if ($request->hasFile('media_files')) {
            $files = $request->file('media_files');
            $totalSize = 0;
            
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $totalSize += $file->getSize();
                }
            }
            
            // Максимальный общий размер файлов: 1000MB
            $maxTotalSize = 1000 * 1024 * 1024; // 1000MB в байтах
            
            if ($totalSize > $maxTotalSize) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Общий размер файлов превышает лимит в 1000MB. Пожалуйста, загрузите файлы по частям.',
                        'total_size' => $totalSize,
                        'max_size' => $maxTotalSize
                    ], 413);
                }
                
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['media_files' => 'Общий размер файлов превышает лимит в 1000MB. Пожалуйста, загрузите файлы по частям.']);
            }
        }

        $validated = $validator->validated();

        // Проверяем, что выбранная компания принадлежит авторизованному пользователю
        $currentUserId = auth()->id() ?? 1;
        $company = Company::with(['warehouses.regions', 'addresses', 'status'])
            ->where('id', $validated['company_id'])
            ->where('owner_user_id', $currentUserId)
            ->first();

        if (!$company) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Выбранная компания недоступна для вашего пользователя'
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['company_id' => 'Выбранная компания недоступна для вашего пользователя']);
        }

        // Проверяем, что компания не находится в статусе "Холд" или "Отказ"
        if (in_array($company->status->name, ['Холд', 'Отказ'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нельзя создавать товары для компаний со статусом "' . $company->status->name . '"'
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['company_id' => 'Нельзя создавать товары для компаний со статусом "' . $company->status->name . '"']);
        }

        // Получаем склад компании (первый связанный склад)
        $warehouse = $company->warehouses->first();
        if (!$warehouse) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'У выбранной компании нет связанного склада'
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['company_id' => 'У выбранной компании нет связанного склада']);
        }

        // Получаем регион склада
        $region = $warehouse->regions->first();
        if (!$region) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'У склада компании нет связанного региона'
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['company_id' => 'У склада компании нет связанного региона']);
        }

        // Получаем регионального представителя для данного региона
        $regionalUser = User::where('role_id', 3)
            ->where('active', true)
            ->whereHas('regions', function($query) use ($region) {
                $query->where('regions.id', $region->id);
            })
            ->first();

        $regionalUserId = $regionalUser ? $regionalUser->id : 1;

        // Генерируем SKU для товара, если не указан
        $sku = $validated['sku'] ?: $this->generateSku($validated['company_id']);

        // Создаем товар со статусом "В работе" (id = 1)
        $product = Product::create([
            'name' => $validated['name'],
            'sku' => $sku,
            'warehouse_id' => $warehouse->id,
            'company_id' => $validated['company_id'],
            'category_id' => $validated['category_id'],
            'owner_id' => $company->owner_user_id, // Владелец товара - владелец компании
            'regional_id' => $regionalUserId, // Региональный представитель из выбранной компании
            'status_id' => 1, // Статус "В работе"
            'product_address' => $validated['product_address'] ?? '',
            'main_chars' => $validated['main_chars'],
            'mark' => $validated['mark'],
            'complectation' => $validated['complectation'],
            'main_payment_method' => $validated['main_payment_method'], // Основной способ оплаты
            'purchase_price' => $validated['purchase_price'],
            'payment_comment' => $validated['payment_comment'],
            'common_commentary_after' => $validated['common_commentary_after'],
            'add_expenses' => 0, // Временно 0
        ]);

        // Создаем запись о проверке
        if (isset($validated['check_status_id']) && $validated['check_status_id']) {
            ProductCheck::create([
                'product_id' => $product->id,
                'check_status_id' => $validated['check_status_id'],
                'comment' => $validated['check_comment'] ?? '',
            ]);
        }

        // Создаем запись о погрузке
        if (isset($validated['loading_status_id']) && $validated['loading_status_id']) {
            ProductLoading::create([
                'product_id' => $product->id,
                'install_status_id' => $validated['loading_status_id'],
                'comment' => $validated['loading_comment'] ?? '',
            ]);
        }

        // Создаем запись о демонтаже
        if (isset($validated['removal_status_id']) && $validated['removal_status_id']) {
            ProductRemoval::create([
                'product_id' => $product->id,
                'install_status_id' => $validated['removal_status_id'],
                'comment' => $validated['removal_comment'] ?? '',
            ]);
        }

        // Создаем записи о вариантах оплаты
        if (isset($validated['payment_types']) && is_array($validated['payment_types'])) {
            foreach ($validated['payment_types'] as $priceTypeId) {
                ProductPaymentVariants::create([
                    'product_id' => $product->id,
                    'price_type' => $priceTypeId,
                ]);
            }
        }

        // Обработка загрузки медиафайлов
        if ($request->hasFile('media_files')) {
            $this->handleMediaFiles($request->file('media_files'), $product);
        }

        // Создаем лог о создании товара
        $this->logProductCreation($product);

        // Создаем задачу для владельца компании
        $this->createProductAction($product);

        // Проверяем, является ли запрос AJAX
        \Log::info('Product store request', [
            'is_ajax' => $request->ajax(),
            'headers' => $request->headers->all(),
            'product_id' => $product->id
        ]);
        
        if ($request->ajax()) {
            try {
                $redirectUrl = route('products.show', $product);
                \Log::info('AJAX response', ['redirect' => $redirectUrl]);
                return response()->json([
                    'success' => true,
                    'message' => 'Товар успешно создан!',
                    'redirect' => $redirectUrl
                ]);
            } catch (\Exception $e) {
                \Log::error('Error generating redirect URL', [
                    'error' => $e->getMessage(),
                    'product_id' => $product->id
                ]);
                // Используем прямой URL как fallback
                $redirectUrl = '/product/' . $product->id;
                \Log::info('Using fallback URL', ['redirect' => $redirectUrl]);
                return response()->json([
                    'success' => true,
                    'message' => 'Товар успешно создан!',
                    'redirect' => $redirectUrl
                ]);
            }
        }

        return redirect()->route('products.show', $product)->with('success', 'Товар успешно создан!');
    }

    private function generateSku($companyId)
    {
        $company = Company::find($companyId);
        $supplierSku = $company->sku ?? '000'; // Получаем артикул поставщика из компании
        $date = now()->format('dmY'); // День и месяц
        $year = now()->format('Y'); // Год
        $time = now()->format('Hi'); // Час и минуты

        return $supplierSku . '-' . $date.$year . '-' . $time;
    }

    private function handleMediaFiles($files, Product $product)
    {
        $sortOrder = 0;

        foreach ($files as $file) {
            if ($file->isValid()) {
                // Определяем тип файла
                $mimeType = $file->getMimeType();
                $fileType = $this->getFileType($mimeType);

                // Генерируем уникальное имя файла
                $fileName = $this->generateUniqueFileName($file);

                // Сохраняем файл в папку products
                $filePath = $file->storeAs('products', $fileName, 'public');

                // Создаем запись в базе данных
                ProductMedia::create([
                    'product_id' => $product->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                    'mime_type' => $mimeType,
                    'file_size' => $file->getSize(),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }

    private function getFileType($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        
        return 'unknown';
    }

    private function generateUniqueFileName($file)
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $randomString = Str::random(8);
        
        return $timestamp . '_' . $randomString . '.' . $extension;
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'company.addresses',
            'owner',
            'regional',
            'status',
            'warehouse',
            'mediaOrdered',
            'check.checkStatus',
            'loading.installStatus',
            'removal.installStatus',
            'paymentVariants.priceType',
            'mainPaymentMethod',
            'activeAdvertisement'
        ]);

        // Получаем последний лог товара
        $lastLog = ProductLog::where('product_id', $product->id)
            ->with(['type', 'user'])
            ->latest()
            ->first();

        // Получаем последнее невыполненное действие товара
        $lastAction = ProductAction::where('product_id', $product->id)
            ->where('status', false)
            ->latest('expired_at')
            ->first();

        $statuses = ProductStatus::all();
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();

        return view('Product.ProductItemPage', compact('product', 'statuses', 'checkStatuses', 'installStatuses', 'lastLog', 'lastAction'));
    }

    public function edit(Product $product)
    {
        $product->load(['mediaOrdered']);
        $companies = Company::with('status')->get();
        $categories = ProductCategories::all();
        $statuses = ProductStatus::where('active', true)->get();

        return view('Product.ProductEditPage', compact('product', 'companies', 'categories', 'statuses'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'status_id' => 'required|exists:product_statuses,id',
            'main_chars' => 'nullable|string',
            'mark' => 'nullable|string',
            'complectation' => 'nullable|string',
            'status_comment' => 'nullable|string',
            'loading_type' => 'nullable|string',
            'loading_comment' => 'nullable|string',
            'removal_type' => 'nullable|string',
            'removal_comment' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'payment_comment' => 'nullable|string',
            'common_commentary_after' => 'nullable|string',
            'media_files.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,mov,avi|max:102400', // 100MB max per file
            'delete_media' => 'nullable|array',
            'delete_media.*' => 'exists:products_media,id'
        ]);

        // Получаем склад компании
        $company = Company::with('warehouses')->find($request->company_id);
        $warehouse = $company ? $company->warehouses->first() : null;

        // Сохраняем старое значение комментария для сравнения
        $oldCommentary = $product->common_commentary_after;

        // Обновляем товар
        $product->update([
            'name' => $request->name,
            'warehouse_id' => $warehouse ? $warehouse->id : $product->warehouse_id,
            'company_id' => $request->company_id,
            'category_id' => $request->category_id,
            'status_id' => $request->status_id,
            'main_chars' => $request->main_chars,
            'mark' => $request->mark,
            'complectation' => $request->complectation,
            'status_comment' => $request->status_comment,
            'loading_type' => $request->loading_type,
            'loading_comment' => $request->loading_comment,
            'removal_type' => $request->removal_type,
            'removal_comment' => $request->removal_comment,
            'payment_method' => $request->payment_method,
            'purchase_price' => $request->purchase_price,
            'payment_comment' => $request->payment_comment,
            'common_commentary_after' => $request->common_commentary_after,
        ]);

        // Создаем системный лог при изменении общего комментария после осмотра
        if ($oldCommentary !== $request->common_commentary_after) {
            $systemLogType = LogType::where('name', 'Системный')->first();
            if ($systemLogType) {
                $oldText = $oldCommentary ?: 'пустой комментарий';
                $newText = $request->common_commentary_after ?: 'пустой комментарий';
                
                $logMessage = "Изменен Общий комментарий после осмотра, с \"{$oldText}\" на \"{$newText}\"";
                
                ProductLog::create([
                    'product_id' => $product->id,
                    'type_id' => $systemLogType->id,
                    'log' => $logMessage,
                    'user_id' => null // От имени системы
                ]);
            }
        }

        // Удаляем выбранные медиафайлы
        if ($request->has('delete_media') && is_array($request->delete_media)) {
            $mediaToDelete = ProductMedia::where('product_id', $product->id)
                ->whereIn('id', $request->delete_media)
                ->get();

            foreach ($mediaToDelete as $media) {
                // Удаляем файл с диска
                if (Storage::disk('public')->exists($media->file_path)) {
                    Storage::disk('public')->delete($media->file_path);
                }
                // Удаляем запись из базы
                $media->delete();
            }
        }

        // Обработка новых медиафайлов
        if ($request->hasFile('media_files')) {
            $this->handleMediaFiles($request->file('media_files'), $product);
        }

        return redirect()->route('products.show', $product)->with('success', 'Товар успешно обновлен!');
    }

    public function updateComment(Request $request, Product $product)
    {
        $request->validate([
            'field' => 'required|in:loading_comment,removal_comment,check_comment,purchase_price,payment_comment,common_commentary_after',
            'value' => 'nullable|string|max:1000'
        ]);

        $field = $request->field;
        $value = $request->value;

        // Специальная обработка для числовых полей
        if ($field === 'purchase_price') {
            $value = $value ? (float) $value : null;
            $product->update([$field => $value]);
        } elseif ($field === 'loading_comment') {
            // Обновляем комментарий в таблице product_loadings
            $loading = $product->loading->first();
            if ($loading) {
                $loading->update(['comment' => $value]);
            } else {
                // Создаем новую запись, если её нет
                ProductLoading::create([
                    'product_id' => $product->id,
                    'install_status_id' => 1, // Статус по умолчанию
                    'comment' => $value
                ]);
            }
        } elseif ($field === 'removal_comment') {
            // Обновляем комментарий в таблице product_removals
            $removal = $product->removal->first();
            if ($removal) {
                $removal->update(['comment' => $value]);
            } else {
                // Создаем новую запись, если её нет
                ProductRemoval::create([
                    'product_id' => $product->id,
                    'install_status_id' => 1, // Статус по умолчанию
                    'comment' => $value
                ]);
            }
        } elseif ($field === 'check_comment') {
            // Обновляем комментарий в таблице product_checks
            $check = $product->check->first();
            if ($check) {
                $check->update(['comment' => $value]);
            } else {
                // Создаем новую запись, если её нет
                ProductCheck::create([
                    'product_id' => $product->id,
                    'check_status_id' => 1, // Статус по умолчанию
                    'comment' => $value
                ]);
            }
        } elseif ($field === 'payment_comment') {
            $product->update([$field => $value]);
        } elseif ($field === 'common_commentary_after') {
            // Сохраняем старое значение для логирования
            $oldCommentary = $product->common_commentary_after;
            
            $product->update([$field => $value]);
            
            // Создаем системный лог при изменении общего комментария после осмотра
            if ($oldCommentary !== $value) {
                $systemLogType = LogType::where('name', 'Системный')->first();
                if ($systemLogType) {
                    $oldText = $oldCommentary ?: 'пустой комментарий';
                    $newText = $value ?: 'пустой комментарий';
                    
                    $logMessage = "Изменен Общий комментарий после осмотра, с \"{$oldText}\" на \"{$newText}\"";
                    
                    ProductLog::create([
                        'product_id' => $product->id,
                        'type_id' => $systemLogType->id,
                        'log' => $logMessage,
                        'user_id' => null // От имени системы
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Данные успешно обновлены',
            'value' => $value
        ]);
    }

    public function updateLoadingStatus(Request $request, Product $product)
    {
        $request->validate([
            'status_id' => 'nullable|exists:product_install_statuses,id',
            'comment' => 'nullable|string|max:1000'
        ]);

        $loading = $product->loading->first();
        
        // Сохраняем старые значения для логирования
        $oldStatusId = $loading ? $loading->install_status_id : null;
        $oldComment = $loading ? $loading->comment : null;
        
        // Получаем названия статусов
        $oldStatusName = $oldStatusId ? ProductInstallStatuses::find($oldStatusId)->name : null;
        $newStatusName = $request->status_id ? ProductInstallStatuses::find($request->status_id)->name : null;
        
        // Нормализуем значения для сравнения
        $oldCommentNormalized = $oldComment ? trim($oldComment) : '';
        $newCommentNormalized = $request->comment ? trim($request->comment) : '';
        
        if ($loading) {
            $loading->update([
                'install_status_id' => $request->status_id,
                'comment' => $request->comment
            ]);
        } else {
            ProductLoading::create([
                'product_id' => $product->id,
                'install_status_id' => $request->status_id ?? 1,
                'comment' => $request->comment ?? ''
            ]);
        }

        // Создаем запись в логе от имени системы
        $this->logLoadingChanges($product, $oldStatusName, $newStatusName, $oldCommentNormalized, $newCommentNormalized);

        return response()->json([
            'success' => true,
            'message' => 'Статус погрузки обновлен'
        ]);
    }

    public function updateRemovalStatus(Request $request, Product $product)
    {
        $request->validate([
            'status_id' => 'nullable|exists:product_install_statuses,id',
            'comment' => 'nullable|string|max:1000'
        ]);

        $removal = $product->removal->first();
        
        // Сохраняем старые значения для логирования
        $oldStatusId = $removal ? $removal->install_status_id : null;
        $oldComment = $removal ? $removal->comment : null;
        
        // Получаем названия статусов
        $oldStatusName = $oldStatusId ? ProductInstallStatuses::find($oldStatusId)->name : null;
        $newStatusName = $request->status_id ? ProductInstallStatuses::find($request->status_id)->name : null;
        
        // Нормализуем значения для сравнения
        $oldCommentNormalized = $oldComment ? trim($oldComment) : '';
        $newCommentNormalized = $request->comment ? trim($request->comment) : '';
        
        if ($removal) {
            $removal->update([
                'install_status_id' => $request->status_id,
                'comment' => $request->comment
            ]);
        } else {
            ProductRemoval::create([
                'product_id' => $product->id,
                'install_status_id' => $request->status_id ?? 1,
                'comment' => $request->comment ?? ''
            ]);
        }

        // Создаем запись в логе от имени системы
        $this->logRemovalChanges($product, $oldStatusName, $newStatusName, $oldCommentNormalized, $newCommentNormalized);

        return response()->json([
            'success' => true,
            'message' => 'Статус демонтажа обновлен'
        ]);
    }

    public function updateCheckStatus(Request $request, Product $product)
    {
        $request->validate([
            'status_id' => 'nullable|exists:product_check_statuses,id',
            'comment' => 'nullable|string|max:1000'
        ]);

        $check = $product->check->first();
        
        // Сохраняем старые значения для логирования
        $oldStatusId = $check ? $check->check_status_id : null;
        $oldComment = $check ? $check->comment : null;
        
        // Получаем названия статусов
        $oldStatusName = $oldStatusId ? ProductCheckStatuses::find($oldStatusId)->name : null;
        $newStatusName = $request->status_id ? ProductCheckStatuses::find($request->status_id)->name : null;
        
        // Нормализуем значения для сравнения
        $oldCommentNormalized = $oldComment ? trim($oldComment) : '';
        $newCommentNormalized = $request->comment ? trim($request->comment) : '';
        
        if ($check) {
            $check->update([
                'check_status_id' => $request->status_id,
                'comment' => $request->comment
            ]);
        } else {
            ProductCheck::create([
                'product_id' => $product->id,
                'check_status_id' => $request->status_id ?? 1,
                'comment' => $request->comment ?? ''
            ]);
        }

        // Создаем запись в логе от имени системы
        $this->logCheckChanges($product, $oldStatusName, $newStatusName, $oldCommentNormalized, $newCommentNormalized);

        return response()->json([
            'success' => true,
            'message' => 'Статус проверки обновлен'
        ]);
    }

    public function updatePaymentVariants(Request $request, Product $product)
    {
        $request->validate([
            'payment_types' => 'nullable|array',
            'payment_types.*' => 'exists:product_price_types,id',
            'main_payment_method' => 'nullable|exists:product_price_types,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'payment_comment' => 'nullable|string|max:1000'
        ]);

        // Сохраняем старые значения для логирования
        $oldMainPaymentMethod = $product->main_payment_method;
        $oldPurchasePrice = $product->purchase_price;
        $oldPaymentComment = $product->payment_comment;
        $oldPaymentVariants = $product->paymentVariants->pluck('price_type')->toArray();
        
        // Получаем названия типов оплаты
        $oldMainPaymentName = $oldMainPaymentMethod ? ProductPriceType::find($oldMainPaymentMethod)->name : null;
        $newMainPaymentName = $request->main_payment_method ? ProductPriceType::find($request->main_payment_method)->name : null;
        
        // Нормализуем значения для сравнения
        $oldPaymentCommentNormalized = $oldPaymentComment ? trim($oldPaymentComment) : '';
        $newPaymentCommentNormalized = $request->payment_comment ? trim($request->payment_comment) : '';

        // Обновляем основные поля оплаты
        $product->update([
            'main_payment_method' => $request->main_payment_method,
            'purchase_price' => $request->purchase_price,
            'payment_comment' => $request->payment_comment
        ]);

        // Обновляем варианты оплаты
        if ($request->has('payment_types')) {
            // Удаляем старые варианты
            $product->paymentVariants()->delete();
            
            // Создаем новые варианты
            if (is_array($request->payment_types)) {
                foreach ($request->payment_types as $priceTypeId) {
                    ProductPaymentVariants::create([
                        'product_id' => $product->id,
                        'price_type' => $priceTypeId
                    ]);
                }
            }
        }

        // Создаем запись в логе от имени системы
        $this->logPaymentChanges($product, $oldMainPaymentName, $newMainPaymentName, $oldPurchasePrice, $request->purchase_price, $oldPaymentCommentNormalized, $newPaymentCommentNormalized, $oldPaymentVariants, $request->payment_types ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Информация об оплате обновлена'
        ]);
    }

    public function updateStatus(Request $request, Product $product)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:product_statuses,id',
            'comment' => 'required|string|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Сохраняем старый статус
            $oldStatus = $product->status;

            // Обновляем статус товара
            $product->update([
                'status_id' => $validated['status_id']
            ]);

            $product->load('status');

            // Получаем новый статус
            $newStatus = $product->status;

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $logText = "Смена статуса товара с '{$oldStatus->name}' на '{$newStatus->name}'. Комментарий: {$validated['comment']}";

                $log = ProductLog::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'log' => $logText,
                    'type_id' => $commentLogType->id
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'status' => $product->status,
                    'message' => 'Статус товара успешно обновлен',
                    'log' => $log->load(['type', 'user'])
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'status' => $product->status,
                'message' => 'Статус товара успешно обновлен'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении статуса: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateCharacteristics(Request $request, Product $product)
    {
        $request->validate([
            'main_chars' => 'nullable|string|max:1000',
            'complectation' => 'nullable|string|max:1000',
            'mark' => 'nullable|string|max:1000'
        ]);

        $product->update([
            'main_chars' => $request->main_chars,
            'complectation' => $request->complectation,
            'mark' => $request->mark
        ]);

        // Создаем запись в логах от имени системы
        $systemLogType = LogType::where('name', 'Системный')->first();
        $currentUser = auth()->user();
        $userName = $currentUser ? $currentUser->name : 'Неизвестный пользователь';
        
        $logMessage = "Пользователь {$userName} изменил блок Характеристик товара";
        
        ProductLog::create([
            'product_id' => $product->id,
            'type_id' => $systemLogType ? $systemLogType->id : null,
            'log' => $logMessage,
            'user_id' => null // От имени системы
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Характеристики товара успешно обновлены'
        ]);
    }

    /**
     * Получает все логи товара
     */
    public function getLogs(Product $product)
    {
        $logs = ProductLog::where('product_id', $product->id)
            ->with(['type', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    /**
     * Получает все действия товара
     */
    public function getActions(Product $product)
    {
        $actions = ProductAction::where('product_id', $product->id)
            ->orderBy('expired_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'actions' => $actions
        ]);
    }

    /**
     * Создает новое действие для товара
     */
    public function storeAction(Request $request, Product $product)
    {
        // Валидируем запрос
        $validated = $request->validate([
            'action' => 'required|string|max:1000',
            'expired_at' => 'required|date|after:today'
        ]);

        try {
            // Создаем новое действие
            $action = ProductAction::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'action' => $validated['action'],
                'expired_at' => $validated['expired_at'],
                'status' => false,
            ]);

            // Создаем лог о создании действия
            $commentType = LogType::where('name', 'Комментарий')->first();
            
            $logMessage = "Пользователь: " . auth()->user()->name . ", создал новую задачу: \"{$validated['action']}\" со сроком до {$validated['expired_at']}";
            
            $log = ProductLog::create([
                'product_id' => $product->id,
                'type_id' => $commentType ? $commentType->id : null,
                'log' => $logMessage,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Действие успешно создано',
                'action' => $action,
                'log' => $log->load(['type', 'user'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании действия: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Отмечает действие как выполненное
     */
    public function completeAction(Request $request, Product $product, $actionId)
    {
        // Валидируем запрос
        $validated = $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        try {
            // Находим действие
            $action = ProductAction::where('id', $actionId)
                ->where('product_id', $product->id)
                ->first();

            if (!$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'Действие не найдено'
                ], 404);
            }

            // Проверяем, что действие еще не выполнено
            if ($action->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Действие уже выполнено'
                ], 400);
            }

            // Отмечаем действие как выполненное
            $action->status = true;
            $action->completed_at = now();
            $action->save();

            // Создаем лог о выполнении действия
            $commentType = LogType::where('name', 'Комментарий')->first();
            
            $logMessage = "Пользователь: " . auth()->user()->name . ", выполнил задачу \"{$action->action}\" с комментарием: {$validated['comment']}";
            
            $log = ProductLog::create([
                'product_id' => $product->id,
                'type_id' => $commentType ? $commentType->id : null,
                'log' => $logMessage,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Действие успешно выполнено',
                'log' => $log->load(['type', 'user'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при выполнении действия: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Логирует изменения в блоке "Информация о проверке"
     */
    private function logCheckChanges(Product $product, $oldStatusName, $newStatusName, $oldComment, $newComment)
    {
        $changes = [];
        
        // Проверяем изменение статуса
        if ($oldStatusName !== $newStatusName) {
            $oldStatusText = $oldStatusName ? "'{$oldStatusName}'" : "'Не указан'";
            $newStatusText = $newStatusName ? "'{$newStatusName}'" : "'Не указан'";
            $changes[] = "сменил статус с {$oldStatusText} на {$newStatusText}";
        }
        
        // Проверяем изменение комментария
        if ($oldComment !== $newComment) {
            $oldCommentText = $oldComment ? "'{$oldComment}'" : "'Не указан'";
            $newCommentText = $newComment ? "'{$newComment}'" : "'Не указан'";
            $changes[] = "изменил комментарий с {$oldCommentText} на {$newCommentText}";
        }
        
        // Если есть изменения, создаем запись в логе
        if (!empty($changes)) {
            $userName = auth()->user()->name ?? 'Неизвестный пользователь';
            $logMessage = "Пользователь {$userName} изменил блок Информация о проверки, " . implode(', ', $changes);
            
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            ProductLog::create([
                'product_id' => $product->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }

    /**
     * Логирует изменения в блоке "Информация о погрузке"
     */
    private function logLoadingChanges(Product $product, $oldStatusName, $newStatusName, $oldComment, $newComment)
    {
        $changes = [];
        
        // Проверяем изменение статуса
        if ($oldStatusName !== $newStatusName) {
            $oldStatusText = $oldStatusName ? "'{$oldStatusName}'" : "'Не указан'";
            $newStatusText = $newStatusName ? "'{$newStatusName}'" : "'Не указан'";
            $changes[] = "сменил статус с {$oldStatusText} на {$newStatusText}";
        }
        
        // Проверяем изменение комментария
        if ($oldComment !== $newComment) {
            $oldCommentText = $oldComment ? "'{$oldComment}'" : "'Не указан'";
            $newCommentText = $newComment ? "'{$newComment}'" : "'Не указан'";
            $changes[] = "изменил комментарий с {$oldCommentText} на {$newCommentText}";
        }
        
        // Если есть изменения, создаем запись в логе
        if (!empty($changes)) {
            $userName = auth()->user()->name ?? 'Неизвестный пользователь';
            $logMessage = "Пользователь {$userName} изменил блок Информация о погрузке, " . implode(', ', $changes);
            
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            ProductLog::create([
                'product_id' => $product->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }

    /**
     * Логирует изменения в блоке "Информация о демонтаже"
     */
    private function logRemovalChanges(Product $product, $oldStatusName, $newStatusName, $oldComment, $newComment)
    {
        $changes = [];
        
        // Проверяем изменение статуса
        if ($oldStatusName !== $newStatusName) {
            $oldStatusText = $oldStatusName ? "'{$oldStatusName}'" : "'Не указан'";
            $newStatusText = $newStatusName ? "'{$newStatusName}'" : "'Не указан'";
            $changes[] = "сменил статус с {$oldStatusText} на {$newStatusText}";
        }
        
        // Проверяем изменение комментария
        if ($oldComment !== $newComment) {
            $oldCommentText = $oldComment ? "'{$oldComment}'" : "'Не указан'";
            $newCommentText = $newComment ? "'{$newComment}'" : "'Не указан'";
            $changes[] = "изменил комментарий с {$oldCommentText} на {$newCommentText}";
        }
        
        // Если есть изменения, создаем запись в логе
        if (!empty($changes)) {
            $userName = auth()->user()->name ?? 'Неизвестный пользователь';
            $logMessage = "Пользователь {$userName} изменил блок Информация о демонтаже, " . implode(', ', $changes);
            
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            ProductLog::create([
                'product_id' => $product->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }

    /**
     * Логирует изменения в блоке "Информация о покупке"
     */
    private function logPaymentChanges(Product $product, $oldMainPaymentName, $newMainPaymentName, $oldPurchasePrice, $newPurchasePrice, $oldComment, $newComment, $oldVariants, $newVariants)
    {
        $changes = [];
        
        // Проверяем изменение основного способа оплаты
        if ($oldMainPaymentName !== $newMainPaymentName) {
            $oldPaymentText = $oldMainPaymentName ? "'{$oldMainPaymentName}'" : "'Не указан'";
            $newPaymentText = $newMainPaymentName ? "'{$newMainPaymentName}'" : "'Не указан'";
            $changes[] = "сменил основной способ оплаты с {$oldPaymentText} на {$newPaymentText}";
        }
        
        // Проверяем изменение закупочной цены
        if ($oldPurchasePrice != $newPurchasePrice) {
            $oldPriceText = $oldPurchasePrice ? number_format($oldPurchasePrice, 0, ',', ' ') . ' ₽' : "'Не указана'";
            $newPriceText = $newPurchasePrice ? number_format($newPurchasePrice, 0, ',', ' ') . ' ₽' : "'Не указана'";
            $changes[] = "изменил закупочную цену с {$oldPriceText} на {$newPriceText}";
        }
        
        // Проверяем изменение комментария
        if ($oldComment !== $newComment) {
            $oldCommentText = $oldComment ? "'{$oldComment}'" : "'Не указан'";
            $newCommentText = $newComment ? "'{$newComment}'" : "'Не указан'";
            $changes[] = "изменил комментарий с {$oldCommentText} на {$newCommentText}";
        }
        
        // Проверяем изменение вариантов оплаты
        $oldVariantsSorted = sort($oldVariants);
        $newVariantsSorted = sort($newVariants);
        if ($oldVariantsSorted != $newVariantsSorted) {
            $oldVariantsNames = [];
            $newVariantsNames = [];
            
            foreach ($oldVariants as $variantId) {
                $variant = ProductPriceType::find($variantId);
                if ($variant) {
                    $oldVariantsNames[] = $variant->name;
                }
            }
            
            foreach ($newVariants as $variantId) {
                $variant = ProductPriceType::find($variantId);
                if ($variant) {
                    $newVariantsNames[] = $variant->name;
                }
            }
            
            $oldVariantsText = !empty($oldVariantsNames) ? "'" . implode(', ', $oldVariantsNames) . "'" : "'Не указаны'";
            $newVariantsText = !empty($newVariantsNames) ? "'" . implode(', ', $newVariantsNames) . "'" : "'Не указаны'";
            $changes[] = "изменил варианты оплаты с {$oldVariantsText} на {$newVariantsText}";
        }
        
        // Если есть изменения, создаем запись в логе
        if (!empty($changes)) {
            $userName = auth()->user()->name ?? 'Неизвестный пользователь';
            $logMessage = "Пользователь {$userName} изменил блок Информация о покупке, " . implode(', ', $changes);
            
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            ProductLog::create([
                'product_id' => $product->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }

    /**
     * Логирует создание товара
     */
    private function logProductCreation(Product $product)
    {
        $userName = auth()->user()->name ?? 'Неизвестный пользователь';
        $logMessage = "Пользователь {$userName} создал товар {$product->name}";
        
        $systemLogType = LogType::where('name', 'Системный')->first();
        
        ProductLog::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'log' => $logMessage,
            'type_id' => $systemLogType ? $systemLogType->id : null
        ]);
    }

    /**
     * Создает задачу для владельца компании при создании товара
     */
    private function createProductAction(Product $product)
    {
        // Загружаем компанию с владельцем
        $company = Company::with('owner')->find($product->company_id);
        
        if (!$company || !$company->owner) {
            \Log::warning('Не удалось найти владельца компании для товара', [
                'product_id' => $product->id,
                'company_id' => $product->company_id
            ]);
            return;
        }

        // Создаем задачу для владельца компании
        $action = ProductAction::create([
            'product_id' => $product->id,
            'user_id' => $company->owner_user_id, // ID владельца компании
            'action' => 'Актуализировать данные по товару, уточнить цену и способ оплаты, принять решение по дальнейшим действиям.',
            'expired_at' => now()->addDays(7), // Срок выполнения - 7 дней
            'status' => false, // Задача не выполнена
        ]);

        // Создаем лог о создании задачи
        $this->logActionCreation($product, $action, $company->owner);

        \Log::info('Создана задача для владельца компании', [
            'product_id' => $product->id,
            'company_id' => $company->id,
            'owner_user_id' => $company->owner_user_id,
            'action_id' => $action->id
        ]);
    }

    /**
     * Логирует создание задачи для товара
     */
    private function logActionCreation(Product $product, ProductAction $action, User $owner)
    {
        $userName = auth()->user()->name ?? 'Неизвестный пользователь';
        $ownerName = $owner->name ?? 'Неизвестный владелец';
        $logMessage = "Пользователь Система создал задачу для {$ownerName}: {$action->action}";
        
        $systemLogType = LogType::where('name', 'Системный')->first();
        
        ProductLog::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'log' => $logMessage,
            'type_id' => $systemLogType ? $systemLogType->id : null
        ]);
    }
}

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
use App\Models\ProductState;
use App\Models\ProductAvailable;
use App\Models\User;
use App\Models\ProductLog;
use App\Models\ProductAction;
use App\Models\LogType;
use App\Models\AdvertisementStatus;
use App\Models\AdvLog;
use App\Models\AdvAction;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $currentUserId = auth()->id() ?? 1;
        
        $productsQuery = Product::with([
            'category', 
            'company.addresses', 
            'status', 
            'state',
            'available',
            'warehouse.regions', 
            'owner',
            'regional',
            'mediaOrdered',
            'check.checkStatus',
            'loading.installStatus',
            'removal.installStatus',
            'paymentVariants.priceType',
            'advertisements.status',
            'actions' => function($query) {
                $query->where('status', false)
                      ->orderBy('expired_at', 'asc');
            }
        ]);
        
        // Применяем фильтры по правам доступа
        if ($user && $user->role) {
            if ($user->role->name === 'Региональный представитель') {
                // Для регионального представителя показываем только товары, где он назначен как региональный представитель
                $productsQuery->where('regional_id', $currentUserId);
            } elseif ($user->role->name === 'Менеджер') {
                // Для менеджера показываем только товары, где он является владельцем
                $productsQuery->where('owner_id', $currentUserId);
            } elseif ($user->role->can_view_products === 1) {
                // Для пользователей с ограниченным доступом показываем только их товары
                $productsQuery->where('owner_id', $currentUserId);
            } elseif ($user->role->can_view_products === 3) {
                // Для администраторов показываем все товары
                // Ничего не добавляем к запросу
            } else {
                // Для остальных ролей показываем только их товары
                $productsQuery->where('owner_id', $currentUserId);
            }
        } else {
            // Если пользователь не авторизован или нет роли, показываем только его товары
            $productsQuery->where('owner_id', $currentUserId);
        }
        
        // Применяем фильтры из запроса
        if ($request->filled('category_id')) {
            $productsQuery->where('category_id', $request->category_id);
        }
        
        if ($request->filled('company_id')) {
            $productsQuery->where('company_id', $request->company_id);
        }
        
        if ($request->filled('status_id')) {
            $productsQuery->where('status_id', $request->status_id);
        }
        
        if ($request->filled('region_id')) {
            $productsQuery->whereHas('company', function($query) use ($request) {
                $query->where('region_id', $request->region_id);
            });
        }
        
        if ($request->filled('state_id')) {
            $productsQuery->where('state_id', $request->state_id);
        }
        
        if ($request->filled('available_id')) {
            $productsQuery->where('available_id', $request->available_id);
        }
        
        // Фильтр по ответственному (только для администраторов)
        if ($request->filled('responsible_id') && $user && $user->role && $user->role->name === 'Администратор') {
            $productsQuery->where('owner_id', $request->responsible_id);
        }
        
        // Поиск по названию, артикулу и адресу товара
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $productsQuery->where(function($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                      ->orWhere('product_address', 'like', '%' . $searchTerm . '%');
            });
        }
        
        $products = $productsQuery->orderBy('id', 'desc')->paginate(20)->withQueryString();

        // Получаем данные для фильтров с учетом прав доступа
        $filterData = $this->getFilterData($user, $currentUserId);

        return view('Product.ProductPage', compact('products', 'filterData'));
    }

    /**
     * Получает данные для фильтров с учетом прав доступа пользователя
     */
    private function getFilterData($user, $currentUserId)
    {
        $filterData = [];
        
        // Категории - доступны всем
        $filterData['categories'] = \App\Models\ProductCategories::where('active', true)->get();
        
        // Статусы - показываем все статусы товаров
        $filterData['statuses'] = \App\Models\ProductStatus::all();
        
        // Состояния товаров - доступны всем
        $filterData['states'] = \App\Models\ProductState::all();
        
        // Доступность товаров - доступна всем
        $filterData['availables'] = \App\Models\ProductAvailable::all();
        
        // Поставщики (компании) - с учетом прав доступа
        $companiesQuery = \App\Models\Company::with('status');
        
        if ($user && $user->role) {
            if ($user->role->name === 'Региональный представитель') {
                // Для регионального представителя показываем компании, где он назначен как региональный представитель
                $companiesQuery->where('regional_user_id', $currentUserId);
            } elseif ($user->role->name === 'Менеджер') {
                // Для менеджера показываем только его компании
                $companiesQuery->where('owner_user_id', $currentUserId);
            } elseif ($user->role->can_view_companies === 1) {
                // Для пользователей с ограниченным доступом показываем только их компании
                $companiesQuery->where('owner_user_id', $currentUserId);
            } elseif ($user->role->can_view_companies === 3) {
                // Для администраторов показываем все компании
                // Ничего не добавляем к запросу
            } else {
                // Для остальных ролей показываем только их компании
                $companiesQuery->where('owner_user_id', $currentUserId);
            }
        } else {
            // Если пользователь не авторизован или нет роли, показываем только его компании
            $companiesQuery->where('owner_user_id', $currentUserId);
        }
        
        $filterData['companies'] = $companiesQuery->get();
        
        // Регионы - упрощенная логика с учетом прав доступа
        $regionsQuery = \App\Models\Regions::where('active', true);
        
        if ($user && $user->role) {
            if ($user->role->name === 'Региональный представитель') {
                // Для регионального представителя показываем регионы складов, к которым у него есть доступ
                $regionsQuery->whereHas('warehouses.users', function($query) use ($currentUserId) {
                    $query->where('users.id', $currentUserId);
                });
            } elseif ($user->role->name === 'Менеджер') {
                // Для менеджера показываем регионы его товаров
                $regionsQuery->whereHas('warehouses.products', function($query) use ($currentUserId) {
                    $query->where('products.owner_id', $currentUserId);
                });
            } elseif ($user->role->can_view_companies === 1) {
                // Для пользователей с ограниченным доступом показываем регионы их товаров
                $regionsQuery->whereHas('warehouses.products', function($query) use ($currentUserId) {
                    $query->where('products.owner_id', $currentUserId);
                });
            } elseif ($user->role->can_view_companies === 3) {
                // Для администраторов показываем все регионы
                // Ничего не добавляем к запросу
            } else {
                // Для остальных ролей показываем регионы их товаров
                $regionsQuery->whereHas('warehouses.products', function($query) use ($currentUserId) {
                    $query->where('products.owner_id', $currentUserId);
                });
            }
        } else {
            // Если пользователь не авторизован или нет роли, показываем регионы его товаров
            $regionsQuery->whereHas('warehouses.products', function($query) use ($currentUserId) {
                $query->where('products.owner_id', $currentUserId);
            });
        }
        
        $filterData['regions'] = $regionsQuery->get();
        
        // Пользователи (ответственные) - только для администраторов, исключая региональных представителей
        if ($user && $user->role && $user->role->name === 'Администратор') {
            $filterData['users'] = \App\Models\User::with('role')
                ->where('active', true)
                ->whereHas('role', function($query) {
                    $query->where('name', '!=', 'Региональный представитель');
                })
                ->orderBy('name')
                ->get();
        } else {
            $filterData['users'] = collect();
        }
        
        return $filterData;
    }

    public function create()
    {
        $currentUserId = auth()->id() ?? 1; // Временно используем id=1, пока нет аутентификации
        $user = auth()->user();
        
        // Получаем ID статусов "Холд" и "Отказ"
        $holdStatusId = \App\Models\CompanyStatus::where('name', 'Холд')->value('id');
        $refuseStatusId = \App\Models\CompanyStatus::where('name', 'Отказ')->value('id');
        
        // Определяем запрос для получения компаний в зависимости от роли пользователя
        $companiesQuery = Company::with(['status', 'addresses', 'warehouses.regions'])
            ->whereNotIn('company_status_id', [$holdStatusId, $refuseStatusId]);
        
        if ($user && $user->role) {
            if ($user->role->name === 'Региональный представитель') {
                // Для регионального представителя показываем компании, где он назначен как региональный представитель
                $companiesQuery->where('regional_user_id', $currentUserId);
            } elseif ($user->role->name === 'Менеджер') {
                // Для менеджера показываем только его компании
                $companiesQuery->where('owner_user_id', $currentUserId);
            } elseif ($user->role->can_view_companies === 1) {
                // Для пользователей с ограниченным доступом показываем только их компании
                $companiesQuery->where('owner_user_id', $currentUserId);
            } elseif ($user->role->can_view_companies === 3) {
                // Для администраторов показываем все компании
                // Ничего не добавляем к запросу
            } else {
                // Для остальных ролей показываем только их компании
                $companiesQuery->where('owner_user_id', $currentUserId);
            }
        } else {
            // Если пользователь не авторизован или нет роли, показываем только его компании
            $companiesQuery->where('owner_user_id', $currentUserId);
        }
        
        $companies = $companiesQuery->get();
        $categories = ProductCategories::all();
        $statuses = ProductStatus::all();
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();
        $priceTypes = ProductPriceType::all();
        $states = ProductState::all();
        $availables = ProductAvailable::all();

        return view('Product.ProductCreatePage', compact(
            'companies', 
            'categories', 
            'statuses',
            'checkStatuses',
            'installStatuses',
            'priceTypes',
            'states',
            'availables'
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
            'state_id' => 'required|exists:product_states,id',
            'available_id' => 'required|exists:product_availables,id',
            'main_chars' => 'nullable|string',
            'mark' => 'nullable|string',
            'complectation' => 'nullable|string',
            'check_status_id' => 'nullable|exists:product_check_statuses,id',
            'check_comment' => 'nullable|string',
            'loading_status_id' => 'nullable|exists:product_install_statuses,id',
            'loading_comment' => 'nullable|string',
            'removal_status_id' => 'nullable|exists:product_install_statuses,id',
            'removal_comment' => 'nullable|string',
            'payment_types' => 'required|array|min:1',
            'payment_types.*' => 'exists:product_price_types,id',
            'main_payment_method' => 'required|exists:product_price_types,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'payment_comment' => 'required|string|min:1',
            'common_commentary_after' => 'nullable|string',
            'media_files.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,mov,avi|max:1024000', // 1000MB max per file
        ], [
            'payment_types.required' => 'Выберите хотя бы один вариант оплаты',
            'payment_types.min' => 'Выберите хотя бы один вариант оплаты',
            'main_payment_method.required' => 'Выберите основной способ оплаты',
            'main_payment_method.exists' => 'Выбранный основной способ оплаты не существует',
            'payment_comment.required' => 'Комментарий обязателен для заполнения',
            'payment_comment.min' => 'Комментарий не может быть пустым',
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

        // Проверяем, что выбранная компания доступна авторизованному пользователю
        $currentUserId = auth()->id() ?? 1;
        $user = auth()->user();
        
        $companyQuery = Company::with(['warehouses.regions', 'addresses', 'status', 'region'])
            ->where('id', $validated['company_id']);
        
        if ($user && $user->role) {
            if ($user->role->name === 'Региональный представитель') {
                // Для регионального представителя проверяем, что он назначен как региональный представитель
                $companyQuery->where('regional_user_id', $currentUserId);
            } elseif ($user->role->name === 'Менеджер') {
                // Для менеджера проверяем, что он владелец
                $companyQuery->where('owner_user_id', $currentUserId);
            } elseif ($user->role->can_view_companies === 1) {
                // Для пользователей с ограниченным доступом проверяем, что они владельцы
                $companyQuery->where('owner_user_id', $currentUserId);
            } elseif ($user->role->can_view_companies === 3) {
                // Для администраторов доступны все компании
                // Ничего не добавляем к запросу
            } else {
                // Для остальных ролей проверяем, что они владельцы
                $companyQuery->where('owner_user_id', $currentUserId);
            }
        } else {
            // Если пользователь не авторизован или нет роли, проверяем, что он владелец
            $companyQuery->where('owner_user_id', $currentUserId);
        }
        
        $company = $companyQuery->first();

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

        // Получаем регион компании напрямую из поля region_id
        $region = $company->region;
        if (!$region) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'У компании не указан регион'
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['company_id' => 'У компании не указан регион']);
        }

        // Получаем регионального представителя из компании
        $regionalUserId = $company->regional_user_id ?: 1;

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
            'state_id' => $validated['state_id'],
            'available_id' => $validated['available_id'],
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
        // Получаем максимальный sort_order для существующих медиафайлов товара
        $maxSortOrder = ProductMedia::where('product_id', $product->id)->max('sort_order') ?? -1;
        $sortOrder = $maxSortOrder + 1;

        // Получаем название склада, артикул организации и товара для создания структуры папок
        $warehouseName = $this->transliterate($product->warehouse->name ?? 'unknown');
        $companySku = $this->transliterate($product->company->sku ?? 'unknown');
        $productSku = $this->transliterate($product->sku ?? 'unknown');

        // Создаем структуру папок: products/название_склада/артикул_организации/артикул_товара
        $folderPath = "products/{$warehouseName}/{$companySku}/{$productSku}";
        
        // Если в конфиге UPLOAD_TO_WINDOWS_FOLDER=true, добавляем webserv/ перед products
        if (config('filesystems.upload_to_windows_folder')) {
            $folderPath = "webserv/{$folderPath}";
        }

        foreach ($files as $file) {
            if ($file->isValid()) {
                try {
                    // Определяем тип файла
                    $mimeType = $file->getMimeType();
                    $fileType = $this->getFileType($mimeType);

                    // Генерируем уникальное имя файла
                    $fileName = $this->generateUniqueFileName($file);

                    // Получаем полный путь к директории для дополнительной диагностики
                    $fullPath = storage_path('app/public/' . $folderPath);
                    
                    // Проверяем права на запись в директорию
                    if (!is_dir($fullPath)) {
                        \Log::warning('Директория не существует, будет создана', [
                            'path' => $fullPath,
                            'folder_path' => $folderPath
                        ]);
                    }

                    // Сохраняем файл в созданную структуру папок
                    // Теперь visibility отключена в config/filesystems.php, поэтому не будет ошибки прав доступа
                    $filePath = $file->storeAs($folderPath, $fileName, 'public');

                    // Проверяем, что файл успешно сохранен
                    if ($filePath === false) {
                        \Log::error('Ошибка сохранения медиафайла: storeAs вернул false', [
                            'product_id' => $product->id,
                            'file_name' => $file->getClientOriginalName(),
                            'folder_path' => $folderPath,
                            'file_name_generated' => $fileName,
                            'full_path' => $fullPath,
                            'storage_path_exists' => is_dir(storage_path('app/public')),
                            'storage_writable' => is_writable(storage_path('app/public')),
                            'target_dir_exists' => is_dir($fullPath),
                            'target_dir_writable' => is_dir($fullPath) ? is_writable($fullPath) : 'N/A'
                        ]);
                        continue; // Пропускаем этот файл и переходим к следующему
                    }

                    // Проверяем, что файл действительно существует на диске
                    $savedFilePath = storage_path('app/public/' . $filePath);
                    if (!file_exists($savedFilePath)) {
                        \Log::error('Файл сохранен, но не найден на диске', [
                            'product_id' => $product->id,
                            'file_path' => $filePath,
                            'full_saved_path' => $savedFilePath
                        ]);
                        continue;
                    }

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

                    \Log::info('Медиафайл успешно сохранен', [
                        'product_id' => $product->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath
                    ]);

                } catch (\Exception $e) {
                    \Log::error('Исключение при сохранении медиафайла', [
                        'product_id' => $product->id,
                        'file_name' => $file->getClientOriginalName(),
                        'folder_path' => $folderPath,
                        'error_message' => $e->getMessage(),
                        'error_code' => $e->getCode(),
                        'error_file' => $e->getFile(),
                        'error_line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    continue; // Пропускаем этот файл и переходим к следующему
                }
            } else {
                \Log::warning('Файл не прошел валидацию', [
                    'product_id' => $product->id,
                    'file_name' => $file->getClientOriginalName(),
                    'error' => $file->getError(),
                    'error_message' => $file->getErrorMessage()
                ]);
            }
        }
    }

    /**
     * Транслитерирует строку, заменяя кириллицу на латиницу и тире на нижнее подчеркивание
     */
    private function transliterate($string)
    {
        // Заменяем тире на нижнее подчеркивание
        $string = str_replace('-', '_', $string);
        
        // Массив соответствий кириллицы и латиницы
        $converter = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
            'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch',
            'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        ];
        
        // Заменяем кириллицу на латиницу
        $string = strtr($string, $converter);
        
        // Удаляем все символы, кроме букв, цифр и нижнего подчеркивания
        $string = preg_replace('/[^a-zA-Z0-9_]/', '', $string);
        
        // Убираем множественные подчеркивания
        $string = preg_replace('/_+/', '_', $string);
        
        // Убираем подчеркивания в начале и конце
        $string = trim($string, '_');
        
        // Если строка пустая, возвращаем 'unknown'
        return $string ?: 'unknown';
    }

    /**
     * Публичный метод для тестирования транслитерации
     */
    public function testTransliterate($string)
    {
        return $this->transliterate($string);
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
            'state',
            'available',
            'warehouse',
            'mediaOrdered',
            'check.checkStatus',
            'loading.installStatus',
            'removal.installStatus',
            'paymentVariants.priceType',
            'mainPaymentMethod',
            'advertisements.status'
        ]);

        // Получаем последний лог товара
        $lastLog = ProductLog::where('product_id', $product->id)
            ->with(['type', 'user'])
            ->latest()
            ->first();

        // Получаем первое невыполненное действие товара (сортировка по сроку выполнения ASC)
        $lastAction = ProductAction::where('product_id', $product->id)
            ->where('status', false)
            ->orderBy('expired_at', 'asc')
            ->first();

        $statuses = ProductStatus::all();
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();

        // Определяем права пользователя на редактирование
        $user = auth()->user();
        $canEdit = false;
        $canChangeStatus = false;
        
        if ($user && $user->role) {
            // Администратор может редактировать все товары и изменять статус
            if ($user->role->can_view_companies === 3) {
                $canEdit = true;
                $canChangeStatus = true;
            }
            // Владелец товара может редактировать свои товары и изменять статус
            elseif ($user->role->can_view_companies === 1 && $product->owner_id === $user->id) {
                $canEdit = true;
                $canChangeStatus = true;
            }
            // Региональный представитель может редактировать товары, где он назначен, но не изменять статус
            elseif ($user->role->name === 'Региональный представитель' && $product->regional_id === $user->id) {
                $canEdit = true;
                $canChangeStatus = false;
            }
        }

        return view('Product.ProductItemPage', compact('product', 'statuses', 'checkStatuses', 'installStatuses', 'lastLog', 'lastAction', 'canEdit', 'canChangeStatus'));
    }

    public function edit(Product $product)
    {
        $product->load(['mediaOrdered']);
        $user = auth()->user();
        $currentUserId = auth()->id() ?? 1;
        
        // Определяем запрос для получения компаний в зависимости от роли пользователя
        $companiesQuery = Company::with('status');
        
        if ($user && $user->role) {
            if ($user->role->name === 'Региональный представитель') {
                // Для регионального представителя показываем компании, где он назначен как региональный представитель
                $companiesQuery->where('regional_user_id', $currentUserId);
            } elseif ($user->role->name === 'Менеджер') {
                // Для менеджера показываем только его компании
                $companiesQuery->where('owner_user_id', $currentUserId);
            } elseif ($user->role->can_view_companies === 1) {
                // Для пользователей с ограниченным доступом показываем только их компании
                $companiesQuery->where('owner_user_id', $currentUserId);
            } elseif ($user->role->can_view_companies === 3) {
                // Для администраторов показываем все компании
                // Ничего не добавляем к запросу
            } else {
                // Для остальных ролей показываем только их компании
                $companiesQuery->where('owner_user_id', $currentUserId);
            }
        } else {
            // Если пользователь не авторизован или нет роли, показываем только его компании
            $companiesQuery->where('owner_user_id', $currentUserId);
        }
        
        $companies = $companiesQuery->get();
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
        
        // Сохраняем старое значение категории для сравнения
        $oldCategoryId = $product->category_id;

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

        // Обновляем категорию в связанных объявлениях, если категория изменилась
        if ($oldCategoryId != $request->category_id && $request->category_id) {
            $advertisements = $product->advertisements()->get();
            if ($advertisements->isNotEmpty()) {
                foreach ($advertisements as $advertisement) {
                    $advertisement->update(['category_id' => $request->category_id]);
                }
            }
        }

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
        // Проверяем права пользователя на обновление статуса товара
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может обновлять только свои товары
            if ($product->owner_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies === 0 && $user->role->name === 'Региональный представитель') {
            // Региональный представитель не может обновлять статус товаров
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        } elseif ($user->role->name === 'Региональный представитель') {
            // Региональный представитель не может обновлять статус товаров
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на обновление товаров
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

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

            // Проверяем, нужно ли создать задачу для создания объявления
            if ($newStatus->name === 'В продаже') {
                $hasAdvertisement = $product->advertisements()->exists();
                
                if (!$hasAdvertisement) {
                    ProductAction::create([
                        'product_id' => $product->id,
                        'user_id' => $product->owner_id ?? auth()->id(),
                        'action' => 'Подготовить и опубликовать объявление на товар',
                        'expired_at' => now()->addDays(1),
                        'status' => false
                    ]);
                }
            }

            // Проверяем, нужно ли создать задачу для ревизии товара
            if ($newStatus->name === 'Ревизия') {
                ProductAction::create([
                    'product_id' => $product->id,
                    'user_id' => $product->owner_id ?? auth()->id(),
                    'action' => 'С поставщиком актуализировать статус товара, проверить наличие, подтвердить цену',
                    'expired_at' => now()->addDays(1),
                    'status' => false
                ]);
            }

            // Обновляем статус связанных объявлений
            $this->updateAdvertisementStatuses($product, $oldStatus, $newStatus);

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
        // Проверяем права пользователя на просмотр логов
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Региональный представитель не может просматривать логи
        if ($user->role->name === 'Региональный представитель') {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

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
        // Проверяем права пользователя на просмотр действий
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Региональный представитель не может просматривать действия
        if ($user->role->name === 'Региональный представитель') {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

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
        // Проверяем права пользователя на создание действий
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Региональный представитель не может создавать действия
        if ($user->role->name === 'Региональный представитель') {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

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
        // Проверяем права пользователя на выполнение действий
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Региональный представитель не может выполнять действия
        if ($user->role->name === 'Региональный представитель') {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

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
            'action' => 'С поставщиком подтвердить цену, условия, принять решение о продаже.',
            'expired_at' => now()->addDays(1), // Срок выполнения - 7 дней
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

  
    /**
     * Обрабатывает перевод товара в статус "Отказ"
     */


    /**
     * Обновляет основную информацию товара (категория и адрес станка)
     */
    public function updateMainInfo(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'nullable|exists:product_categories,id',
            'product_address' => 'nullable|string|max:255',
            'state_id' => 'nullable|exists:product_states,id',
            'available_id' => 'nullable|exists:product_availables,id'
        ]);

        // Сохраняем старые значения для логирования
        $oldCategoryId = $product->category_id;
        $oldProductAddress = $product->product_address;
        $oldStateId = $product->state_id;
        $oldAvailableId = $product->available_id;

        // Обновляем товар
        $product->update([
            'category_id' => $request->category_id,
            'product_address' => $request->product_address,
            'state_id' => $request->state_id,
            'available_id' => $request->available_id
        ]);

        // Обновляем категорию в связанных объявлениях, если категория изменилась
        if ($oldCategoryId != $request->category_id && $request->category_id) {
            $advertisements = $product->advertisements()->get();
            if ($advertisements->isNotEmpty()) {
                foreach ($advertisements as $advertisement) {
                    $advertisement->update(['category_id' => $request->category_id]);
                }
            }
        }

        // Создаем лог изменений
        $logMessages = [];
        
        if ($oldCategoryId != $request->category_id) {
            $oldCategory = $oldCategoryId ? \App\Models\ProductCategories::find($oldCategoryId) : null;
            $newCategory = $request->category_id ? \App\Models\ProductCategories::find($request->category_id) : null;
            
            $logMessages[] = "Категория изменена с '" . ($oldCategory ? $oldCategory->name : 'Не указана') . "' на '" . ($newCategory ? $newCategory->name : 'Не указана') . "'";
        }
        
        if ($oldProductAddress != $request->product_address) {
            $logMessages[] = "Адрес станка изменен с '" . ($oldProductAddress ?: 'Не указан') . "' на '" . ($request->product_address ?: 'Не указан') . "'";
        }

        if ($oldStateId != $request->state_id) {
            $oldState = $oldStateId ? \App\Models\ProductState::find($oldStateId) : null;
            $newState = $request->state_id ? \App\Models\ProductState::find($request->state_id) : null;
            
            $logMessages[] = "Состояние изменено с '" . ($oldState ? $oldState->name : 'Не указано') . "' на '" . ($newState ? $newState->name : 'Не указано') . "'";
        }

        if ($oldAvailableId != $request->available_id) {
            $oldAvailable = $oldAvailableId ? \App\Models\ProductAvailable::find($oldAvailableId) : null;
            $newAvailable = $request->available_id ? \App\Models\ProductAvailable::find($request->available_id) : null;
            
            $logMessages[] = "Доступность изменена с '" . ($oldAvailable ? $oldAvailable->name : 'Не указана') . "' на '" . ($newAvailable ? $newAvailable->name : 'Не указана') . "'";
        }

        if (!empty($logMessages)) {
            ProductLog::create([
                'product_id' => $product->id,
                'type_id' => 2, // Системный тип лога
                'log' => 'Обновлена основная информация: ' . implode(', ', $logMessages),
                'user_id' => null // Системный лог
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Основная информация успешно обновлена'
        ]);
    }

    public function updateTitle(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:1'
        ]);

        try {
            // Сохраняем старое значение для логирования
            $oldName = $product->name;
            $newName = trim($request->name);

            // Проверяем, что название действительно изменилось
            if ($oldName === $newName) {
                return response()->json(['success' => true, 'message' => 'Название товара не изменилось']);
            }

            // Обновляем название товара
            $product->update([
                'name' => $newName
            ]);

            // Создаем запись в логе от имени системы
            $this->logProductTitleChanges($product, $oldName, $newName);

            return response()->json([
                'success' => true, 
                'message' => 'Название товара успешно обновлено',
                'new_name' => $newName
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении названия товара'], 500);
        }
    }

    /**
     * Скачивает все медиафайлы товара в ZIP архиве
     */
    public function downloadMedia(Product $product)
    {
        // Устанавливаем увеличенные лимиты для обработки больших файлов
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1024M');
        
        // Проверяем права доступа
        $user = auth()->user();
        $canAccess = false;
        
        if ($user && $user->role) {
            if ($user->role->can_view_companies === 3) {
                $canAccess = true;
            } elseif ($user->role->can_view_companies === 1 && $product->owner_id === $user->id) {
                $canAccess = true;
            } elseif ($user->role->name === 'Региональный представитель' && $product->regional_id === $user->id) {
                $canAccess = true;
            }
        }
        
        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав для скачивания медиафайлов этого товара'
            ], 403);
        }

        // Получаем все медиафайлы товара
        $mediaFiles = $product->mediaOrdered;
        
        if ($mediaFiles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'У товара нет медиафайлов для скачивания'
            ], 404);
        }

        // Создаем временную директорию для архива
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Создаем имя архива
        $zipFileName = 'product_' . $product->id . '_media_' . time() . '.zip';
        $zipPath = $tempDir . '/' . $zipFileName;

        // Создаем ZIP архив
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании архива'
            ], 500);
        }

        // Добавляем файлы в архив
        $totalSize = 0;
        $addedFiles = 0;
        
        foreach ($mediaFiles as $media) {
            $filePath = storage_path('app/public/' . $media->file_path);
            
            if (file_exists($filePath)) {
                $fileSize = filesize($filePath);
                $totalSize += $fileSize;
                
                // Проверяем общий размер файлов (максимум 2GB)
                if ($totalSize > 2 * 1024 * 1024 * 1024) {
                    $zip->close();
                    unlink($zipPath);
                    return response()->json([
                        'success' => false,
                        'message' => 'Общий размер медиафайлов превышает 2GB. Пожалуйста, скачайте файлы по частям.'
                    ], 413);
                }
                
                // Создаем безопасное имя файла для архива
                $safeFileName = $this->sanitizeFileName($media->file_name);
                $zip->addFile($filePath, $safeFileName);
                $addedFiles++;
            }
        }

        $zip->close();

        // Проверяем, что архив создался и не пустой
        if (!file_exists($zipPath) || filesize($zipPath) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании архива'
            ], 500);
        }

        // Получаем размер архива
        $archiveSize = filesize($zipPath);
        
        // Устанавливаем заголовки для потоковой передачи
        $headers = [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
            'Content-Length' => $archiveSize,
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        // Возвращаем файл для скачивания с потоковой передачей
        return response()->download($zipPath, $zipFileName, $headers)
            ->deleteFileAfterSend(true);
    }

    /**
     * Скачивает отдельный медиафайл товара
     */
    public function downloadSingleMedia(Product $product, $mediaId)
    {
        // Устанавливаем увеличенные лимиты
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');
        
        // Проверяем права доступа
        $user = auth()->user();
        $canAccess = false;
        
        if ($user && $user->role) {
            if ($user->role->can_view_companies === 3) {
                $canAccess = true;
            } elseif ($user->role->can_view_companies === 1 && $product->owner_id === $user->id) {
                $canAccess = true;
            } elseif ($user->role->name === 'Региональный представитель' && $product->regional_id === $user->id) {
                $canAccess = true;
            }
        }
        
        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав для скачивания медиафайлов этого товара'
            ], 403);
        }

        // Находим медиафайл
        $media = $product->mediaOrdered()->find($mediaId);
        
        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Медиафайл не найден'
            ], 404);
        }

        $filePath = storage_path('app/public/' . $media->file_path);
        
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Файл не найден на сервере'
            ], 404);
        }

        // Проверяем размер файла
        $fileSize = filesize($filePath);
        if ($fileSize > 500 * 1024 * 1024) { // 500MB
            return response()->json([
                'success' => false,
                'message' => 'Файл слишком большой для скачивания. Максимальный размер: 500MB'
            ], 413);
        }

        // Возвращаем файл для скачивания
        return response()->download($filePath, $media->file_name, [
            'Content-Type' => $media->mime_type,
            'Content-Length' => $fileSize,
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache'
        ]);
    }

    /**
     * Получает список медиафайлов для скачивания по одному
     */
    public function getMediaList(Product $product)
    {
        // Проверяем права доступа
        $user = auth()->user();
        $canAccess = false;
        
        if ($user && $user->role) {
            if ($user->role->can_view_companies === 3) {
                $canAccess = true;
            } elseif ($user->role->can_view_companies === 1 && $product->owner_id === $user->id) {
                $canAccess = true;
            } elseif ($user->role->name === 'Региональный представитель' && $product->regional_id === $user->id) {
                $canAccess = true;
            }
        }
        
        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав для просмотра медиафайлов этого товара'
            ], 403);
        }

        $mediaFiles = $product->mediaOrdered()->select([
            'id', 'file_name', 'file_path', 'file_type', 'mime_type'
        ])->get();

        $mediaList = [];
        $totalSize = 0;
        
        foreach ($mediaFiles as $media) {
            $filePath = storage_path('app/public/' . $media->file_path);
            $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
            $totalSize += $fileSize;
            
            $mediaList[] = [
                'id' => $media->id,
                'name' => $media->file_name,
                'type' => $media->file_type,
                'mime_type' => $media->mime_type,
                'size' => $fileSize,
                'size_formatted' => $this->formatFileSize($fileSize),
                'download_url' => route('products.download-single-media', ['product' => $product->id, 'mediaId' => $media->id])
            ];
        }

        return response()->json([
            'success' => true,
            'media_files' => $mediaList,
            'total_count' => count($mediaList),
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatFileSize($totalSize)
        ]);
    }

    /**
     * Форматирует размер файла в читаемый вид
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Очищает имя файла от недопустимых символов
     */
    private function sanitizeFileName($fileName)
    {
        // Удаляем недопустимые символы для файловой системы
        $fileName = preg_replace('/[<>:"\/\\\\|?*]/', '_', $fileName);
        
        // Ограничиваем длину имени файла
        if (strlen($fileName) > 200) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $name = pathinfo($fileName, PATHINFO_FILENAME);
            $fileName = substr($name, 0, 200 - strlen($extension) - 1) . '.' . $extension;
        }
        
        return $fileName;
    }

    /**
     * Загружает дополнительные медиафайлы для существующего товара
     */
    public function uploadAdditionalMedia(Request $request, Product $product)
    {
        // Проверяем права пользователя на редактирование товара
        $user = auth()->user();
        $canEdit = false;
        
        if ($user && $user->role) {
            // Администратор может редактировать все товары
            if ($user->role->can_view_companies === 3) {
                $canEdit = true;
            }
            // Владелец товара может редактировать свои товары
            elseif ($user->role->can_view_companies === 1 && $product->owner_id === $user->id) {
                $canEdit = true;
            }
            // Региональный представитель может редактировать товары, где он назначен
            elseif ($user->role->name === 'Региональный представитель' && $product->regional_id === $user->id) {
                $canEdit = true;
            }
        }
        
        if (!$canEdit) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав для редактирования этого товара'
            ], 403);
        }

        $validator = \Validator::make($request->all(), [
            'media_files.*' => 'required|file|mimes:jpeg,png,gif,mp4,mov,avi|max:1024000', // 1000MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
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
                return response()->json([
                    'success' => false,
                    'message' => 'Общий размер файлов превышает лимит в 1000MB. Пожалуйста, загрузите файлы по частям.',
                    'total_size' => $totalSize,
                    'max_size' => $maxTotalSize
                ], 413);
            }
        }

        try {
            // Обработка загрузки медиафайлов
            if ($request->hasFile('media_files')) {
                $this->handleMediaFiles($request->file('media_files'), $product);
            }

            // Создаем лог о загрузке дополнительных медиафайлов
            $this->logAdditionalMediaUpload($product, $request->file('media_files'));

            return response()->json([
                'success' => true,
                'message' => 'Медиафайлы успешно загружены!',
                'redirect' => route('products.show', $product)
            ]);

        } catch (\Exception $e) {
            \Log::error('Ошибка при загрузке дополнительных медиафайлов', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке медиафайлов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удаляет медиафайл товара и связанные медиафайлы из объявлений
     */
    public function deleteMedia(Request $request, Product $product, $mediaId)
    {
        // Проверяем права пользователя на редактирование товара
        $user = auth()->user();
        $canEdit = false;
        
        if ($user && $user->role) {
            // Администратор может редактировать все товары
            if ($user->role->can_view_companies === 3) {
                $canEdit = true;
            }
            // Владелец товара может редактировать свои товары
            elseif ($user->role->can_view_companies === 1 && $product->owner_id === $user->id) {
                $canEdit = true;
            }
            // Региональный представитель может редактировать товары, где он назначен
            elseif ($user->role->name === 'Региональный представитель' && $product->regional_id === $user->id) {
                $canEdit = true;
            }
        }
        
        if (!$canEdit) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав для редактирования этого товара'
            ], 403);
        }

        try {
            // Находим медиафайл товара
            $media = $product->mediaOrdered()->find($mediaId);
            
            if (!$media) {
                return response()->json([
                    'success' => false,
                    'message' => 'Медиафайл не найден'
                ], 404);
            }

            // Находим все связанные медиафайлы в объявлениях
            $advertisementMedia = \App\Models\AdvertisementMedia::where('product_media_id', $mediaId)->get();
            
            // Удаляем связанные медиафайлы из объявлений
            foreach ($advertisementMedia as $advMedia) {
                $advMedia->delete();
            }

            // Удаляем файл с диска
            if (Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }

            // Удаляем запись из базы данных
            $media->delete();

            // Создаем лог о удалении медиафайла
            $this->logMediaDeletion($product, $media, $advertisementMedia->count());

            return response()->json([
                'success' => true,
                'message' => 'Медиафайл успешно удален',
                'deleted_from_advertisements' => $advertisementMedia->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Ошибка при удалении медиафайла', [
                'product_id' => $product->id,
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении медиафайла: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Логирует загрузку дополнительных медиафайлов
     */
    private function logAdditionalMediaUpload(Product $product, $files)
    {
        $userName = auth()->user()->name ?? 'Неизвестный пользователь';
        $fileCount = count($files);
        $logMessage = "Пользователь {$userName} загрузил {$fileCount} дополнительных медиафайлов";
        
        $systemLogType = LogType::where('name', 'Системный')->first();
        
        ProductLog::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'log' => $logMessage,
            'type_id' => $systemLogType ? $systemLogType->id : null
        ]);
    }

    /**
     * Логирует удаление медиафайла
     */
    private function logMediaDeletion(Product $product, ProductMedia $media, $deletedFromAdvertisementsCount)
    {
        $userName = auth()->user()->name ?? 'Неизвестный пользователь';
        $logMessage = "Пользователь {$userName} удалил медиафайл '{$media->file_name}'";
        
        if ($deletedFromAdvertisementsCount > 0) {
            $logMessage .= " (также удален из {$deletedFromAdvertisementsCount} объявлений)";
        }
        
        $systemLogType = LogType::where('name', 'Системный')->first();
        
        ProductLog::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'log' => $logMessage,
            'type_id' => $systemLogType ? $systemLogType->id : null
        ]);
    }

    /**
     * Обновляет статус связанных объявлений при смене статуса товара
     */
    private function updateAdvertisementStatuses(Product $product, $oldStatus, $newStatus)
    {
        $advertisements = $product->advertisements()->get();

        if ($advertisements->isEmpty()) {
            return;
        }

        // Определяем новый статус для объявлений на основе статуса товара
        $newAdvertisementStatusName = $this->getAdvertisementStatusByProductStatus($newStatus->name);
        
        if (!$newAdvertisementStatusName) {
            return; // Если нет соответствующего статуса для объявления
        }

        // Получаем ID нового статуса объявления
        $newAdvertisementStatus = AdvertisementStatus::where('name', $newAdvertisementStatusName)->first();
        
        if (!$newAdvertisementStatus) {
            \Log::warning('Статус объявления не найден', [
                'status_name' => $newAdvertisementStatusName,
                'product_id' => $product->id
            ]);
            return;
        }

        // Обновляем статусы объявлений
        foreach ($advertisements as $advertisement) {
            $oldAdvertisementStatus = $advertisement->status;
            
            // Подготавливаем данные для обновления
            $updateData = ['status_id' => $newAdvertisementStatus->id];
            
            // Если новый статус "В продаже", "Холд" или "Резерв", обновляем published_at
            if (in_array($newAdvertisementStatusName, ['В продаже', 'Холд', 'Резерв'])) {
                $updateData['published_at'] = now();
            }
            
            // Обновляем статус объявления
            $advertisement->update($updateData);
            
            // Создаем лог о смене статуса объявления
            $this->logAdvertisementStatusChange($advertisement, $oldAdvertisementStatus, $newAdvertisementStatus, $newStatus);
            
            // Если новый статус объявления "Ревизия", создаем задачу на актуализацию
            if ($newAdvertisementStatusName === 'Ревизия' && $newStatus->name !== 'Ревизия') {
                $this->createAdvertisementReviewTask($advertisement);
            }
        }
    }

    /**
     * Определяет статус объявления на основе статуса товара
     */
    private function getAdvertisementStatusByProductStatus($productStatusName)
    {
        $statusMapping = [
            'Ревизия' => 'Ревизия',
            'В продаже' => 'Ревизия', 
            'Резерв' => 'Резерв',
            'Холд' => 'Холд',
            'Продано' => 'Продано',
            'Вторая очередь' => 'Архив',
            'Отказ' => 'Архив'
        ];

        return $statusMapping[$productStatusName] ?? null;
    }

    /**
     * Логирует смену статуса объявления
     */
    private function logAdvertisementStatusChange($advertisement, $oldStatus, $newStatus, $productStatus)
    {
        $systemLogType = LogType::where('name', 'Системный')->first();
        
        $logMessage = "Статус объявления изменен с '{$oldStatus->name}' на '{$newStatus->name}' в связи со сменой статуса связанного товара на '{$productStatus->name}'";
        
        AdvLog::create([
            'advertisement_id' => $advertisement->id,
            'type_id' => $systemLogType ? $systemLogType->id : null,
            'log' => $logMessage,
            'user_id' => null // От имени системы
        ]);
    }

    /**
     * Логирует изменения названия товара
     */
    private function logProductTitleChanges(Product $product, $oldName, $newName)
    {
        $userName = auth()->user()->name ?? 'Неизвестный пользователь';
        $logMessage = "Пользователь {$userName} изменил название товара с '{$oldName}' на '{$newName}'";

        $systemLogType = \App\Models\LogType::where('name', 'Системный')->first();

        ProductLog::create([
            'product_id' => $product->id,
            'type_id' => $systemLogType ? $systemLogType->id : null,
            'log' => $logMessage,
            'user_id' => null // От имени системы
        ]);
    }

    /**
     * Создает задачу на актуализацию объявления при переводе в статус "Ревизия"
     */
    private function createAdvertisementReviewTask($advertisement)
    {
        try {
            // Создаем задачу на актуализацию объявления сроком на 7 дней
            $action = AdvAction::create([
                'advertisement_id' => $advertisement->id,
                'user_id' => $advertisement->created_by, // Назначаем задачу создателю объявления
                'action' => 'Актуализировать и опубликовать обьявление',
                'expired_at' => now()->addDays(1), // Срок выполнения - 7 дней
                'status' => false, // Задача не выполнена
            ]);

            // Создаем лог о создании задачи
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            $logMessage = "Создана задача на актуализацию объявления в связи с переводом в статус 'Ревизия'";
            
            AdvLog::create([
                'advertisement_id' => $advertisement->id,
                'type_id' => $systemLogType ? $systemLogType->id : null,
                'log' => $logMessage,
                'user_id' => null // От имени системы
            ]);

            \Log::info('Создана задача на актуализацию объявления', [
                'advertisement_id' => $advertisement->id,
                'action_id' => $action->id,
                'expired_at' => $action->expired_at
            ]);

        } catch (\Exception $e) {
            \Log::error('Ошибка при создании задачи на актуализацию объявления', [
                'advertisement_id' => $advertisement->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

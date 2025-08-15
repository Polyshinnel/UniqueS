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
            'activeAdvertisement'
        ])->get();

        return view('Product.ProductPage', compact('products'));
    }

    public function create()
    {
        $warehouses = Warehouses::where('active', true)->get();
        
        // Получаем только компании, принадлежащие авторизованному пользователю
        $currentUserId = auth()->id() ?? 1; // Временно используем id=1, пока нет аутентификации
        $companies = Company::with('status')
            ->where('owner_user_id', $currentUserId)
            ->get();
            
        $categories = ProductCategories::all();
        $statuses = ProductStatus::where('active', true)->get();
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();
        $priceTypes = ProductPriceType::all();

        return view('Product.ProductCreatePage', compact(
            'warehouses', 
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
            'warehouse_id' => 'required|exists:warehouses,id',
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
        $company = Company::where('id', $validated['company_id'])
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

        // Получаем регионального представителя из выбранной компании
        $regionalUserId = $company->regional_user_id ?? 1;

        // Генерируем SKU для товара, если не указан
        $sku = $validated['sku'] ?: $this->generateSku($validated['company_id']);

        // Создаем товар со статусом "В работе" (id = 1)
        $product = Product::create([
            'name' => $validated['name'],
            'sku' => $sku,
            'warehouse_id' => $validated['warehouse_id'],
            'company_id' => $validated['company_id'],
            'category_id' => $validated['category_id'],
            'owner_id' => $currentUserId, // Владелец товара - авторизованный пользователь
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

        $statuses = ProductStatus::all();

        return view('Product.ProductItemPage', compact('product', 'statuses'));
    }

    public function edit(Product $product)
    {
        $product->load(['mediaOrdered']);
        $warehouses = Warehouses::where('active', true)->get();
        $companies = Company::with('status')->get();
        $categories = ProductCategories::all();
        $statuses = ProductStatus::where('active', true)->get();

        return view('Product.ProductEditPage', compact('product', 'warehouses', 'companies', 'categories', 'statuses'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
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

        // Обновляем товар
        $product->update([
            'name' => $request->name,
            'warehouse_id' => $request->warehouse_id,
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
            $product->update([$field => $value]);
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
            'install_status_id' => 'required|exists:product_install_statuses,id',
            'comment' => 'nullable|string|max:1000'
        ]);

        $loading = $product->loading->first();
        if ($loading) {
            $loading->update([
                'install_status_id' => $request->install_status_id,
                'comment' => $request->comment
            ]);
        } else {
            ProductLoading::create([
                'product_id' => $product->id,
                'install_status_id' => $request->install_status_id,
                'comment' => $request->comment ?? ''
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Статус погрузки обновлен'
        ]);
    }

    public function updateRemovalStatus(Request $request, Product $product)
    {
        $request->validate([
            'install_status_id' => 'required|exists:product_install_statuses,id',
            'comment' => 'nullable|string|max:1000'
        ]);

        $removal = $product->removal->first();
        if ($removal) {
            $removal->update([
                'install_status_id' => $request->install_status_id,
                'comment' => $request->comment
            ]);
        } else {
            ProductRemoval::create([
                'product_id' => $product->id,
                'install_status_id' => $request->install_status_id,
                'comment' => $request->comment ?? ''
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Статус демонтажа обновлен'
        ]);
    }

    public function updateCheckStatus(Request $request, Product $product)
    {
        $request->validate([
            'check_status_id' => 'required|exists:product_check_statuses,id',
            'comment' => 'nullable|string|max:1000'
        ]);

        $check = $product->check->first();
        if ($check) {
            $check->update([
                'check_status_id' => $request->check_status_id,
                'comment' => $request->comment
            ]);
        } else {
            ProductCheck::create([
                'product_id' => $product->id,
                'check_status_id' => $request->check_status_id,
                'comment' => $request->comment ?? ''
            ]);
        }

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

        return response()->json([
            'success' => true,
            'message' => 'Информация об оплате обновлена'
        ]);
    }

    public function updateStatus(Request $request, Product $product)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:product_statuses,id'
        ]);

        $product->update([
            'status_id' => $validated['status_id']
        ]);

        $product->load('status');

        return response()->json([
            'success' => true,
            'status' => $product->status,
            'message' => 'Статус товара успешно обновлен'
        ]);
    }
}

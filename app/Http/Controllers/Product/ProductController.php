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
            'mainImage',
            'check.checkStatus',
            'loading.installStatus',
            'removal.installStatus',
            'paymentVariants.priceType'
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
        $request->validate([
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
            'purchase_price' => 'nullable|numeric|min:0',
            'payment_comment' => 'nullable|string',
            'media_files.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,mov,avi|max:51200', // 50MB max
        ]);

        // Проверяем, что выбранная компания принадлежит авторизованному пользователю
        $currentUserId = auth()->id() ?? 1;
        $company = Company::where('id', $request->company_id)
            ->where('owner_user_id', $currentUserId)
            ->first();

        if (!$company) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['company_id' => 'Выбранная компания недоступна для вашего пользователя']);
        }

        // Получаем регионального представителя из выбранной компании
        $regionalUserId = $company->regional_user_id ?? 1;

        // Генерируем SKU для товара, если не указан
        $sku = $request->sku ?: $this->generateSku($request->company_id);

        // Создаем товар со статусом "В работе" (id = 1)
        $product = Product::create([
            'name' => $request->name,
            'sku' => $sku,
            'warehouse_id' => $request->warehouse_id,
            'company_id' => $request->company_id,
            'category_id' => $request->category_id,
            'owner_id' => $currentUserId, // Владелец товара - авторизованный пользователь
            'regional_id' => $regionalUserId, // Региональный представитель из выбранной компании
            'status_id' => 1, // Статус "В работе"
            'product_address' => $request->product_address ?? '',
            'main_chars' => $request->main_chars,
            'mark' => $request->mark,
            'complectation' => $request->complectation,
            'main_payment_method' => $request->payment_types[0] ?? null, // Первый выбранный тип оплаты
            'purchase_price' => $request->purchase_price,
            'payment_comment' => $request->payment_comment,
            'add_expenses' => 0, // Временно 0
        ]);

        // Создаем запись о проверке
        if ($request->check_status_id) {
            ProductCheck::create([
                'product_id' => $product->id,
                'check_status_id' => $request->check_status_id,
                'comment' => $request->check_comment ?? '',
            ]);
        }

        // Создаем запись о погрузке
        if ($request->loading_status_id) {
            ProductLoading::create([
                'product_id' => $product->id,
                'install_status_id' => $request->loading_status_id,
                'comment' => $request->loading_comment ?? '',
            ]);
        }

        // Создаем запись о демонтаже
        if ($request->removal_status_id) {
            ProductRemoval::create([
                'product_id' => $product->id,
                'install_status_id' => $request->removal_status_id,
                'comment' => $request->removal_comment ?? '',
            ]);
        }

        // Создаем записи о вариантах оплаты
        if ($request->payment_types && is_array($request->payment_types)) {
            foreach ($request->payment_types as $priceTypeId) {
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

        return redirect()->route('products.index')->with('success', 'Товар успешно создан!');
    }

    private function generateSku($companyId)
    {
        $company = Company::find($companyId);
        $supplierSku = $company->sku ?? '000'; // Получаем артикул поставщика из компании
        $date = now()->format('dmYHi'); // Форматируем дату и время: день, месяц, год, час, минуты

        return $supplierSku . $date;
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
            'mainPaymentMethod'
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
            'media_files.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,mov,avi|max:51200', // 50MB max
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
            'field' => 'required|in:loading_comment,removal_comment,check_comment,purchase_price,payment_comment',
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

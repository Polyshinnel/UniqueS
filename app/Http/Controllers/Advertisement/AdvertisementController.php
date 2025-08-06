<?php

namespace App\Http\Controllers\Advertisement;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AdvertisementMedia;
use App\Models\Product;
use App\Models\ProductCategories;
use App\Models\ProductMedia;
use App\Models\ProductStatus;
use App\Models\ProductCheckStatuses;
use App\Models\ProductInstallStatuses;
use App\Models\ProductPriceType;
use App\Models\ProductCheck;
use App\Models\ProductLoading;
use App\Models\ProductRemoval;
use App\Models\ProductPaymentVariants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdvertisementController extends Controller
{
    public function index()
    {
        $advertisements = Advertisement::with([
            'category',
            'product.company.addresses',
            'product.warehouse',
            'product.regional.role',
            'product.status',
            'creator',
            'mainImage'
        ])->get();

        return view('Advertisement.AdvertisementListPage', compact('advertisements'));
    }

    public function create(Request $request)
    {
        $productId = $request->get('product_id');
        $product = null;

        if ($productId) {
            $product = Product::with([
                'category',
                'company',
                'status',
                'warehouse',
                'mediaOrdered',
                'check.checkStatus',
                'loading.installStatus',
                'removal.installStatus',
                'paymentVariants.priceType'
            ])->findOrFail($productId);
        }

        $categories = ProductCategories::all();
        $products = Product::with('category', 'company')->where('owner_id', auth()->id())->get();
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();
        $priceTypes = ProductPriceType::all();

        return view('Advertisement.AdvertisementCreatePage', compact(
            'categories', 
            'products', 
            'product', 
            'checkStatuses',
            'installStatuses',
            'priceTypes'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:product_categories,id',
            'main_characteristics' => 'nullable|string',
            'complectation' => 'nullable|string',
            'technical_characteristics' => 'nullable|string',
            'additional_info' => 'nullable|string',
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
            'selected_product_media.*' => 'nullable|exists:products_media,id',
            'media_files.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,mov,avi|max:51200',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Проверяем, что товар принадлежит авторизованному пользователю
        if ($product->owner_id !== auth()->id()) {
            abort(403, 'Вы можете создавать объявления только для своих товаров.');
        }

        // Подготавливаем данные для JSON полей
        $checkData = null;
        if ($request->check_status_id || $request->check_comment) {
            $checkData = [
                'status_id' => $request->check_status_id,
                'comment' => $request->check_comment ?? '',
            ];
        }

        $loadingData = null;
        if ($request->loading_status_id || $request->loading_comment) {
            $loadingData = [
                'status_id' => $request->loading_status_id,
                'comment' => $request->loading_comment ?? '',
            ];
        }

        $removalData = null;
        if ($request->removal_status_id || $request->removal_comment) {
            $removalData = [
                'status_id' => $request->removal_status_id,
                'comment' => $request->removal_comment ?? '',
            ];
        }

        // Создаем объявление с данными в JSON полях
        $advertisement = Advertisement::create([
            'product_id' => $request->product_id,
            'title' => $request->title,
            'category_id' => $request->category_id,
            'main_characteristics' => $request->main_characteristics,
            'complectation' => $request->complectation,
            'technical_characteristics' => $request->technical_characteristics,
            'additional_info' => $request->additional_info,
            'check_data' => $checkData,
            'loading_data' => $loadingData,
            'removal_data' => $removalData,
            'status' => 'draft',
            'created_by' => auth()->id() ?? 1, // Временно используем id=1
        ]);

        // Обработка выбранных медиафайлов из товара
        if ($request->has('selected_product_media')) {
            $this->handleSelectedProductMedia($request->selected_product_media, $advertisement);
        }

        // Обработка загруженных медиафайлов
        if ($request->hasFile('media_files')) {
            $this->handleUploadedMedia($request->file('media_files'), $advertisement);
        }

        // Обновляем цену покупки и комментарий по оплате в товаре
        if ($request->purchase_price !== null || $request->payment_comment !== null) {
            $product->update([
                'purchase_price' => $request->purchase_price,
                'payment_comment' => $request->payment_comment,
            ]);
        }

        // Обновляем варианты оплаты в товаре
        if ($request->has('payment_types')) {
            // Удаляем старые варианты
            $product->paymentVariants()->delete();
            
            // Создаем новые варианты
            if (is_array($request->payment_types)) {
                foreach ($request->payment_types as $priceTypeId) {
                    ProductPaymentVariants::create([
                        'product_id' => $product->id,
                        'price_type' => $priceTypeId,
                    ]);
                }
            }
        }

        return redirect()->route('advertisements.index')->with('success', 'Объявление успешно создано!');
    }

    public function show(Advertisement $advertisement)
    {
        $advertisement->load([
            'category',
            'product.company.addresses',
            'product.paymentVariants.priceType',
            'creator',
            'mediaOrdered'
        ]);

        // Загружаем статусы для отображения
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();
        $priceTypes = ProductPriceType::all();

        return view('Advertisement.AdvertisementShowPage', compact('advertisement', 'checkStatuses', 'installStatuses', 'priceTypes'));
    }

    public function edit(Advertisement $advertisement)
    {
        $advertisement->load([
            'product.mediaOrdered', 
            'product.check.checkStatus',
            'product.loading.installStatus',
            'product.removal.installStatus',
            'product.paymentVariants.priceType',
            'mediaOrdered'
        ]);
        $categories = ProductCategories::all();
        $products = Product::with('category', 'company')->get();
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();
        $priceTypes = ProductPriceType::all();

        return view('Advertisement.AdvertisementEditPage', compact(
            'advertisement', 
            'categories', 
            'products', 
            'checkStatuses',
            'installStatuses',
            'priceTypes'
        ));
    }

    public function update(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:product_categories,id',
            'main_characteristics' => 'nullable|string',
            'complectation' => 'nullable|string',
            'technical_characteristics' => 'nullable|string',
            'additional_info' => 'nullable|string',
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
            'media_files.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,mov,avi|max:51200',
        ]);

        // Подготавливаем данные для JSON полей
        $checkData = null;
        if ($request->check_status_id || $request->check_comment) {
            $checkData = [
                'status_id' => $request->check_status_id,
                'comment' => $request->check_comment ?? '',
            ];
        }

        $loadingData = null;
        if ($request->loading_status_id || $request->loading_comment) {
            $loadingData = [
                'status_id' => $request->loading_status_id,
                'comment' => $request->loading_comment ?? '',
            ];
        }

        $removalData = null;
        if ($request->removal_status_id || $request->removal_comment) {
            $removalData = [
                'status_id' => $request->removal_status_id,
                'comment' => $request->removal_comment ?? '',
            ];
        }

        $advertisement->update([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'main_characteristics' => $request->main_characteristics,
            'complectation' => $request->complectation,
            'technical_characteristics' => $request->technical_characteristics,
            'additional_info' => $request->additional_info,
            'check_data' => $checkData,
            'loading_data' => $loadingData,
            'removal_data' => $removalData,
        ]);

        // Обработка загруженных медиафайлов
        if ($request->hasFile('media_files')) {
            $this->handleUploadedMedia($request->file('media_files'), $advertisement);
        }

        // Обновляем цену покупки и комментарий по оплате в товаре
        if ($request->purchase_price !== null || $request->payment_comment !== null) {
            $advertisement->product->update([
                'purchase_price' => $request->purchase_price,
                'payment_comment' => $request->payment_comment,
            ]);
        }

        // Обновляем варианты оплаты в товаре
        if ($request->has('payment_types')) {
            // Удаляем старые варианты
            $advertisement->product->paymentVariants()->delete();
            
            // Создаем новые варианты
            if (is_array($request->payment_types)) {
                foreach ($request->payment_types as $priceTypeId) {
                    ProductPaymentVariants::create([
                        'product_id' => $advertisement->product->id,
                        'price_type' => $priceTypeId,
                    ]);
                }
            }
        }

        return redirect()->route('advertisements.show', $advertisement)
            ->with('success', 'Объявление успешно обновлено!');
    }

    public function destroy(Advertisement $advertisement)
    {
        $advertisement->delete();

        return redirect()->route('advertisements.index')
            ->with('success', 'Объявление успешно удалено!');
    }

    public function publish(Advertisement $advertisement)
    {
        $advertisement->publish();

        return redirect()->route('advertisements.show', $advertisement)
            ->with('success', 'Объявление опубликовано!');
    }

    public function copyFromProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::with([
            'check.checkStatus',
            'loading.installStatus',
            'removal.installStatus',
            'paymentVariants.priceType'
        ])->findOrFail($request->product_id);

        // Проверяем, что товар принадлежит авторизованному пользователю
        if ($product->owner_id !== auth()->id()) {
            abort(403, 'Вы можете копировать данные только из своих товаров.');
        }

        // Подготавливаем данные для JSON полей
        $checkData = null;
        $check = $product->check->first();
        if ($check) {
            $checkData = [
                'status_id' => $check->check_status_id,
                'comment' => $check->comment,
            ];
        }

        $loadingData = null;
        $loading = $product->loading->first();
        if ($loading) {
            $loadingData = [
                'status_id' => $loading->install_status_id,
                'comment' => $loading->comment,
            ];
        }

        $removalData = null;
        $removal = $product->removal->first();
        if ($removal) {
            $removalData = [
                'status_id' => $removal->install_status_id,
                'comment' => $removal->comment,
            ];
        }

        return response()->json([
            'main_characteristics' => $product->main_chars,
            'complectation' => $product->complectation,
            'category_id' => $product->category_id,
            'check_data' => $checkData,
            'loading_data' => $loadingData,
            'removal_data' => $removalData,
            'payment_data' => [
                'types' => $product->paymentVariants->pluck('price_type')->toArray(),
                'purchase_price' => $product->purchase_price,
                'payment_comment' => $product->payment_comment,
            ],
        ]);
    }

    public function getProductMedia(Product $product)
    {
        // Проверяем, что товар принадлежит авторизованному пользователю
        if ($product->owner_id !== auth()->id()) {
            abort(403, 'Вы можете получать медиафайлы только своих товаров.');
        }

        $media = $product->mediaOrdered->map(function ($mediaItem) {
            return [
                'id' => $mediaItem->id,
                'file_name' => $mediaItem->file_name,
                'file_type' => $mediaItem->file_type,
                'file_size' => $mediaItem->file_size,
                'full_url' => asset('storage/' . $mediaItem->file_path),
                'formatted_size' => $this->formatBytes($mediaItem->file_size),
            ];
        });

        return response()->json($media);
    }

    public function getProductStatuses()
    {
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();
        $priceTypes = ProductPriceType::all();
        
        return response()->json([
            'check_statuses' => $checkStatuses,
            'install_statuses' => $installStatuses,
            'price_types' => $priceTypes
        ]);
    }

    public function deleteMedia(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'media_id' => 'required|exists:advertisement_media,id'
        ]);

        $media = AdvertisementMedia::where('id', $request->media_id)
                                  ->where('advertisement_id', $advertisement->id)
                                  ->first();

        if ($media) {
            // Удаляем файл с диска только если это не медиафайл из товара
            if (!$media->is_selected_from_product && Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }
            
            $media->delete();
            
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function updateComment(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'field' => 'required|string|in:technical_characteristics,additional_info,check_comment,loading_comment,removal_comment',
            'value' => 'nullable|string'
        ]);

        $field = $request->field;
        $value = $request->value;

        try {
            if ($field === 'technical_characteristics') {
                $advertisement->update(['technical_characteristics' => $value]);
            } elseif ($field === 'additional_info') {
                $advertisement->update(['additional_info' => $value]);
            } elseif ($field === 'check_comment') {
                $checkData = $advertisement->check_data ?? [];
                $checkData['comment'] = $value;
                $advertisement->update(['check_data' => $checkData]);
            } elseif ($field === 'loading_comment') {
                $loadingData = $advertisement->loading_data ?? [];
                $loadingData['comment'] = $value;
                $advertisement->update(['loading_data' => $loadingData]);
            } elseif ($field === 'removal_comment') {
                $removalData = $advertisement->removal_data ?? [];
                $removalData['comment'] = $value;
                $advertisement->update(['removal_data' => $removalData]);
            }

            return response()->json(['success' => true, 'message' => 'Данные успешно обновлены']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении данных'], 500);
        }
    }

    public function updatePaymentInfo(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'payment_types' => 'nullable|array',
            'payment_types.*' => 'exists:product_price_types,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'payment_comment' => 'nullable|string'
        ]);

        try {
            // Обновляем данные в связанном товаре
            if ($advertisement->product) {
                $product = $advertisement->product;
                
                // Обновляем варианты оплаты
                if ($request->has('payment_types')) {
                    // Удаляем старые варианты
                    $product->paymentVariants()->delete();
                    
                    // Добавляем новые варианты
                    foreach ($request->payment_types as $typeId) {
                        ProductPaymentVariants::create([
                            'product_id' => $product->id,
                            'price_type_id' => $typeId
                        ]);
                    }
                }

                // Обновляем закупочную цену и комментарий
                $product->update([
                    'purchase_price' => $request->purchase_price,
                    'payment_comment' => $request->payment_comment
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Информация об оплате успешно обновлена']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении информации об оплате'], 500);
        }
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function handleSelectedProductMedia(array $selectedMediaIds, Advertisement $advertisement)
    {
        $sortOrder = 0;

        foreach ($selectedMediaIds as $mediaId) {
            $productMedia = ProductMedia::find($mediaId);
            if ($productMedia) {
                AdvertisementMedia::createFromProductMedia(
                    $advertisement->id,
                    $productMedia,
                    $sortOrder++
                );
            }
        }
    }

    private function handleUploadedMedia($files, Advertisement $advertisement)
    {
        $existingMediaCount = $advertisement->media()->count();
        $sortOrder = $existingMediaCount;

        foreach ($files as $file) {
            if ($file->isValid()) {
                // Определяем тип файла
                $mimeType = $file->getMimeType();
                $fileType = $this->getFileType($mimeType);

                // Генерируем уникальное имя файла
                $fileName = $this->generateUniqueFileName($file);

                // Сохраняем файл в папку advertisements
                $filePath = $file->storeAs('advertisements', $fileName, 'public');

                // Создаем запись в базе данных
                AdvertisementMedia::create([
                    'advertisement_id' => $advertisement->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                    'mime_type' => $mimeType,
                    'file_size' => $file->getSize(),
                    'sort_order' => $sortOrder++,
                    'is_selected_from_product' => false,
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

        return 'adv_' . $timestamp . '_' . $randomString . '.' . $extension;
    }
}

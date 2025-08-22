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
use App\Models\AdvLog;
use App\Models\AdvAction;
use App\Models\LogType;

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
            'mainImage',
            'tags'
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
            'main_info' => 'nullable|string',
            'additional_info' => 'nullable|string',
            'check_status_id' => 'nullable|exists:product_check_statuses,id',
            'check_comment' => 'nullable|string',
            'loading_status_id' => 'nullable|exists:product_install_statuses,id',
            'loading_comment' => 'nullable|string',
            'removal_status_id' => 'nullable|exists:product_install_statuses,id',
            'removal_comment' => 'nullable|string',
            'adv_price' => 'nullable|numeric|min:0',
            'adv_price_comment' => 'nullable|string',
            'main_img' => 'nullable|exists:products_media,id',
            'tags' => 'nullable|string',
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
            'main_info' => $request->main_info,
            'additional_info' => $request->additional_info,
            'check_data' => $checkData,
            'loading_data' => $loadingData,
            'removal_data' => $removalData,
            'adv_price' => $request->adv_price,
            'adv_price_comment' => $request->adv_price_comment,
            'main_img' => $request->main_img,
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



        // Обработка тегов
        if ($request->has('tags') && $request->tags) {
            $tags = json_decode($request->tags, true);
            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    \App\Models\AdvertisementsTags::create([
                        'advertisement_id' => $advertisement->id,
                        'tag' => $tag,
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
            'mediaOrdered',
            'tags',
            'actions' => function($query) {
                $query->where('status', false)
                      ->latest('expired_at')
                      ->limit(1);
            }
        ]);

        // Получаем последний лог объявления
        $lastLog = AdvLog::where('advertisement_id', $advertisement->id)
            ->with(['type', 'user'])
            ->latest()
            ->first();

        // Получаем последнее невыполненное действие объявления
        $lastAction = AdvAction::where('advertisement_id', $advertisement->id)
            ->where('status', false)
            ->latest('expired_at')
            ->first();

        // Загружаем статусы для отображения
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();
        $priceTypes = ProductPriceType::all();

        return view('Advertisement.AdvertisementShowPage', compact('advertisement', 'checkStatuses', 'installStatuses', 'priceTypes', 'lastLog', 'lastAction'));
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
            'main_info' => 'nullable|string',
            'additional_info' => 'nullable|string',
            'check_status_id' => 'nullable|exists:product_check_statuses,id',
            'check_comment' => 'nullable|string',
            'loading_status_id' => 'nullable|exists:product_install_statuses,id',
            'loading_comment' => 'nullable|string',
            'removal_status_id' => 'nullable|exists:product_install_statuses,id',
            'removal_comment' => 'nullable|string',
            'adv_price' => 'nullable|numeric|min:0',
            'adv_price_comment' => 'nullable|string',
            'main_img' => 'nullable|exists:products_media,id',
            'tags' => 'nullable|string',
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
            'main_info' => $request->main_info,
            'additional_info' => $request->additional_info,
            'check_data' => $checkData,
            'loading_data' => $loadingData,
            'removal_data' => $removalData,
            'adv_price' => $request->adv_price,
            'adv_price_comment' => $request->adv_price_comment,
            'main_img' => $request->main_img,
        ]);

        // Обработка загруженных медиафайлов
        if ($request->hasFile('media_files')) {
            $this->handleUploadedMedia($request->file('media_files'), $advertisement);
        }



        // Обработка тегов
        if ($request->has('tags')) {
            // Удаляем старые теги
            $advertisement->tags()->delete();
            
            // Добавляем новые теги
            if ($request->tags) {
                $tags = json_decode($request->tags, true);
                if (is_array($tags)) {
                    foreach ($tags as $tag) {
                        \App\Models\AdvertisementsTags::create([
                            'advertisement_id' => $advertisement->id,
                            'tag' => $tag,
                        ]);
                    }
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
                'adv_price' => $product->purchase_price,
                'adv_price_comment' => $product->payment_comment,
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
            'field' => 'required|string|in:technical_characteristics,main_info,additional_info,check_comment,loading_comment,removal_comment',
            'value' => 'nullable|string'
        ]);

        $field = $request->field;
        $value = $request->value;

        try {
            // Сохраняем старые значения для логирования
            $oldValue = null;
            
            if ($field === 'technical_characteristics') {
                $oldValue = $advertisement->technical_characteristics;
                $advertisement->update(['technical_characteristics' => $value]);
            } elseif ($field === 'main_info') {
                $oldValue = $advertisement->main_info;
                $advertisement->update(['main_info' => $value]);
            } elseif ($field === 'additional_info') {
                $oldValue = $advertisement->additional_info;
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

            // Логируем изменения для технических характеристик, основной информации и дополнительной информации
            if (in_array($field, ['technical_characteristics', 'main_info', 'additional_info'])) {
                $this->logAdvertisementBlockChanges($advertisement, $field, $oldValue, $value);
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
            'adv_price' => 'nullable|numeric|min:0',
            'adv_price_comment' => 'nullable|string',
            'payment_comment' => 'nullable|string'
        ]);

        try {
            // Сохраняем старые значения для логирования
            $oldAdvPrice = $advertisement->adv_price;
            $oldAdvPriceComment = $advertisement->adv_price_comment;
            $oldPurchasePrice = null;
            $oldPaymentComment = null;
            $oldPaymentVariants = [];
            
            if ($advertisement->product) {
                $oldPurchasePrice = $advertisement->product->purchase_price;
                $oldPaymentComment = $advertisement->product->payment_comment;
                $oldPaymentVariants = $advertisement->product->paymentVariants->pluck('price_type')->toArray();
            }
            
            // Нормализуем значения для сравнения
            $oldAdvPriceCommentNormalized = $oldAdvPriceComment ? trim($oldAdvPriceComment) : '';
            $newAdvPriceCommentNormalized = $request->adv_price_comment ? trim($request->adv_price_comment) : '';
            $oldPaymentCommentNormalized = $oldPaymentComment ? trim($oldPaymentComment) : '';
            $newPaymentCommentNormalized = $request->payment_comment ? trim($request->payment_comment) : '';

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

            // Обновляем данные в объявлении
            $advertisement->update([
                'adv_price' => $request->adv_price,
                'adv_price_comment' => $request->adv_price_comment
            ]);

            // Создаем запись в логе от имени системы
            $this->logAdvertisementPaymentChanges($advertisement, $oldAdvPrice, $request->adv_price, $oldAdvPriceCommentNormalized, $newAdvPriceCommentNormalized, $oldPurchasePrice, $request->purchase_price, $oldPaymentCommentNormalized, $newPaymentCommentNormalized, $oldPaymentVariants, $request->payment_types ?? []);

            return response()->json(['success' => true, 'message' => 'Информация об оплате успешно обновлена']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении информации об оплате'], 500);
        }
    }

    public function updateCheckStatus(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'status_id' => 'nullable|exists:product_check_statuses,id',
            'comment' => 'nullable|string|max:1000'
        ]);

        try {
            // Сохраняем старые значения для логирования
            $oldCheckData = $advertisement->check_data ?? [];
            $oldStatusId = $oldCheckData['status_id'] ?? null;
            $oldComment = $oldCheckData['comment'] ?? null;
            
            // Получаем названия статусов
            $oldStatusName = $oldStatusId ? ProductCheckStatuses::find($oldStatusId)->name : null;
            $newStatusName = $request->status_id ? ProductCheckStatuses::find($request->status_id)->name : null;
            
            // Нормализуем значения для сравнения
            $oldCommentNormalized = $oldComment ? trim($oldComment) : '';
            $newCommentNormalized = $request->comment ? trim($request->comment) : '';

            $checkData = [
                'status_id' => $request->status_id,
                'comment' => $request->comment ?? '',
            ];

            $advertisement->update(['check_data' => $checkData]);

            // Создаем запись в логе от имени системы
            $this->logAdvertisementCheckChanges($advertisement, $oldStatusName, $newStatusName, $oldCommentNormalized, $newCommentNormalized);

            return response()->json(['success' => true, 'message' => 'Статус проверки обновлен']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении статуса проверки'], 500);
        }
    }

    public function updateLoadingStatus(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'status_id' => 'nullable|exists:product_install_statuses,id',
            'comment' => 'nullable|string|max:1000'
        ]);

        try {
            // Сохраняем старые значения для логирования
            $oldLoadingData = $advertisement->loading_data ?? [];
            $oldStatusId = $oldLoadingData['status_id'] ?? null;
            $oldComment = $oldLoadingData['comment'] ?? null;
            
            // Получаем названия статусов
            $oldStatusName = $oldStatusId ? ProductInstallStatuses::find($oldStatusId)->name : null;
            $newStatusName = $request->status_id ? ProductInstallStatuses::find($request->status_id)->name : null;
            
            // Нормализуем значения для сравнения
            $oldCommentNormalized = $oldComment ? trim($oldComment) : '';
            $newCommentNormalized = $request->comment ? trim($request->comment) : '';

            $loadingData = [
                'status_id' => $request->status_id,
                'comment' => $request->comment ?? '',
            ];

            $advertisement->update(['loading_data' => $loadingData]);

            // Создаем запись в логе от имени системы
            $this->logAdvertisementLoadingChanges($advertisement, $oldStatusName, $newStatusName, $oldCommentNormalized, $newCommentNormalized);

            return response()->json(['success' => true, 'message' => 'Статус погрузки обновлен']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении статуса погрузки'], 500);
        }
    }

    public function updateRemovalStatus(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'status_id' => 'nullable|exists:product_install_statuses,id',
            'comment' => 'nullable|string|max:1000'
        ]);

        try {
            // Сохраняем старые значения для логирования
            $oldRemovalData = $advertisement->removal_data ?? [];
            $oldStatusId = $oldRemovalData['status_id'] ?? null;
            $oldComment = $oldRemovalData['comment'] ?? null;
            
            // Получаем названия статусов
            $oldStatusName = $oldStatusId ? ProductInstallStatuses::find($oldStatusId)->name : null;
            $newStatusName = $request->status_id ? ProductInstallStatuses::find($request->status_id)->name : null;
            
            // Нормализуем значения для сравнения
            $oldCommentNormalized = $oldComment ? trim($oldComment) : '';
            $newCommentNormalized = $request->comment ? trim($request->comment) : '';

            $removalData = [
                'status_id' => $request->status_id,
                'comment' => $request->comment ?? '',
            ];

            $advertisement->update(['removal_data' => $removalData]);

            // Создаем запись в логе от имени системы
            $this->logAdvertisementRemovalChanges($advertisement, $oldStatusName, $newStatusName, $oldCommentNormalized, $newCommentNormalized);

            return response()->json(['success' => true, 'message' => 'Статус демонтажа обновлен']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении статуса демонтажа'], 500);
        }
    }

    public function updateSaleInfo(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'adv_price' => 'nullable|numeric|min:0',
            'adv_price_comment' => 'nullable|string|max:1000'
        ]);

        try {
            // Сохраняем старые значения для логирования
            $oldAdvPrice = $advertisement->adv_price;
            $oldAdvPriceComment = $advertisement->adv_price_comment;
            
            // Нормализуем значения для сравнения
            $oldAdvPriceCommentNormalized = $oldAdvPriceComment ? trim($oldAdvPriceComment) : '';
            $newAdvPriceCommentNormalized = $request->adv_price_comment ? trim($request->adv_price_comment) : '';

            // Обновляем данные в объявлении
            $advertisement->update([
                'adv_price' => $request->adv_price,
                'adv_price_comment' => $request->adv_price_comment
            ]);

            // Создаем запись в логе от имени системы
            $this->logAdvertisementSaleChanges($advertisement, $oldAdvPrice, $request->adv_price, $oldAdvPriceCommentNormalized, $newAdvPriceCommentNormalized);

            return response()->json(['success' => true, 'message' => 'Информация о продаже обновлена']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении информации о продаже'], 500);
        }
    }

    public function updateCharacteristics(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'main_characteristics' => 'nullable|string|max:5000',
            'complectation' => 'nullable|string|max:5000'
        ]);

        try {
            // Сохраняем старые значения для логирования
            $oldMainCharacteristics = $advertisement->main_characteristics;
            $oldComplectation = $advertisement->complectation;
            
            // Нормализуем значения для сравнения
            $oldMainCharacteristicsNormalized = $oldMainCharacteristics ? trim($oldMainCharacteristics) : '';
            $newMainCharacteristicsNormalized = $request->main_characteristics ? trim($request->main_characteristics) : '';
            $oldComplectationNormalized = $oldComplectation ? trim($oldComplectation) : '';
            $newComplectationNormalized = $request->complectation ? trim($request->complectation) : '';

            // Обновляем данные в объявлении
            $advertisement->update([
                'main_characteristics' => $request->main_characteristics,
                'complectation' => $request->complectation
            ]);

            // Создаем запись в логе от имени системы
            $this->logAdvertisementCharacteristicsChanges($advertisement, $oldMainCharacteristicsNormalized, $newMainCharacteristicsNormalized, $oldComplectationNormalized, $newComplectationNormalized);

            return response()->json(['success' => true, 'message' => 'Характеристики обновлены']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении характеристик'], 500);
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

    /**
     * Получает все логи объявления
     */
    public function getLogs(Advertisement $advertisement)
    {
        $logs = AdvLog::where('advertisement_id', $advertisement->id)
            ->with(['type', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    /**
     * Получает все действия объявления
     */
    public function getActions(Advertisement $advertisement)
    {
        $actions = AdvAction::where('advertisement_id', $advertisement->id)
            ->orderBy('expired_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'actions' => $actions
        ]);
    }

    /**
     * Создает новое действие для объявления
     */
    public function storeAction(Request $request, Advertisement $advertisement)
    {
        // Валидируем запрос
        $validated = $request->validate([
            'action' => 'required|string|max:1000',
            'expired_at' => 'required|date|after:today'
        ]);

        try {
            // Создаем новое действие
            $action = AdvAction::create([
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'action' => $validated['action'],
                'expired_at' => $validated['expired_at'],
                'status' => false,
            ]);

            // Создаем лог о создании действия
            $commentType = LogType::where('name', 'Комментарий')->first();
            
            $logMessage = "Пользователь: " . auth()->user()->name . ", создал новую задачу: \"{$validated['action']}\" со сроком до {$validated['expired_at']}";
            
            $log = AdvLog::create([
                'advertisement_id' => $advertisement->id,
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
    public function completeAction(Request $request, Advertisement $advertisement, $actionId)
    {
        // Валидируем запрос
        $validated = $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        try {
            // Находим действие
            $action = AdvAction::where('id', $actionId)
                ->where('advertisement_id', $advertisement->id)
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
            
            $log = AdvLog::create([
                'advertisement_id' => $advertisement->id,
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
     * Логирует изменения в блоке "Информация о погрузке" для рекламы
     */
    private function logAdvertisementLoadingChanges(Advertisement $advertisement, $oldStatusName, $newStatusName, $oldComment, $newComment)
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
            
            AdvLog::create([
                'advertisement_id' => $advertisement->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }

    /**
     * Логирует изменения в блоке "Информация о демонтаже" для рекламы
     */
    private function logAdvertisementRemovalChanges(Advertisement $advertisement, $oldStatusName, $newStatusName, $oldComment, $newComment)
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
            
            AdvLog::create([
                'advertisement_id' => $advertisement->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }

    /**
     * Логирует изменения в блоке "Информация о покупке" для рекламы
     */
    private function logAdvertisementPaymentChanges(Advertisement $advertisement, $oldAdvPrice, $newAdvPrice, $oldAdvPriceComment, $newAdvPriceComment, $oldPurchasePrice, $newPurchasePrice, $oldPaymentComment, $newPaymentComment, $oldVariants, $newVariants)
    {
        $changes = [];
        
        // Проверяем изменение цены объявления
        if ($oldAdvPrice != $newAdvPrice) {
            $oldAdvPriceText = $oldAdvPrice ? number_format($oldAdvPrice, 0, ',', ' ') . ' ₽' : "'Не указана'";
            $newAdvPriceText = $newAdvPrice ? number_format($newAdvPrice, 0, ',', ' ') . ' ₽' : "'Не указана'";
            $changes[] = "изменил цену объявления с {$oldAdvPriceText} на {$newAdvPriceText}";
        }
        
        // Проверяем изменение комментария к цене объявления
        if ($oldAdvPriceComment !== $newAdvPriceComment) {
            $oldAdvPriceCommentText = $oldAdvPriceComment ? "'{$oldAdvPriceComment}'" : "'Не указан'";
            $newAdvPriceCommentText = $newAdvPriceComment ? "'{$newAdvPriceComment}'" : "'Не указан'";
            $changes[] = "изменил комментарий к цене объявления с {$oldAdvPriceCommentText} на {$newAdvPriceCommentText}";
        }
        
        // Проверяем изменение закупочной цены
        if ($oldPurchasePrice != $newPurchasePrice) {
            $oldPurchasePriceText = $oldPurchasePrice ? number_format($oldPurchasePrice, 0, ',', ' ') . ' ₽' : "'Не указана'";
            $newPurchasePriceText = $newPurchasePrice ? number_format($newPurchasePrice, 0, ',', ' ') . ' ₽' : "'Не указана'";
            $changes[] = "изменил закупочную цену с {$oldPurchasePriceText} на {$newPurchasePriceText}";
        }
        
        // Проверяем изменение комментария к оплате
        if ($oldPaymentComment !== $newPaymentComment) {
            $oldPaymentCommentText = $oldPaymentComment ? "'{$oldPaymentComment}'" : "'Не указан'";
            $newPaymentCommentText = $newPaymentComment ? "'{$newPaymentComment}'" : "'Не указан'";
            $changes[] = "изменил комментарий к оплате с {$oldPaymentCommentText} на {$newPaymentCommentText}";
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
            
            AdvLog::create([
                'advertisement_id' => $advertisement->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }

    /**
     * Логирует изменения в блоке "Информация о проверке" для рекламы
     */
    private function logAdvertisementCheckChanges(Advertisement $advertisement, $oldStatusName, $newStatusName, $oldComment, $newComment)
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
            
            AdvLog::create([
                'advertisement_id' => $advertisement->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }

    /**
     * Логирует изменения в блоке "Информация о продаже" для рекламы
     */
    private function logAdvertisementSaleChanges(Advertisement $advertisement, $oldAdvPrice, $newAdvPrice, $oldAdvPriceComment, $newAdvPriceComment)
    {
        $changes = [];
        
        // Проверяем изменение цены объявления
        if ($oldAdvPrice != $newAdvPrice) {
            $oldAdvPriceText = $oldAdvPrice ? number_format($oldAdvPrice, 0, ',', ' ') . ' ₽' : "'Не указана'";
            $newAdvPriceText = $newAdvPrice ? number_format($newAdvPrice, 0, ',', ' ') . ' ₽' : "'Не указана'";
            $changes[] = "изменил цену продажи с {$oldAdvPriceText} на {$newAdvPriceText}";
        }
        
        // Проверяем изменение комментария к цене продажи
        if ($oldAdvPriceComment !== $newAdvPriceComment) {
            $oldAdvPriceCommentText = $oldAdvPriceComment ? "'{$oldAdvPriceComment}'" : "'Не указан'";
            $newAdvPriceCommentText = $newAdvPriceComment ? "'{$newAdvPriceComment}'" : "'Не указан'";
            $changes[] = "изменил комментарий к цене продажи с {$oldAdvPriceCommentText} на {$newAdvPriceCommentText}";
        }
        
        // Если есть изменения, создаем запись в логе
        if (!empty($changes)) {
            $userName = auth()->user()->name ?? 'Неизвестный пользователь';
            $logMessage = "Пользователь {$userName} изменил блок Информация о продаже, " . implode(', ', $changes);
            
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            AdvLog::create([
                'advertisement_id' => $advertisement->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }

    /**
     * Логирует изменения в блоке "Характеристики" для рекламы
     */
    private function logAdvertisementCharacteristicsChanges(Advertisement $advertisement, $oldMainCharacteristics, $newMainCharacteristics, $oldComplectation, $newComplectation)
    {
        $changes = [];
        
        // Проверяем изменение основных характеристик
        if ($oldMainCharacteristics !== $newMainCharacteristics) {
            $oldMainCharacteristicsText = $oldMainCharacteristics ? "'{$oldMainCharacteristics}'" : "'Не указаны'";
            $newMainCharacteristicsText = $newMainCharacteristics ? "'{$newMainCharacteristics}'" : "'Не указаны'";
            $changes[] = "изменил основные характеристики с {$oldMainCharacteristicsText} на {$newMainCharacteristicsText}";
        }
        
        // Проверяем изменение комплектации
        if ($oldComplectation !== $newComplectation) {
            $oldComplectationText = $oldComplectation ? "'{$oldComplectation}'" : "'Не указана'";
            $newComplectationText = $newComplectation ? "'{$newComplectation}'" : "'Не указана'";
            $changes[] = "изменил комплектацию с {$oldComplectationText} на {$newComplectationText}";
        }
        
        // Если есть изменения, создаем запись в логе
        if (!empty($changes)) {
            $userName = auth()->user()->name ?? 'Неизвестный пользователь';
            $logMessage = "Пользователь {$userName} изменил блок Характеристики, " . implode(', ', $changes);
            
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            AdvLog::create([
                'advertisement_id' => $advertisement->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }

    /**
     * Логирует изменения в блоке "Технические характеристики" или "Основная информация" для рекламы
     */
    private function logAdvertisementBlockChanges(Advertisement $advertisement, $field, $oldValue, $newValue)
    {
        $changes = [];
        
        // Проверяем изменение значения
        if ($oldValue !== $newValue) {
            $oldValueText = $oldValue ? "'{$oldValue}'" : "'Не указан'";
            $newValueText = $newValue ? "'{$newValue}'" : "'Не указан'";
            $changes[] = "изменил значение с {$oldValueText} на {$newValueText}";
        }
        
        // Если есть изменения, создаем запись в логе
        if (!empty($changes)) {
                                     $userName = auth()->user()->name ?? 'Неизвестный пользователь';
            $blockName = match($field) {
                'technical_characteristics' => 'Технические характеристики',
                'main_info' => 'Основная информация',
                'additional_info' => 'Дополнительная информация',
                default => ucfirst($field)
            };
            $logMessage = "Пользователь {$userName} изменил {$blockName}";
            
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            AdvLog::create([
                'advertisement_id' => $advertisement->id,
                'user_id' => null, // От имени системы
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);
        }
    }
}

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
use App\Models\AdvertisementStatus;
use App\Models\Company;
use App\Models\Regions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\AdvLog;
use App\Models\AdvAction;
use App\Models\LogType;

class AdvertisementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $currentUserId = auth()->id() ?? 1;

        $advertisementsQuery = Advertisement::with([
            'category',
            'product.company.addresses',
            'product.warehouse',
            'product.regional.role',
            'product.status',
            'status',
            'creator',
            'mainImage',
            'tags',
            'productState',
            'productAvailable'
        ]);

        // Применяем фильтры по правам доступа (базовая фильтрация)
        if ($user && $user->role) {
            if ($user->role->name === 'Региональный представитель') {
                // Для регионального представителя показываем объявления товаров, где он назначен как региональный представитель
                $advertisementsQuery->whereHas('product', function($query) use ($currentUserId) {
                    $query->where('regional_id', $currentUserId);
                });
            } elseif ($user->role->can_view_products === 1) {

            } elseif ($user->role->can_view_products === 3) {
                // Для администраторов показываем все объявления
                // Ничего не добавляем к запросу
            } else {
                // Для остальных ролей показываем только их объявления
                $advertisementsQuery->whereHas('product', function($query) use ($currentUserId) {
                    $query->where('owner_id', $currentUserId);
                });
            }
        } else {
            // Если пользователь не авторизован или нет роли, показываем только его объявления
            $advertisementsQuery->whereHas('product', function($query) use ($currentUserId) {
                $query->where('owner_id', $currentUserId);
            });
        }

        // Фильтр по типу объявлений (Свои/Все) - применяется поверх прав доступа
        if ($request->filled('advertisement_type')) {
            if ($request->advertisement_type === 'own') {
                // Показываем только объявления, созданные текущим пользователем
                $advertisementsQuery->where('created_by', $currentUserId);
            }
            // Если 'all' - показываем все объявления в рамках прав доступа (уже применено выше)
        } else {
            // По умолчанию показываем только свои объявления
            $advertisementsQuery->where('created_by', $currentUserId);
        }

        // Применяем фильтры из запроса
        if ($request->filled('category_id')) {
            $advertisementsQuery->where('category_id', $request->category_id);
        }

        if ($request->filled('company_id')) {
            $advertisementsQuery->whereHas('product.company', function($query) use ($request) {
                $query->where('id', $request->company_id);
            });
        }

        if ($request->filled('status_id')) {
            $advertisementsQuery->where('status_id', $request->status_id);
        }

        if ($request->filled('region_id')) {
            $advertisementsQuery->whereHas('product.warehouse.regions', function($query) use ($request) {
                $query->where('regions.id', $request->region_id);
            });
        }

        if ($request->filled('product_state_id')) {
            $advertisementsQuery->where('product_state', $request->product_state_id);
        }

        if ($request->filled('product_available_id')) {
            $advertisementsQuery->where('product_available', $request->product_available_id);
        }

        // Фильтр по ответственному (только для администраторов)
        if ($request->filled('responsible_id') && $user && $user->role && $user->role->name === 'Администратор') {
            $advertisementsQuery->where('created_by', $request->responsible_id);
        }

        // Поиск по названию объявления и артикулу товара
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $advertisementsQuery->where(function($query) use ($searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('product', function($subQuery) use ($searchTerm) {
                          $subQuery->where('sku', 'like', '%' . $searchTerm . '%');
                      });
            });
        }

        $advertisements = $advertisementsQuery->orderBy('id', 'desc')->paginate(20)->withQueryString();

        // Получаем данные для фильтров с учетом прав доступа
        $filterData = $this->getFilterData($user, $currentUserId);

        return view('Advertisement.AdvertisementListPage', compact('advertisements', 'filterData'));
    }

    /**
     * Получает данные для фильтров с учетом прав доступа пользователя
     */
    private function getFilterData($user, $currentUserId)
    {
        $filterData = [];

        // Категории - доступны всем
        $filterData['categories'] = \App\Models\ProductCategories::where('active', true)->get();

        // Статусы объявлений - показываем все статусы
        $filterData['statuses'] = \App\Models\AdvertisementStatus::all();

        // Поставщики (компании) - с учетом прав доступа
        $companiesQuery = \App\Models\Company::with('status');

        if ($user && $user->role) {
            if ($user->role->name === 'Региональный представитель') {
                // Для регионального представителя показываем компании, где он назначен как региональный представитель
                $companiesQuery->where('regional_user_id', $currentUserId);
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

        // Регионы - показываем все активные регионы
        $filterData['regions'] = \App\Models\Regions::where('active', true)->get();

        // Состояния товаров - показываем все состояния
        $filterData['productStates'] = \App\Models\ProductState::all();

        // Доступности товаров - показываем все доступности
        $filterData['productAvailables'] = \App\Models\ProductAvailable::all();

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

        // Получаем товары со статусом "В продаже" и has_advertise=false
        $productsQuery = Product::with('category', 'company', 'status')
            ->where('owner_id', auth()->id())
            ->where('has_advertise', false);

        // Находим статус "В продаже"
        $inSaleStatus = ProductStatus::where('name', 'В продаже')->first();
        
        if ($inSaleStatus) {
            $productsQuery->where('status_id', $inSaleStatus->id);
        } else {
            // Если статус "В продаже" не найден, возвращаем пустой результат
            $productsQuery->where('status_id', 0);
        }

        $products = $productsQuery->get();

        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();
        $priceTypes = ProductPriceType::all();
        $productStates = \App\Models\ProductState::all();
        $productAvailables = \App\Models\ProductAvailable::all();

        return view('Advertisement.AdvertisementCreatePage', compact(
            'categories',
            'products',
            'product',
            'checkStatuses',
            'installStatuses',
            'priceTypes',
            'productStates',
            'productAvailables'
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
            'adv_price_comment' => 'required|string',
            'show_price' => 'nullable|boolean',
            'product_state' => 'nullable|exists:product_states,id',
            'product_available' => 'nullable|exists:product_availables,id',
            'main_img' => 'nullable|integer|exists:products_media,id',
            'tags' => 'nullable|string',
            'selected_product_media.*' => 'nullable|exists:products_media,id',
            'media_files.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,mov,avi|max:51200',
        ]);

        $product = Product::with('status')->findOrFail($request->product_id);

        // Проверяем, что товар принадлежит авторизованному пользователю
        if ($product->owner_id !== auth()->id()) {
            abort(403, 'Вы можете создавать объявления только для своих товаров.');
        }

        // Проверяем, что товар находится в статусе "В продаже" и has_advertise=false
        if (!$product->status || $product->status->name !== 'В продаже') {
            abort(403, 'Можно создавать объявления только для товаров со статусом "В продаже".');
        }
        
        if ($product->has_advertise) {
            abort(403, 'Для этого товара уже создано объявление.');
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
            'show_price' => (bool)$request->show_price,
            'product_state' => $request->product_state,
            'product_available' => $request->product_available,
            'main_img' => $request->main_img,
            'status_id' => AdvertisementStatus::where('name', 'В продаже')->first()->id,
            'created_by' => auth()->id() ?? 1, // Временно используем id=1
        ]);

        // Устанавливаем has_advertise=true для товара
        $product->update(['has_advertise' => true]);

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

        // Создаем лог о создании объявления
        $this->logAdvertisementCreation($advertisement);

        return redirect()->route('advertisements.index')->with('success', 'Объявление успешно создано!');
    }

    public function show(Advertisement $advertisement)
    {
        $advertisement->load([
            'category',
            'product.company.addresses',
            'product.paymentVariants.priceType',
            'product.status',
            'status',
            'creator',
            'mediaOrdered',
            'tags',
            'actions' => function($query) {
                $query->where('status', false)
                      ->orderBy('expired_at', 'asc');
            }
        ]);

        // Получаем последний лог объявления
        $lastLog = AdvLog::where('advertisement_id', $advertisement->id)
            ->with(['type', 'user'])
            ->latest()
            ->first();

        // Получаем первое невыполненное действие объявления (сортировка по сроку выполнения ASC)
        $lastAction = AdvAction::where('advertisement_id', $advertisement->id)
            ->where('status', false)
            ->orderBy('expired_at', 'asc')
            ->first();

        // Загружаем статусы для отображения
        $checkStatuses = ProductCheckStatuses::all();
        $installStatuses = ProductInstallStatuses::all();
        $priceTypes = ProductPriceType::all();
        $advertisementStatuses = AdvertisementStatus::all();

        return view('Advertisement.AdvertisementShowPage', compact('advertisement', 'checkStatuses', 'installStatuses', 'priceTypes', 'advertisementStatuses', 'lastLog', 'lastAction'));
    }

    public function edit(Advertisement $advertisement)
    {
        $advertisement->load([
            'product.mediaOrdered',
            'product.check.checkStatus',
            'product.loading.installStatus',
            'product.removal.installStatus',
            'product.paymentVariants.priceType',
            'status',
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
            'show_price' => 'nullable|boolean',
            'main_img' => 'nullable|integer|exists:products_media,id',
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
            'show_price' => (bool)$request->show_price,
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
        $product = $advertisement->product;
        
        $advertisement->delete();

        // Обновляем has_advertise для товара
        $this->updateProductHasAdvertise($product);

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
            'product_state' => $product->state_id,
            'product_available' => $product->available_id,
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

    public function uploadMedia(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'media_files.*' => 'required|file|mimes:jpeg,png,gif,mp4,mov,avi|max:51200',
        ]);

        try {
            $uploadedFiles = [];

            if ($request->hasFile('media_files')) {
                $existingMediaCount = $advertisement->media()->count();
                $sortOrder = $existingMediaCount;

                foreach ($request->file('media_files') as $file) {
                    if ($file->isValid()) {
                        // Определяем тип файла
                        $mimeType = $file->getMimeType();
                        $fileType = $this->getFileType($mimeType);

                        // Генерируем уникальное имя файла
                        $fileName = $this->generateUniqueFileName($file);

                        // Сохраняем файл в папку advertisements
                        $filePath = $file->storeAs('advertisements', $fileName, 'public');

                        // Создаем запись в базе данных
                        $media = AdvertisementMedia::create([
                            'advertisement_id' => $advertisement->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $filePath,
                            'file_type' => $fileType,
                            'mime_type' => $mimeType,
                            'file_size' => $file->getSize(),
                            'sort_order' => $sortOrder++,
                            'is_selected_from_product' => false,
                        ]);

                        $uploadedFiles[] = $media;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Медиафайлы успешно загружены',
                'files' => $uploadedFiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке медиафайлов: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteMediaById(Advertisement $advertisement, $mediaId)
    {
        $media = AdvertisementMedia::where('id', $mediaId)
                                  ->where('advertisement_id', $advertisement->id)
                                  ->first();

        if (!$media) {
            return response()->json(['success' => false, 'message' => 'Медиафайл не найден'], 404);
        }

        try {
            // Удаляем файл с диска только если это не медиафайл из товара
            if (!$media->is_selected_from_product && Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }

            $media->delete();

            return response()->json(['success' => true, 'message' => 'Медиафайл успешно удален']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении медиафайла: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateMainImage(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'media_id' => 'required|exists:advertisement_media,id'
        ]);

        try {
            $media = AdvertisementMedia::where('id', $request->media_id)
                                      ->where('advertisement_id', $advertisement->id)
                                      ->first();

            if (!$media) {
                return response()->json(['success' => false, 'message' => 'Медиафайл не найден'], 404);
            }

            // Проверяем, что это изображение
            if ($media->file_type !== 'image') {
                return response()->json(['success' => false, 'message' => 'Главным изображением может быть только изображение'], 400);
            }

            // Обновляем главное изображение
            $advertisement->update(['main_img' => $media->id]);

            return response()->json([
                'success' => true,
                'message' => 'Главное изображение успешно обновлено',
                'main_image_url' => asset('storage/' . $media->file_path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении главного изображения: ' . $e->getMessage()
            ], 500);
        }
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
            'adv_price_comment' => 'nullable|string|max:1000',
            'show_price' => 'nullable|boolean'
        ]);

        try {
            // Сохраняем старые значения для логирования
            $oldAdvPrice = $advertisement->adv_price;
            $oldAdvPriceComment = $advertisement->adv_price_comment;
            $oldShowPrice = $advertisement->show_price;

            // Нормализуем значения для сравнения
            $oldAdvPriceCommentNormalized = $oldAdvPriceComment ? trim($oldAdvPriceComment) : '';
            $newAdvPriceCommentNormalized = $request->adv_price_comment ? trim($request->adv_price_comment) : '';

            // Обновляем данные в объявлении
            $advertisement->update([
                'adv_price' => $request->adv_price,
                'adv_price_comment' => $request->adv_price_comment,
                'show_price' => (bool)$request->show_price
            ]);

            // Создаем запись в логе от имени системы
            $this->logAdvertisementSaleChanges($advertisement, $oldAdvPrice, $request->adv_price, $oldAdvPriceCommentNormalized, $newAdvPriceCommentNormalized);

            return response()->json([
                'success' => true, 
                'message' => 'Информация о продаже обновлена',
                'data' => [
                    'adv_price' => $advertisement->adv_price,
                    'adv_price_comment' => $advertisement->adv_price_comment,
                    'show_price' => $advertisement->show_price
                ]
            ]);
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

    public function updateTitle(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'title' => 'required|string|max:255|min:1'
        ]);

        try {
            // Сохраняем старое значение для логирования
            $oldTitle = $advertisement->title;
            $newTitle = trim($request->title);

            // Проверяем, что заголовок действительно изменился
            if ($oldTitle === $newTitle) {
                return response()->json(['success' => true, 'message' => 'Заголовок не изменился']);
            }

            // Обновляем заголовок объявления
            $advertisement->update([
                'title' => $newTitle
            ]);

            // Создаем запись в логе от имени системы
            $this->logAdvertisementTitleChanges($advertisement, $oldTitle, $newTitle);

            return response()->json([
                'success' => true, 
                'message' => 'Заголовок успешно обновлен',
                'new_title' => $newTitle
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении заголовка'], 500);
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
     * Логирует изменения заголовка объявления
     */
    private function logAdvertisementTitleChanges(Advertisement $advertisement, $oldTitle, $newTitle)
    {
        $userName = auth()->user()->name ?? 'Неизвестный пользователь';
        $logMessage = "Пользователь {$userName} изменил заголовок объявления с '{$oldTitle}' на '{$newTitle}'";

        $systemLogType = LogType::where('name', 'Системный')->first();

        AdvLog::create([
            'advertisement_id' => $advertisement->id,
            'user_id' => null, // От имени системы
            'log' => $logMessage,
            'type_id' => $systemLogType ? $systemLogType->id : null
        ]);
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

    /**
     * Обновляет статус объявления
     */
    public function updateStatus(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'status_id' => 'required|exists:advertisement_statuses,id',
            'comment' => 'required|string|min:1'
        ]);

        $oldStatus = $advertisement->status;
        $newStatus = AdvertisementStatus::find($request->status_id);

        // Проверяем, что объявление связано с товаром
        if (!$advertisement->product) {
            return response()->json([
                'success' => false,
                'message' => 'Объявление не связано с товаром'
            ], 400);
        }

        // Проверяем статус товара при переводе объявления в статусы: Ревизия, Активное, Резерв
        $restrictedStatuses = ['Ревизия', 'Активное', 'Резерв'];
        if (in_array($newStatus->name, $restrictedStatuses)) {
            $product = $advertisement->product;
            $productStatus = $product->status;

            if ($productStatus && in_array($productStatus->name, ['Холд', 'Отказ'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Нельзя перевести объявление в статус '{$newStatus->name}', так как связанный товар находится в статусе '{$productStatus->name}'. Сначала переведите товар из статуса '{$productStatus->name}'.",
                    'product_status' => $productStatus->name,
                    'product_id' => $product->id
                ], 400);
            }
        }

        // Обновляем статус объявления
        $advertisement->update([
            'status_id' => $request->status_id
        ]);

        // Создаем запись в логе
        $userName = auth()->user()->name ?? 'Неизвестный пользователь';
        $oldStatusName = $oldStatus && $oldStatus instanceof \App\Models\AdvertisementStatus ? $oldStatus->name : 'Не указан';
        $newStatusName = $newStatus->name;

        $logMessage = "Пользователь {$userName} изменил статус объявления с '{$oldStatusName}' на '{$newStatusName}'. Комментарий: {$request->comment}";

        $systemLogType = LogType::where('name', 'Системный')->first();

        $log = AdvLog::create([
            'advertisement_id' => $advertisement->id,
            'user_id' => auth()->id(),
            'log' => $logMessage,
            'type_id' => $systemLogType ? $systemLogType->id : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Статус объявления успешно обновлен',
            'log' => $log->load(['type', 'user'])
        ]);
    }

    /**
     * Логирует создание объявления
     */
    private function logAdvertisementCreation(Advertisement $advertisement)
    {
        $userName = auth()->user()->name ?? 'Неизвестный пользователь';
        $logMessage = "Пользователь {$userName} создал объявление {$advertisement->title}";

        $systemLogType = LogType::where('name', 'Системный')->first();

        AdvLog::create([
            'advertisement_id' => $advertisement->id,
            'user_id' => auth()->id(),
            'log' => $logMessage,
            'type_id' => $systemLogType ? $systemLogType->id : null
        ]);
    }

    /**
     * Активирует объявление (переводит из статуса "Ревизия" в "В продаже")
     */
    public function activate(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'comment' => 'nullable|string|max:1000'
        ]);

        try {
            // Проверяем, что объявление находится в статусе "Ревизия"
            if (!$advertisement->status || $advertisement->status->name !== 'Ревизия') {
                return response()->json([
                    'success' => false,
                    'message' => 'Можно активировать только объявления в статусе "Ревизия"'
                ], 400);
            }

            // Проверяем, что объявление связано с товаром
            if (!$advertisement->product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Объявление не связано с товаром'
                ], 400);
            }

            // Проверяем статус товара
            $product = $advertisement->product;
            $productStatus = $product->status;

            if ($productStatus && in_array($productStatus->name, ['Холд', 'Отказ'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Нельзя активировать объявление, так как связанный товар находится в статусе '{$productStatus->name}'. Сначала переведите товар из статуса '{$productStatus->name}'.",
                    'product_status' => $productStatus->name,
                    'product_id' => $product->id
                ], 400);
            }

            // Получаем статус "В продаже"
            $activeStatus = AdvertisementStatus::where('name', 'В продаже')->first();
            if (!$activeStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Статус "В продаже" не найден в системе'
                ], 500);
            }

            // Сохраняем старый статус для логирования
            $oldStatusName = $advertisement->status ? $advertisement->status->name : 'Не указан';

            // Обновляем статус объявления
            $advertisement->update([
                'status_id' => $activeStatus->id
            ]);

            // Создаем запись в логе
            $userName = auth()->user()->name ?? 'Неизвестный пользователь';
            $comment = $request->comment ? " Комментарий: {$request->comment}" : '';
            $logMessage = "Пользователь {$userName} активировал объявление, переведя его из статуса '{$oldStatusName}' в статус '{$activeStatus->name}'.{$comment}";

            $systemLogType = LogType::where('name', 'Системный')->first();

            $log = AdvLog::create([
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'log' => $logMessage,
                'type_id' => $systemLogType ? $systemLogType->id : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Объявление успешно активировано',
                'status_name' => $activeStatus->name,
                'status_color' => $activeStatus->color,
                'log' => $log->load(['type', 'user'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при активации объявления: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Advertisement;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AdvertisementMedia;
use App\Models\Product;
use App\Models\ProductCategories;
use App\Models\ProductMedia;
use App\Models\ProductStatus;
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
                'mediaOrdered'
            ])->findOrFail($productId);
        }

        $categories = ProductCategories::all();
        $products = Product::with('category', 'company')->get();
        $productStatuses = ProductStatus::active()->get();

        return view('Advertisement.AdvertisementCreatePage', compact('categories', 'products', 'product', 'productStatuses'));
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
            'check_status_id' => 'nullable|exists:product_statuses,id',
            'check_comment' => 'nullable|string',
            'loading_type' => 'nullable|string',
            'loading_comment' => 'nullable|string',
            'removal_type' => 'nullable|string',
            'removal_comment' => 'nullable|string',
            'selected_product_media.*' => 'nullable|exists:products_media,id',
            'media_files.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,mov,avi|max:51200',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Создаем объявление
        $advertisement = Advertisement::create([
            'product_id' => $request->product_id,
            'title' => $request->title,
            'category_id' => $request->category_id,
            'main_characteristics' => $request->main_characteristics,
            'complectation' => $request->complectation,
            'technical_characteristics' => $request->technical_characteristics,
            'additional_info' => $request->additional_info,
            'check_data' => [
                'status_id' => $request->check_status_id,
                'status_comment' => $request->check_comment,
            ],
            'loading_data' => [
                'loading_type' => $request->loading_type,
                'loading_comment' => $request->loading_comment,
            ],
            'removal_data' => [
                'removal_type' => $request->removal_type,
                'removal_comment' => $request->removal_comment,
            ],
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

        return redirect()->route('advertisements.index')->with('success', 'Объявление успешно создано!');
    }

    public function show(Advertisement $advertisement)
    {
        $advertisement->load([
            'category',
            'product.company.addresses',
            'creator',
            'mediaOrdered'
        ]);

        return view('Advertisement.AdvertisementShowPage', compact('advertisement'));
    }

    public function edit(Advertisement $advertisement)
    {
        $advertisement->load(['product.mediaOrdered', 'mediaOrdered']);
        $categories = ProductCategories::all();
        $products = Product::with('category', 'company')->get();
        $productStatuses = ProductStatus::active()->get();

        return view('Advertisement.AdvertisementEditPage', compact('advertisement', 'categories', 'products', 'productStatuses'));
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
            'check_status_id' => 'nullable|exists:product_statuses,id',
            'check_comment' => 'nullable|string',
            'loading_type' => 'nullable|string',
            'loading_comment' => 'nullable|string',
            'removal_type' => 'nullable|string',
            'removal_comment' => 'nullable|string',
            'media_files.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,mov,avi|max:51200',
        ]);

        $advertisement->update([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'main_characteristics' => $request->main_characteristics,
            'complectation' => $request->complectation,
            'technical_characteristics' => $request->technical_characteristics,
            'additional_info' => $request->additional_info,
            'check_data' => [
                'status_id' => $request->check_status_id,
                'status_comment' => $request->check_comment,
            ],
            'loading_data' => [
                'loading_type' => $request->loading_type,
                'loading_comment' => $request->loading_comment,
            ],
            'removal_data' => [
                'removal_type' => $request->removal_type,
                'removal_comment' => $request->removal_comment,
            ],
        ]);

        // Обработка загруженных медиафайлов
        if ($request->hasFile('media_files')) {
            $this->handleUploadedMedia($request->file('media_files'), $advertisement);
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

        $product = Product::with('status')->findOrFail($request->product_id);

        return response()->json([
            'main_characteristics' => $product->main_chars,
            'complectation' => $product->complectation,
            'category_id' => $product->category_id,
            'check_data' => [
                'status_id' => $product->status_id,
                'status_comment' => $product->status_comment,
            ],
            'loading_data' => [
                'loading_type' => $product->loading_type,
                'loading_comment' => $product->loading_comment,
            ],
            'removal_data' => [
                'removal_type' => $product->removal_type,
                'removal_comment' => $product->removal_comment,
            ],
        ]);
    }

    public function getProductMedia(Product $product)
    {
        $media = $product->mediaOrdered()->get()->map(function ($mediaItem) {
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
        $statuses = ProductStatus::where('active', true)->get();
        return response()->json($statuses);
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

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
            'mainImage'
        ])->get();

        return view('Product.ProductPage', compact('products'));
    }

    public function create()
    {
        $warehouses = Warehouses::where('active', true)->get();
        $companies = Company::with('status')->get();
        $categories = ProductCategories::all();
        $statuses = ProductStatus::where('active', true)->get();

        return view('Product.ProductCreatePage', compact('warehouses', 'companies', 'categories', 'statuses'));
    }

    public function store(Request $request)
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
        ]);

        // Генерируем SKU для товара
        $sku = $this->generateSku();

        // Создаем товар
        $product = Product::create([
            'name' => $request->name,
            'sku' => $sku,
            'warehouse_id' => $request->warehouse_id,
            'company_id' => $request->company_id,
            'category_id' => $request->category_id,
            'owner_id' => auth()->id() ?? 1, // Временно используем id=1, пока нет аутентификации
            'regional_id' => auth()->id() ?? 1, // Временно используем id=1, пока нет аутентификации
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
            'price_comment' => '', // Временно пустое значение
            'add_expenses' => 0, // Временно 0
        ]);

        // Обработка загрузки медиафайлов
        if ($request->hasFile('media_files')) {
            $this->handleMediaFiles($request->file('media_files'), $product);
        }

        return redirect()->route('products.index')->with('success', 'Товар успешно создан!');
    }

    private function generateSku()
    {
        do {
            $sku = 'PRD-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
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
}

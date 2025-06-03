<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use App\Models\ProductCategories;
use Illuminate\Http\Request;

class GuidesCategories extends Controller
{
    private function buildTree($categories, $parentId = 0, $level = 0)
    {
        $result = [];
        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $category->level = $level;
                $result[] = $category;
                $children = $this->buildTree($categories, $category->id, $level + 1);
                $result = array_merge($result, $children);
            }
        }
        return $result;
    }

    public function index()
    {
        $categories = ProductCategories::all();
        $tree = $this->buildTree($categories);
        return view('Guides.GuidesCategoriesPage', compact('categories', 'tree'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|integer|min:0'
        ]);

        ProductCategories::create($request->all());

        return redirect()->back()->with('success', 'Категория успешно добавлена');
    }

    public function destroy(ProductCategories $category)
    {
        // Проверяем, есть ли дочерние категории
        if (ProductCategories::where('parent_id', $category->id)->exists()) {
            return redirect()->back()->with('error', 'Нельзя удалить категорию, содержащую подкатегории');
        }

        $category->delete();
        return redirect()->back()->with('success', 'Категория успешно удалена');
    }
}

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
        $activeCategories = ProductCategories::where('active', true)->get();
        $tree = $this->buildTree($categories);
        return view('Guides.GuidesCategoriesPage', compact('categories', 'activeCategories', 'tree'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|integer|min:0',
            'active' => 'required|boolean'
        ]);

        ProductCategories::create($request->all());

        return redirect()->back()->with('success', 'Категория успешно добавлена');
    }

    public function edit(ProductCategories $category)
    {
        $parent = null;
        if ($category->parent_id > 0) {
            $parent = ProductCategories::find($category->parent_id);
        }
        
        $activeCategories = ProductCategories::where('active', true)->get();
        
        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'parent_id' => $category->parent_id,
            'parent_name' => $parent ? $parent->name : null,
            'active' => $category->active,
            'activeCategories' => $activeCategories
        ]);
    }

    public function update(Request $request, ProductCategories $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|integer|min:0',
            'active' => 'required|boolean'
        ]);

        // Проверяем, что категория не пытается стать родителем самой себя
        if ($request->parent_id == $category->id) {
            return redirect()->back()->with('error', 'Категория не может быть родителем самой себя');
        }

        // Проверяем, что родительская категория не является дочерней
        if ($request->parent_id > 0) {
            $parent = ProductCategories::find($request->parent_id);
            if (!$parent) {
                return redirect()->back()->with('error', 'Родительская категория не найдена');
            }
            
            // Проверяем, что новая родительская категория не является дочерней текущей категории
            $children = $this->getAllChildren($category->id);
            if (in_array($request->parent_id, $children)) {
                return redirect()->back()->with('error', 'Нельзя установить дочернюю категорию как родительскую');
            }
        }

        $category->update($request->all());

        return redirect()->back()->with('success', 'Категория успешно обновлена');
    }

    private function getAllChildren($categoryId)
    {
        $children = [];
        $directChildren = ProductCategories::where('parent_id', $categoryId)->get();
        
        foreach ($directChildren as $child) {
            $children[] = $child->id;
            $children = array_merge($children, $this->getAllChildren($child->id));
        }
        
        return $children;
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

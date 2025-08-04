<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use App\Models\Sources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuidesSources extends Controller
{
    public function index()
    {
        $sources = Sources::all();
        return view('Guides.GuidesSourcesPage', compact('sources'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'active' => 'required|boolean'
            ]);

            // Преобразуем строковое значение в boolean
            $validated['active'] = (bool) $validated['active'];

            $source = Sources::create($validated);

            if (!$source) {
                Log::error('Не удалось создать источник', ['data' => $validated]);
                return redirect()->back()->with('error', 'Произошла ошибка при создании источника');
            }

            return redirect()->back()->with('success', 'Источник успешно добавлен');
        } catch (\Exception $e) {
            Log::error('Ошибка при создании источника: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при создании источника: ' . $e->getMessage());
        }
    }

    public function edit(Sources $source)
    {
        return response()->json($source);
    }

    public function update(Request $request, Sources $source)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'active' => 'required|boolean'
            ]);

            // Преобразуем строковое значение в boolean
            $validated['active'] = (bool) $validated['active'];

            $source->update($validated);

            return redirect()->back()->with('success', 'Источник успешно обновлен');
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении источника: ' . $e->getMessage(), [
                'source_id' => $source->id,
                'request' => $request->all(),
                'exception' => $e
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при обновлении источника: ' . $e->getMessage());
        }
    }

    public function destroy(Sources $source)
    {
        try {
            $source->delete();
            return redirect()->back()->with('success', 'Источник успешно удален');
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении источника: ' . $e->getMessage(), [
                'source_id' => $source->id,
                'exception' => $e
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при удалении источника');
        }
    }
}

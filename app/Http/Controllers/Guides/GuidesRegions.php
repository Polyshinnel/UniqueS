<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use App\Models\Regions;
use Illuminate\Http\Request;

class GuidesRegions extends Controller
{
    public function index()
    {
        $regions = Regions::all();
        return view('Guides.GuidesRegionsPage', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city_name' => 'required|string|max:255',
            'active' => 'required|boolean'
        ]);

        Regions::create($validated);

        return redirect()->route('regions.index')->with('success', 'Регион успешно добавлен');
    }

    public function edit(Regions $region)
    {
        return response()->json($region);
    }

    public function update(Request $request, Regions $region)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city_name' => 'required|string|max:255',
            'active' => 'required|boolean'
        ]);

        $region->update($validated);

        return redirect()->route('regions.index')->with('success', 'Регион успешно обновлен');
    }


}

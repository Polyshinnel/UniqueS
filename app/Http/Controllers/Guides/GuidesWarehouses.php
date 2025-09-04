<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use App\Models\Regions;
use App\Models\Warehouses;
use App\Models\WarehousesToRegions;
use Illuminate\Http\Request;

class GuidesWarehouses extends Controller
{
    public function index()
    {
        $warehouses = Warehouses::with('regions')->get();
        $regions = Regions::all();
        return view('Guides.GuidesWarehousePage', compact('warehouses', 'regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'regions' => 'required|array|min:1',
            'regions.*' => 'exists:regions,id',
            'active' => 'required|boolean'
        ]);

        $warehouse = Warehouses::create([
            'name' => $request->name,
            'active' => $request->active
        ]);

        // Создаем связи с регионами
        foreach ($request->regions as $regionId) {
            WarehousesToRegions::create([
                'warehouse_id' => $warehouse->id,
                'region_id' => $regionId
            ]);
        }

        return redirect()->back()->with('success', 'Склад успешно создан');
    }

    public function edit(Warehouses $warehouse)
    {
        $warehouse->load('regions');
        
        return response()->json([
            'id' => $warehouse->id,
            'name' => $warehouse->name,
            'active' => $warehouse->active,
            'regions' => $warehouse->regions->map(function($region) {
                return [
                    'id' => $region->id,
                    'name' => $region->name
                ];
            })
        ]);
    }

    public function update(Request $request, Warehouses $warehouse)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'regions' => 'required|array|min:1',
            'regions.*' => 'exists:regions,id',
            'active' => 'required|boolean'
        ]);

        $warehouse->update([
            'name' => $request->name,
            'active' => $request->active
        ]);

        // Обновляем связи с регионами
        WarehousesToRegions::where('warehouse_id', $warehouse->id)->delete();
        foreach ($request->regions as $regionId) {
            WarehousesToRegions::create([
                'warehouse_id' => $warehouse->id,
                'region_id' => $regionId
            ]);
        }

        return redirect()->back()->with('success', 'Склад успешно обновлен');
    }

    public function destroy(Warehouses $warehouse)
    {
        WarehousesToRegions::where('warehouse_id', $warehouse->id)->delete();
        $warehouse->delete();
        return redirect()->back()->with('success', 'Склад успешно удален');
    }
}

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
            'region_id' => 'required|exists:regions,id',
            'active' => 'required|boolean'
        ]);

        $warehouse = Warehouses::create([
            'name' => $request->name,
            'active' => $request->active
        ]);

        WarehousesToRegions::create([
            'warehouse_id' => $warehouse->id,
            'region_id' => $request->region_id
        ]);

        return redirect()->back()->with('success', 'Склад успешно создан');
    }

    public function edit(Warehouses $warehouse)
    {
        $warehouse->load('regions');
        $region = $warehouse->regions->first();
        
        return response()->json([
            'id' => $warehouse->id,
            'name' => $warehouse->name,
            'active' => $warehouse->active,
            'region_id' => $region ? $region->id : null
        ]);
    }

    public function update(Request $request, Warehouses $warehouse)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'active' => 'required|boolean'
        ]);

        $warehouse->update([
            'name' => $request->name,
            'active' => $request->active
        ]);

        // Обновляем связь с регионом
        WarehousesToRegions::where('warehouse_id', $warehouse->id)->delete();
        WarehousesToRegions::create([
            'warehouse_id' => $warehouse->id,
            'region_id' => $request->region_id
        ]);

        return redirect()->back()->with('success', 'Склад успешно обновлен');
    }

    public function destroy(Warehouses $warehouse)
    {
        WarehousesToRegions::where('warehouse_id', $warehouse->id)->delete();
        $warehouse->delete();
        return redirect()->back()->with('success', 'Склад успешно удален');
    }
}

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
            'region_id' => 'required|exists:regions,id'
        ]);

        $warehouse = Warehouses::create([
            'name' => $request->name,
            'active' => true
        ]);

        WarehousesToRegions::create([
            'warehouse_id' => $warehouse->id,
            'region_id' => $request->region_id
        ]);

        return redirect()->back()->with('success', 'Склад успешно создан');
    }

    public function destroy(Warehouses $warehouse)
    {
        WarehousesToRegions::where('warehouse_id', $warehouse->id)->delete();
        $warehouse->delete();
        return redirect()->back()->with('success', 'Склад успешно удален');
    }
}

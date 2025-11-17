<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::all();
        $selectedWarehouseId = $request->input('warehouse_id');

        $stockQuery = Warehouse::query();

        if ($selectedWarehouseId) {
            $stockQuery->where('id', $selectedWarehouseId);
        }

        // Ambil data gudang, DAN produk-produk di dalamnya (beserta data 'quantity' dari pivot)
        $stockData = $stockQuery->with('products')->get();
        
        return view('stocks.index', compact('warehouses', 'stockData', 'selectedWarehouseId'));
    }

    public function createTransfer()
    {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('stocks.transfer', compact('warehouses', 'products'));
    }

    public function storeTransfer(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity_change' => 'required|integer|not_in:0',
        ]);

        $warehouseId = $request->warehouse_id;
        $productId = $request->product_id;
        $quantityChange = (int) $request->quantity_change;

        $warehouse = Warehouse::find($warehouseId);

        // Ambil data stok saat ini
        $productInWarehouse = $warehouse->products()->where('product_id', $productId)->first();
        $currentStock = $productInWarehouse ? $productInWarehouse->pivot->quantity : 0;

        $newStock = $currentStock + $quantityChange;

        // Validasi: Stok tidak boleh minus
        if ($newStock < 0) {
            return back()->withErrors([
                'quantity_change' => 'Stok akhir tidak boleh minus. Stok saat ini: ' . $currentStock . '.'
            ])->withInput();
        }

        // Gunakan syncWithoutDetaching untuk membuat/memperbarui pivot
        $warehouse->products()->syncWithoutDetaching([
            $productId => ['quantity' => $newStock]
        ]);

        return redirect()->route('stocks.index')->with('success', 'Stok berhasil diperbarui.');
    }
}
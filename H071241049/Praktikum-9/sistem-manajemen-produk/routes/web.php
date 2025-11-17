<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// 1. Manajemen Kategori
Route::resource('categories', CategoryController::class);

// 2. Manajemen Warehouse
Route::resource('warehouses', WarehouseController::class)->except([
    'show', 'destroy' // Sesuai permintaan: hanya index, create, edit
]);

// 3. Manajemen Produk
Route::resource('products', ProductController::class);

// 4. Manajemen Stok
Route::prefix('stocks')->name('stocks.')->group(function () {
    Route::get('/', [StockController::class, 'index'])->name('index');
    Route::get('/transfer', [StockController::class, 'createTransfer'])->name('transfer.create');
    Route::post('/transfer', [StockController::class, 'storeTransfer'])->name('transfer.store');
});
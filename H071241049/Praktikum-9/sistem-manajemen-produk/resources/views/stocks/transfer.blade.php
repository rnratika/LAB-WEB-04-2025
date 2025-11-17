@extends('layouts.app')

@section('content')
    <h1>Form Transfer Stok</h1>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('stocks.transfer.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="warehouse_id" class="form-label">1. Pilih Gudang</label>
                    <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                        <option value="">Pilih Gudang...</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="product_id" class="form-label">2. Pilih Produk</label>
                    <select class="form-select" id="product_id" name="product_id" required>
                        <option value="">Pilih Produk...</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="quantity_change" class="form-label">3. Jumlah Perubahan</label>
                    <input type="number" class="form-control" id="quantity_change" name="quantity_change" value="{{ old('quantity_change') }}" required>
                    <div class="form-text">
                        Gunakan angka positif untuk menambah stok (misal: <strong>10</strong>).
                        <br>
                        Gunakan angka negatif untuk mengurangi stok (misal: <strong>-5</strong>).
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Proses Transfer</button>
                <a href="{{ route('stocks.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
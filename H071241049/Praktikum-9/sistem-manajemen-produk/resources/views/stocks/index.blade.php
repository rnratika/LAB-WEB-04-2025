@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Manajemen Stok</h1>
        <a href="{{ route('stocks.transfer.create') }}" class="btn btn-primary">Transfer Stok</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('stocks.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="warehouse_id" class="form-label">Filter Gudang:</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select">
                            <option value="">Tampilkan Semua Gudang</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $selectedWarehouseId == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-info">Filter</button>
                        <a href="{{ route('stocks.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($stockData->isEmpty())
        <div class="alert alert-warning">
            @if ($selectedWarehouseId)
                Tidak ada data stok untuk gudang yang dipilih.
            @else
                Tidak ada data gudang atau stok sama sekali.
            @endif
        </div>
    @else
        @foreach ($stockData as $warehouse)
            <h3 class="mt-4">Stok di Gudang: {{ $warehouse->name }}</h3>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID Produk</th>
                        <th>Nama Produk</th>
                        <th>Total Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($warehouse->products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->pivot->quantity }} Pcs</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada produk di gudang ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    @endif

@endsection
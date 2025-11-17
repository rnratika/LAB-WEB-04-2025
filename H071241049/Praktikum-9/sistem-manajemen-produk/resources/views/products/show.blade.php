@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Detail Produk: {{ $product->name }}</h3>
        </div>
        <div class="card-body">
            <h4>Informasi Utama</h4>
            <p><strong>Nama:</strong> {{ $product->name }}</p>
            <p><strong>Kategori:</strong> {{ $product->category->name ?? 'N/A' }}</p>
            <p><strong>Harga:</strong> Rp {{ number_format($product->price, 2, ',', '.') }}</p>
            <hr>
            <h4>Informasi Detail</h4>
            <p><strong>Deskripsi:</strong> {{ $product->detail->description ?? 'N/A' }}</p>
            <p><strong>Berat:</strong> {{ $product->detail->weight ?? 'N/A' }} kg</p>
            <p><strong>Ukuran:</strong> {{ $product->detail->size ?? 'N/A' }}</p>
            <hr>
            <p><strong>Dibuat pada:</strong> {{ $product->created_at->format('d-m-Y H:i') }}</p>
            <p><strong>Diperbarui pada:</strong> {{ $product->updated_at->format('d-m-Y H:i') }}</p>
        </div>
        <div class="card-footer">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
@endsection
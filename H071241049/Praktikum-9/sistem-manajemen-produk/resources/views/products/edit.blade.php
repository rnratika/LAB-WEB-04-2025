@extends('layouts.app')

@section('content')
    <h1>Edit Produk: {{ $product->name }}</h1>
    
    <form action="{{ route('products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-header">Data Produk</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Kategori</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Pilih Kategori (Opsional)</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                 <div class="mb-3">
                    <label for="price" class="form-label">Harga</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Detail Produk</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi Lengkap</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $product->detail->description ?? '') }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="weight" class="form-label">Berat (kg)</label>
                    <input type="number" step="0.01" class="form-control" id="weight" name="weight" value="{{ old('weight', $product->detail->weight ?? '') }}" required>
                </div>
                <div class="mb-3">
                    <label for="size" class="form-label">Ukuran</label>
                    <input type="text" class="form-control" id="size" name="size" value="{{ old('size', $product->detail->size ?? '') }}">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update Produk</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Batal</a>
    </form>
@endsection
@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Detail Kategori: {{ $category->name }}</h3>
        </div>
        <div class="card-body">
            <p><strong>Nama:</strong> {{ $category->name }}</p>
            <p><strong>Deskripsi:</strong> {{ $category->description ?? 'N/A' }}</p>
            <p><strong>Dibuat pada:</strong> {{ $category->created_at->format('d-m-Y H:i') }}</p>
            <p><strong>Diperbarui pada:</strong> {{ $category->updated_at->format('d-m-Y H:i') }}</p>
        </div>
        <div class="card-footer">
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="p-5 mb-4 bg-light rounded-3">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Selamat Datang!</h1>
            <p class="col-md-8 fs-4">Sistem Manajemen Produk dan Stok</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">Lihat Produk</a>
        </div>
    </div>
@endsectio\
@extends('layouts.app')

@section('content')
    <h1>Edit Gudang: {{ $warehouse->name }}</h1>
    
    <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Nama Gudang</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $warehouse->name) }}" required>
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">Lokasi</label>
            <textarea class="form-control" id="location" name="location" rows="3">{{ old('location', $warehouse->location) }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection
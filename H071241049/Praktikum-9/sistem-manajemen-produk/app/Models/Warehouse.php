<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function products()
    {
        // Tentukan nama pivot table, dan ambil kolom 'quantity'
        return $this->belongsToMany(Product::class, 'product_warehouse')
                    ->withPivot('quantity');
    }
}
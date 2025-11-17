<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nama tabel 'product_warehouse' (snake_case, singular)
        // adalah konvensi Laravel untuk pivot N:M
        Schema::create('product_warehouse', function (Blueprint $table) {
            
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');
            
            $table->foreignId('warehouse_id')
                  ->constrained('warehouses')
                  ->onDelete('cascade');
                  
            $table->integer('quantity')->default(0);

            $table->primary(['product_id', 'warehouse_id']);
            
            // Kita tidak perlu timestamps untuk pivot ini
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warehouse');
    }
};
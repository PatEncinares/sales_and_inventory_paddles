<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'brand_id',
        'color',
        'price',
        'cost',
        'stock_qty',
        'description',
        'is_active',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}

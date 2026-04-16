<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Products extends Model
{
    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'cabys_code',
        'vehicle_compatibility',
        'min_stock',
        'purchase_price',
        'cost_price',
        'distributor_price',
        'sale_price',
        'tax_percentage',
        'category_id',
        'brand_id',
        'supplier_id',
    ];

    protected $casts = [
        'vehicle_compatibility' => 'array',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brands::class, 'brand_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class, 'supplier_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class, 'product_id');
    }
}

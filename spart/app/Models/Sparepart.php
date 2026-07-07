<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sparepart extends Model
{
    protected $fillable = [
        'material_code',
        'bin_location',
        'old_material_no',
        'description',
        'stock',
        'unit',
        'min_stock',
        'category_id',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function isLowStock(): bool
    {
        // Tidak dianggap low stock jika min_stock 0 atau null
        if (!$this->min_stock || $this->min_stock == 0) {
            return false;
        }
        
        return $this->stock < $this->min_stock;
    }
}

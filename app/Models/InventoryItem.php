<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_code',
        'name',
        'description',
        'category',
        'unit_of_measure',
        'unit_cost',
        'reorder_level',
        'reorder_quantity',
        'item_type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'reorder_level' => 'decimal:2',
            'reorder_quantity' => 'decimal:2',
        ];
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getCurrentStockAttribute()
    {
        return $this->stockMovements()
            ->orderBy('created_at', 'desc')
            ->value('balance_after') ?? 0;
    }

    public function purchaseRequestItems()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function materialIssuanceItems()
    {
        return $this->hasMany(MaterialIssuanceItem::class);
    }

    public function supplierPrices()
    {
        return $this->hasMany(SupplierPrice::class);
    }
}


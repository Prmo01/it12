<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_id',
        'status',
        'notes',
    ];

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function prices()
    {
        return $this->hasMany(SupplierPrice::class);
    }

    public function getPriceForItem($inventoryItemId)
    {
        $price = $this->prices()->where('inventory_item_id', $inventoryItemId)->first();
        return $price ? $price->unit_price : null;
    }
}


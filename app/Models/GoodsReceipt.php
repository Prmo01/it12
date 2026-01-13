<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'gr_number',
        'project_code',
        'purchase_order_id',
        'gr_date',
        'status',
        'delivery_note_number',
        'remarks',
        'cancellation_reason',
        'received_by',
        'approved_by',
        'approved_at',
        'warehouse_approved_by',
        'warehouse_approved_at',
        'inventory_approved_by',
        'inventory_approved_at',
        'inventory_feedback',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'gr_date' => 'date',
            'approved_at' => 'datetime',
            'warehouse_approved_at' => 'datetime',
            'inventory_approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function warehouseApprovedBy()
    {
        return $this->belongsTo(User::class, 'warehouse_approved_by');
    }

    public function inventoryApprovedBy()
    {
        return $this->belongsTo(User::class, 'inventory_approved_by');
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function goodsReturns()
    {
        return $this->hasMany(GoodsReturn::class);
    }
}


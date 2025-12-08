<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_code',
        'name',
        'description',
        'project_manager_id',
        'start_date',
        'end_date',
        'actual_end_date',
        'status',
        'budget',
        'actual_cost',
        'progress_percentage',
        'notes',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'actual_end_date' => 'date',
            'budget' => 'decimal:2',
            'actual_cost' => 'decimal:2',
        ];
    }

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function changeOrders()
    {
        return $this->hasMany(ChangeOrder::class);
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function materialIssuances()
    {
        return $this->hasMany(MaterialIssuance::class);
    }

    public function quotations()
    {
        return $this->hasManyThrough(
            Quotation::class,
            PurchaseRequest::class,
            'project_id', // Foreign key on purchase_requests table
            'purchase_request_id', // Foreign key on quotations table
            'id', // Local key on projects table
            'id' // Local key on purchase_requests table
        );
    }

    public function purchaseOrders()
    {
        return $this->hasManyThrough(
            PurchaseOrder::class,
            PurchaseRequest::class,
            'project_id', // Foreign key on purchase_requests table
            'purchase_request_id', // Foreign key on purchase_orders table
            'id', // Local key on projects table
            'id' // Local key on purchase_requests table
        );
    }
}


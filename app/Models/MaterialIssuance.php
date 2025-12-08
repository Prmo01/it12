<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialIssuance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'issuance_number',
        'project_id',
        'work_order_number',
        'issuance_type',
        'issuance_date',
        'status',
        'purpose',
        'requested_by',
        'approved_by',
        'issued_by',
        'approved_at',
        'issued_at',
        'notes',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'issuance_date' => 'date',
            'approved_at' => 'datetime',
            'issued_at' => 'datetime',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items()
    {
        return $this->hasMany(MaterialIssuanceItem::class);
    }
}


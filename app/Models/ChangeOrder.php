<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChangeOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'change_order_number',
        'description',
        'reason',
        'additional_days',
        'additional_cost',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'additional_cost' => 'decimal:2',
            'approved_at' => 'datetime',
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
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'customer_id',
        'invoice_number',
        'amount',
        'billing_date',
        'due_date',
        'paid_date',
        'status',
        'notes',
        'created_by',
        'confirmed_by'
    ];

    protected $casts = [
        'billing_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
            if (empty($model->invoice_number)) {
                $model->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $lastInvoice = static::where('invoice_number', 'like', $prefix . $date . '%')
                           ->orderBy('invoice_number', 'desc')
                           ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $date . $newNumber;
    }

    /**
     * Customer relationship
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Created by relationship
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Confirmed by relationship
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Check if payment is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || 
               ($this->status === 'pending' && $this->due_date < now()->toDateString());
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdue(): int
    {
        if ($this->status === 'paid') {
            return 0;
        }
        
        if ($this->due_date >= now()->toDateString()) {
            return 0;
        }
        
        return Carbon::parse($this->due_date)->diffInDays(now(), false);
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => $this->isOverdue() ? 'Terlambat' : 'Belum Bayar',
            'paid' => 'Sudah Bayar',
            'overdue' => 'Terlambat',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => $this->isOverdue() ? 'badge-danger' : 'badge-warning',
            'paid' => 'badge-success',
            'overdue' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmount(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($confirmedBy = null): bool
    {
        $this->status = 'paid';
        $this->paid_date = now()->toDateString();
        $this->confirmed_by = $confirmedBy ?? auth()->id();
        
        return $this->save();
    }

    /**
     * Update overdue status
     */
    public function updateOverdueStatus(): bool
    {
        if ($this->status === 'pending' && $this->due_date < now()->toDateString()) {
            $this->status = 'overdue';
            return $this->save();
        }
        
        return false;
    }
}

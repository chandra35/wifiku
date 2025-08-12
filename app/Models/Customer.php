<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Customer extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'name',
        'phone',
        'email',
        'identity_number',
        'birth_date',
        'gender',
        'address',
        'postal_code',
        'province_id',
        'city_id',
        'district_id',
        'village_id',
        'package_id',
        'installation_date',
        'billing_cycle',
        'next_billing_date',
        'status',
        'notes',
        'pppoe_secret_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'installation_date' => 'date',
        'next_billing_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid4()->toString();
            }
            
            // Generate customer_id jika belum ada
            if (empty($model->customer_id)) {
                $model->customer_id = static::generateCustomerId();
            }
        });
    }

    /**
     * Generate unique customer ID
     */
    public static function generateCustomerId(): string
    {
        // Ambil prefix dari AppSetting, jika kosong maka ''
        $prefix = \App\Models\AppSetting::getValue('customer_prefix', '');
        $prefix = trim($prefix);
        // 6 digit random (unique)
        do {
            $random = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
            $customerId = $prefix ? ($prefix . $random) : $random;
        } while (static::where('customer_id', $customerId)->exists());
        return $customerId;
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function pppoeSecret(): BelongsTo
    {
        return $this->belongsTo(UserPppoe::class, 'pppoe_secret_id');
    }

    // Area/Wilayah relationships (Laravolt)
    public function province(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class, 'province_id', 'code');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class, 'city_id', 'code');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class, 'district_id', 'code');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Village::class, 'village_id', 'code');
    }

    /**
     * Payment relationship
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get current pending payment
     */
    public function getCurrentPendingPayment()
    {
        return $this->payments()
                   ->where('status', 'pending')
                   ->orderBy('due_date', 'asc')
                   ->first();
    }

    /**
     * Get overdue payments
     */
    public function getOverduePayments()
    {
        return $this->payments()
                   ->whereIn('status', ['pending', 'overdue'])
                   ->where('due_date', '<', now()->toDateString())
                   ->orderBy('due_date', 'asc')
                   ->get();
    }

    /**
     * Check if customer has overdue payments
     */
    public function hasOverduePayments(): bool
    {
        return $this->getOverduePayments()->count() > 0;
    }

    /**
     * Get payment status for billing
     */
    public function getPaymentStatus(): string
    {
        if ($this->hasOverduePayments()) {
            return 'overdue';
        }
        
        $pendingPayment = $this->getCurrentPendingPayment();
        if ($pendingPayment) {
            return 'pending';
        }
        
        return 'up_to_date';
    }

    /**
     * Get complete address
     */
    public function getFullAddress(): string
    {
        $addressParts = array_filter([
            $this->address,
            $this->village ? $this->village->name : null,
            $this->district ? $this->district->name : null,
            $this->city ? $this->city->name : null,
            $this->province ? $this->province->name : null,
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'suspended' => 'Disuspend',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'badge-success',
            'inactive' => 'badge-secondary',
            'suspended' => 'badge-warning',
            default => 'badge-secondary'
        };
    }
}

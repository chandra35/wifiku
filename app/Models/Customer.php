<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        $prefix = 'CUS';
        $year = date('Y');
        $month = date('m');
        
        // Get last customer for this month
        $lastCustomer = static::where('customer_id', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('customer_id', 'desc')
            ->first();
        
        if ($lastCustomer) {
            $lastNumber = (int) substr($lastCustomer->customer_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
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

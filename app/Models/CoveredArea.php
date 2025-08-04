<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoveredArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'province_id',
        'city_id',
        'district_id',
        'village_id',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Relationship to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to Province
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class);
    }

    /**
     * Relationship to City
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class);
    }

    /**
     * Relationship to District
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class);
    }

    /**
     * Relationship to Village
     */
    public function village(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Village::class);
    }

    /**
     * Get complete area name
     */
    public function getCompleteAreaAttribute(): string
    {
        $parts = [];
        
        // Jika village_id kosong, tampilkan "Semua Desa/Kelurahan"
        if ($this->village) {
            $parts[] = $this->village->name;
        } else {
            $parts[] = 'Semua Desa/Kelurahan';
        }
        
        if ($this->district) $parts[] = $this->district->name;
        if ($this->city) $parts[] = $this->city->name;
        if ($this->province) $parts[] = $this->province->name;
        
        return implode(', ', $parts);
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active' => '<span class="badge badge-success">Active</span>',
            'inactive' => '<span class="badge badge-secondary">Inactive</span>',
            default => '<span class="badge badge-secondary">Unknown</span>',
        };
    }

    /**
     * Scope for active areas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for user areas
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

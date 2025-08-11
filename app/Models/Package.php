<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Package extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'ppp_profile_id',
        'rate_limit',
        'local_address',
        'remote_address',
        'dns_server',
        'only_one',
        'session_timeout',
        'idle_timeout',
        'address_list',
        'price',
        'price_before_tax',
        'currency',
        'billing_cycle',
        'router_id',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_before_tax' => 'decimal:2',
        'only_one' => 'boolean',
        'is_active' => 'boolean',
        'session_timeout' => 'integer',
        'idle_timeout' => 'integer',
        'billing_cycle' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid4()->toString();
            }
        });
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

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function pppProfile(): BelongsTo
    {
        return $this->belongsTo(PppProfile::class, 'ppp_profile_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        return number_format($this->price, 0, ',', '.') . ' ' . $this->currency;
    }

    /**
     * Get formatted price before tax
     */
    public function getFormattedPriceBeforeTax(): string
    {
        return number_format($this->price_before_tax, 0, ',', '.') . ' ' . $this->currency;
    }

    /**
     * Get PPN amount
     */
    public function getPpnAmount(): float
    {
        return $this->price - $this->price_before_tax;
    }

    /**
     * Get formatted PPN amount
     */
    public function getFormattedPpnAmount(): string
    {
        return number_format($this->getPpnAmount(), 0, ',', '.') . ' ' . $this->currency;
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return $this->is_active ? 'Aktif' : 'Tidak Aktif';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return $this->is_active ? 'badge-success' : 'badge-secondary';
    }

    /**
     * Get speed description from rate_limit
     */
    public function getSpeedDescription(): string
    {
        if (!$this->rate_limit) {
            return 'Unlimited';
        }

        // Parse rate_limit format: rx-rate[/tx-rate] [rx-burst-rate[/tx-burst-rate] ...]
        $parts = explode(' ', $this->rate_limit);
        if (empty($parts[0])) {
            return 'Unlimited';
        }

        $speeds = explode('/', $parts[0]);
        $rxRate = $this->formatSpeed($speeds[0] ?? '');
        $txRate = $this->formatSpeed($speeds[1] ?? $speeds[0]);

        if ($rxRate === $txRate) {
            return $rxRate;
        }

        return "↓{$rxRate} / ↑{$txRate}";
    }

    /**
     * Format speed from bytes to human readable
     */
    private function formatSpeed(string $speed): string
    {
        if (empty($speed) || $speed === '0') {
            return 'Unlimited';
        }

        $speed = (int) $speed;
        $units = ['bps', 'Kbps', 'Mbps', 'Gbps'];
        $unitIndex = 0;

        while ($speed >= 1000 && $unitIndex < count($units) - 1) {
            $speed /= 1000;
            $unitIndex++;
        }

        return round($speed, 1) . ' ' . $units[$unitIndex];
    }

    /**
     * Scope for active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered packages
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}

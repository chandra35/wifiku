<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class PppProfile extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'router_id',
        'local_address',
        'remote_address',
        'dns_server',
        'rate_limit',
        'session_timeout',
        'idle_timeout',
        'only_one',
        'comment',
        'mikrotik_id',
        'created_by',
    ];

    protected $casts = [
        'only_one' => 'boolean',
        'session_timeout' => 'integer',
        'idle_timeout' => 'integer',
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

    /**
     * Get the router that owns the PPP profile
     */
    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    /**
     * Get the user who created this profile
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

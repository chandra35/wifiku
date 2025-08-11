<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'ip_address',
        'username',
        'password',
        'port',
        'status',
        'description',
        'routeros_version',
        'architecture',
        'board_name',
        'last_system_check',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'last_system_check' => 'datetime',
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_routers');
    }

    public function pppProfiles(): HasMany
    {
        return $this->hasMany(PppProfile::class);
    }

    public function pppoeSecrets(): HasMany
    {
        return $this->hasMany(UserPppoe::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

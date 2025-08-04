<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'pic_photo',
        'isp_logo',
        'company_name',
        'company_address',
        'company_phone',
        'province_id',
        'city_id',
        'district_id',
        'village_id',
        'full_address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function routers(): BelongsToMany
    {
        return $this->belongsToMany(Router::class, 'user_routers');
    }

    public function pppoeSecrets(): HasMany
    {
        return $this->hasMany(UserPppoe::class);
    }

    public function coveredAreas(): HasMany
    {
        return $this->hasMany(CoveredArea::class);
    }

    // Area/Wilayah relationships
    public function province(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Village::class);
    }

    public function hasRole($roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Get PIC photo URL
     */
    public function getPicPhotoUrl()
    {
        if ($this->pic_photo && Storage::disk('public')->exists($this->pic_photo)) {
            return asset('storage/' . $this->pic_photo);
        }
        return null;
    }

    /**
     * Get ISP logo URL
     */
    public function getIspLogoUrl()
    {
        if ($this->isp_logo && Storage::disk('public')->exists($this->isp_logo)) {
            return asset('storage/' . $this->isp_logo);
        }
        return null;
    }

    /**
     * Get complete company information for invoice generation
     */
    public function getCompanyInfo()
    {
        return [
            'company_name' => $this->company_name ?? 'Tidak diset',
            'company_address' => $this->company_address ?? 'Tidak diset',
            'company_phone' => $this->company_phone ?? 'Tidak diset',
            'pic_name' => $this->name,
            'pic_email' => $this->email,
            'pic_photo_url' => $this->getPicPhotoUrl(),
            'isp_logo_url' => $this->getIspLogoUrl(),
            'has_complete_info' => $this->hasCompleteCompanyInfo()
        ];
    }

    /**
     * Check if user has complete company information for invoice generation
     */
    public function hasCompleteCompanyInfo()
    {
        return !empty($this->company_name) && 
               !empty($this->company_address) && 
               !empty($this->company_phone) && 
               !empty($this->pic_photo) && 
               !empty($this->isp_logo);
    }

    /**
     * Get complete area information
     */
    public function getAreaInfo()
    {
        return [
            'province' => $this->province ? $this->province->name : null,
            'city' => $this->city ? $this->city->name : null,
            'district' => $this->district ? $this->district->name : null,
            'village' => $this->village ? $this->village->name : null,
            'full_address' => $this->full_address,
            'complete_address' => $this->getCompleteAddress()
        ];
    }

    /**
     * Get complete formatted address
     */
    public function getCompleteAddress()
    {
        $addressParts = array_filter([
            $this->full_address,
            $this->village ? $this->village->name : null,
            $this->district ? $this->district->name : null,
            $this->city ? $this->city->name : null,
            $this->province ? $this->province->name : null,
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Check if user has complete area information
     */
    public function hasCompleteAreaInfo()
    {
        return !empty($this->province_id) && 
               !empty($this->city_id) && 
               !empty($this->district_id) && 
               !empty($this->village_id) && 
               !empty($this->full_address);
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\Exportable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Account extends Authenticatable implements JWTSubject
{
    use Exportable;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'type',
        'mobile',
        'source',
        'pending_email',
        'is_active',
        'last_login_at',
        'last_activity',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getPhoneAttribute()
    {
        return $this->removeCountryCode($this->attributes['phone']); //
    }

    public function getMobileAttribute()
    {
        return $this->removeCountryCode($this->attributes['mobile']);
    }

    private function removeCountryCode($value)
    {
        if (str_starts_with($value, '+966')) {
            return substr($value, 4);
        }

        if (str_starts_with($value, '966')) {
            return substr($value, 3);
        }

        return $value;
    }

    protected static $cacheKey = 'accounts_cache';

    protected static $cacheDuration = 3600; // 1 hour

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            Cache::forget(self::$cacheKey);
        });

        static::updated(function ($model) {
            Cache::forget(self::$cacheKey);
        });

        static::deleted(function ($model) {
            Cache::forget(self::$cacheKey);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active');
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        // static::observe(AccountObserver::class);
    }

    /**
     * Relationships.
     */
    // public function logs(): HasMany
    // {
    //     return $this->hasMany(
    //         Log::class,
    //         'by_user_id'
    //     );
    // }

    public function toExportArray()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'type' => $this->type,
            'source' => $this->source,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Activity Log.
     */

    /**
     * Get the exportable data for the model.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return self::select([
            'name',
            'email',
            'phone',
            'mobile',
            'type',
            'source',
            'is_active',
        ])->get();
    }

    public static function getCached($id)
    {
        return Cache::remember(static::$cacheKey."_{$id}", static::$cacheDuration, function () use ($id) {
            return static::find($id);
        });
    }

    public static function getAllCached()
    {
        return Cache::remember(static::$cacheKey.'_all', static::$cacheDuration, function () {
            return static::all();
        });
    }

    public function scopeFilterHotel($query, $filters)
    {
        if (isset($filters['search'])) {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%')
                    ->orWhere('phone', 'like', '%'.$filters['search'].'%');
            });

            $query->orWhereHas('hotel', function ($subQuery) use ($filters) {
                $subQuery->where('hotel_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('about_hotel', 'like', '%'.$filters['search'].'%');
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['stars'])) {
            $query->whereHas('hotel', function ($ratingQuery) use ($filters) {
                $ratingQuery->where('stars', '>=', $filters['stars']);
            });
        }

        return $query;
    }

    public function scopeFilterProvider($query, $filters)
    {
        if (isset($filters['search'])) {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%')
                    ->orWhere('phone', 'like', '%'.$filters['search'].'%');
            });

            $query->orWhereHas('provider', function ($subQuery) use ($filters) {
                $subQuery->where('company_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('about_company', 'like', '%'.$filters['search'].'%');
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['main_service_id'])) {
            $query->whereHas('mainServiceProviders', function ($subQuery) use ($filters) {
                $subQuery->where('main_service_id', $filters['main_service_id']);
            });
        }

        return $query;
    }

    public function isOnline()
    {
        return $this->last_login_at &&
               Carbon::parse($this->last_login_at)->diffInMinutes(now()) <= 5;
    }

    public function getActivityStatusAttribute()
    {
        if ($this->isOnline()) {
            return 'online';
        }

        if ($this->last_login_at) {
            return 'last_seen: '.Carbon::parse($this->last_login_at)->diffForHumans();
        }

        return 'offline';
    }
}

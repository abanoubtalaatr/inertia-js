<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\OpeningHours\OpeningHours;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use CanResetPasswordTrait;
    use HasApiTokens, HasFactory, HasRoles,   Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'created_at',
        'is_active',
        'avatar',
        'updated_at',
        'role',
        'company_id',
        'phone',
        'bio',
        'email_notifications',
        'is_suspend',
        'last_login_at',
        'verified',
        'status',
        'rejection_reason',
        'opening_hours_data',
        'session_duration',
        'buffer_time',
    ];

    protected $appends = ['avatar'];

    public function casts()
    {
        return [
            'email_notifications' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'date:Y-m-d',
            'opening_hours_data' => 'array',
        ];
    }

    protected $attributes = [
        'session_duration' => 45, // القيمة الافتراضية 45 دقيقة
        'buffer_time' => 15,      // القيمة الافتراضية 15 دقيقة
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function getAvatarAttribute()
    {
        if (isset($this->attributes['avatar'])) {
            return asset("storage/{$this->attributes['avatar']}");
        }

        return null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('d M Y');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(
            \App\Models\Log::class,
            'by_user_id'
        );
    }

    public function isAdmin()
    {
        return $this->hasAnyRole(['superadmin']);
    }

    /**
     * Get the opening hours instance for the doctor.
     */
    public function getOpeningHoursAttribute()
    {
        if (! $this->opening_hours_data) {
            return null;
        }

        return OpeningHours::create($this->opening_hours_data);
    }

    /**
     * Check if the doctor is available at a specific date and time.
     */
    public function isAvailableAt($dateTime)
    {
        if (! $this->opening_hours) {
            return false;
        }

        return $this->opening_hours->isOpenAt($dateTime);
    }

    /**
     * Get available time slots for a specific date.
     *
     * @param  string  $date  Date in Y-m-d format
     * @param  int  $duration  Duration in minutes
     * @return array Array of available time slots
     */
    public function getAvailableSlots($date, $duration = 30)
    {
        if (! $this->opening_hours) {
            return [];
        }

        $carbonDate = \Carbon\Carbon::parse($date);
        $slots = [];

        // Check if the doctor works on this day
        if ($this->opening_hours->isOpenOn($carbonDate->format('l'))) {
            // Get the ranges for this day (accounting for exceptions)
            $ranges = $this->opening_hours->forDate($carbonDate);

            foreach ($ranges as $range) {
                $start = \Carbon\Carbon::parse($range->start()->format('H:i'));
                $end = \Carbon\Carbon::parse($range->end()->format('H:i'));

                // Generate slots
                $slotStart = $start->copy();
                while ($slotStart->copy()->addMinutes($duration)->lte($end)) {
                    $slotEnd = $slotStart->copy()->addMinutes($duration);

                    // Check if this slot conflicts with existing appointments
                    $hasConflict = $this->appointments()
                        ->where('date', $carbonDate->format('Y-m-d'))
                        ->where('status', '!=', 'cancelled')
                        ->where(function ($query) use ($slotStart, $slotEnd) {
                            $query->where(function ($q) use ($slotStart) {
                                $q->where('start_time', '<=', $slotStart->format('H:i'))
                                    ->where('end_time', '>', $slotStart->format('H:i'));
                            })->orWhere(function ($q) use ($slotStart, $slotEnd) {
                                $q->where('start_time', '>=', $slotStart->format('H:i'))
                                    ->where('start_time', '<', $slotEnd->format('H:i'));
                            });
                        })
                        ->exists();

                    if (! $hasConflict) {
                        $slots[] = [
                            'start' => $slotStart->format('H:i'),
                            'end' => $slotEnd->format('H:i'),
                        ];
                    }

                    $slotStart->addMinutes($duration);
                }
            }
        }

        return $slots;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    public function specialistBookings()
    {
        return $this->hasMany(Booking::class, 'specialist_id');
    }

    public function givenRatings()
    {
        return $this->hasMany(Rating::class, 'user_id');
    }

    public function receivedRatings()
    {
        return $this->hasMany(Rating::class, 'specialist_id');
    }

    // Specialist relationships
    public function bookingsAsSpecialist()
    {
        return $this->hasMany(Booking::class, 'specialist_id');
    }

    // Client relationships
    public function bookingsAsClient()
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    public function medicalHistory()
    {
        return $this->hasOne(MedicalHistory::class, 'client_id');
    }

    public function treatmentPlans()
    {
        return $this->hasMany(TreatmentPlan::class, 'client_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'client_id');
    }

    // علاقة مع المواعيد
    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    // علاقة مع الإجازات
    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }
}

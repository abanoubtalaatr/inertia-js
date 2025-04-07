<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'type',
        'expires_at',
    ];

    // Cast the `expires_at` field to a datetime instance
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the OTP is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->expires_at->isFuture();
    }
}

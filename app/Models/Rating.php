<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'specialist_id', 'booking_id', 'rating', 'feedback'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function specialist()
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

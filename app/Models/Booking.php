<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'specialist_id',
        'client_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    protected $dates = ['date'];

    public function specialist()
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }
}

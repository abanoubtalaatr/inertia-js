<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = ['specialist_id', 'client_id', 'instructions', 'dosage', 'medication'];

    public function specialist()
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}

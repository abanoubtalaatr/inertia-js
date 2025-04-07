<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentPlan extends Model
{
    use HasFactory;

    protected $fillable = ['specialist_id', 'client_id', 'details', 'start_date'];

    public function specialist()
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }
}

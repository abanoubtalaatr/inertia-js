<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalHistory extends Model
{
    protected $fillable = ['client_id', 'details'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}

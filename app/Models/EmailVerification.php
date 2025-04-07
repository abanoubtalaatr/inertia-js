<?php

// app/Models/EmailVerification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    protected $fillable = ['user_id', 'email', 'token'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    //
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'message',
        'recipient_type',
        'recipient_id',
        'status',
        'scheduled_at',
        'sent_at',
        'created_by',
        'read_at',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

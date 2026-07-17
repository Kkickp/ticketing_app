<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'status_sebelum',
        'status_sesudah',
        'user_id',
    ];

    /**
     * Relationship: Belongs to Event.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Relationship: Belongs to User (who performed the action).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

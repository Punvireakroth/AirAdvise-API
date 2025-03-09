<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'status',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responses()
    {
        return $this->hasMany(FeedbackResponse::class);
    }

    // Check if feedback has any responses
    public function hasResponses()
    {
        return $this->responses()->exists();
    }

    // Latest response
    public function latestResponse()
    {
        return $this->responses()->latest()->first();
    }
}

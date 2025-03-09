<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_id',
        'admin_id',
        'response',
    ];

    // Relationships
    public function feedback()
    {
        return $this->belongsTo(Feedback::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}

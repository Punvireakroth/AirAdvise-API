<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function preference()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function userLocations()
    {
        return $this->hasMany(UserLocation::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'user_locations')
            ->withPivot('is_favorite')
            ->withTimestamps();
    }

    public function favoriteCities()
    {
        return $this->belongsToMany(City::class, 'user_favorite_cities')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function feedbackResponses()
    {
        return $this->hasMany(FeedbackResponse::class, 'admin_id');
    }

    public function healthTips()
    {
        return $this->hasMany(HealthTip::class, 'created_by');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}

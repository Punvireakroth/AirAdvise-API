<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'featured_image',
        'summary',
        'created_by',
        'is_published',
        'published_at',
        'category',
        'view_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'view_count' => 'integer',
    ];

    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope for published articles
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    // Scope for articles by category
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}

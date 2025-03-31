<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_name',
        'endpoint',
        'parameters',
        'response_code',
        'execution_time',
        'created_at',
    ];

    public $timestamps = true;

    protected $casts = [
        'parameters' => 'array',
        'response_code' => 'integer',
        'execution_time' => 'integer',
        'created_at' => 'datetime',
    ];

    // Scope for filtering logs by API name
    public function scopeForApi($query, $apiName)
    {
        return $query->where('api_name', $apiName);
    }

    // Scope for filtering logs by time period
    public function scopeWithinPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Scope for failed requests
    public function scopeFailed($query)
    {
        return $query->where('response_code', '>=', 400);
    }
}
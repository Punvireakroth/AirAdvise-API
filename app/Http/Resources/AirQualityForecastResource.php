<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AirQualityForecastResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'location_id' => $this->location_id,
            'forecast_date' => $this->forecast_date->format('Y-m-d'),
            'aqi' => $this->aqi,
            'pm25' => $this->pm25,
            'pm10' => $this->pm10,
            'o3' => $this->o3,
            'no2' => $this->no2,
            'so2' => $this->so2,
            'co' => $this->co,
            'category' => $this->category,
            'description' => $this->description,
            'recommendation' => $this->recommendation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AirQualityDataResource extends JsonResource
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
            'location' => new LocationResource($this->whenLoaded('location')),
            'aqi' => $this->aqi,
            'category' => $this->category,
            'color_code' => $this->getColorCode(),
            'pollutants' => [
                'pm25' => $this->pm25,
                'pm10' => $this->pm10,
                'o3' => $this->o3,
                'no2' => $this->no2,
                'so2' => $this->so2,
                'co' => $this->co,
            ],
            'source' => $this->source,
            'timestamp' => $this->timestamp,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function getColorCode()
    {
        return [
            'Good' => '#00E400',
            'Moderate' => '#FFFF00',
            'Unhealthy for Sensitive Groups' => '#FF7E00',
            'Unhealthy' => '#FF0000',
            'Very Unhealthy' => '#8F3F97',
            'Hazardous' => '#7E0023',
        ][$this->category] ?? '#CCCCCC';
    }
}

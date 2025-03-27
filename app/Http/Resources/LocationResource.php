<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'city_name' => $this->city_name,
            'state_province' => $this->state_province,
            'country' => $this->country,
            'country_code' => $this->country_code,
            'full_name' => $this->full_name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timezone' => $this->timezone,
            'is_active' => $this->is_active,
            'is_favorite' => $this->whenPivotLoaded('user_locations', function () {
                return (bool) $this->pivot->is_favorite;
            }),
            'latest_air_quality' => new AirQualityDataResource($this->whenLoaded('latestAirQuality')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

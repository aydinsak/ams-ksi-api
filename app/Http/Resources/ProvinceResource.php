<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\mini\CityMiniResource;

class ProvinceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'code' => $this->code ?? null,
            'name' => $this->name ?? null,
            'cities' => $this->when(
                $request->boolean('include_cities') && $this->relationLoaded('cities'),
                fn() => CityMiniResource::collection($this->cities)
            ),
        ];
    }
}

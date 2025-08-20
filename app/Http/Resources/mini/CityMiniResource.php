<?php

namespace App\Http\Resources\mini;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityMiniResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'code'  => $this->code ?? null,
            'name'  => $this->name ?? $this->city ?? null,
        ];
    }
}

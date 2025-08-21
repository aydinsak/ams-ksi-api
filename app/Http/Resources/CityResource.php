<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\mini\ProvinceMiniResource;

class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name ?? $this->city ?? null,
            'code'        => $this->code ?? null,
            'status'      => $this->status ?? null,
            'province_id' => $this->province_id ?? null,
            'province'    => $this->when(
                $request->boolean('include_province') && $this->relationLoaded('province'),
                fn() => new ProvinceMiniResource($this->province)
            ),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'email'    => $this->email,
            'username' => $this->username,
            'phone'    => $this->phone,
            'status'   => $this->status,
            'type'     => $this->type,
            'image'    => $this->image,
            'provider_id'   => $this->provider_id,
            'perusahaan_id' => $this->perusahaan_id,
            'position_id'   => $this->position_id,

            // include relasi jika diminta & sudah diload
            'perusahaan' => $this->when(
                $request->boolean('include_perusahaan') && $this->relationLoaded('perusahaan'),
                fn() => [
                    'id'   => $this->perusahaan?->id,
                    'code' => $this->perusahaan?->code,
                    'name' => $this->perusahaan?->name,
                ]
            ),
            'provider' => $this->when(
                $request->boolean('include_provider') && $this->relationLoaded('provider'),
                fn() => [
                    'id'   => $this->provider?->id,
                    'code' => $this->provider?->code,
                    'name' => $this->provider?->name,
                ]
            ),
            'position' => $this->when(
                $request->boolean('include_position') && $this->relationLoaded('position'),
                fn() => [
                    'id'   => $this->position?->id,
                    'code' => $this->position?->code,
                    'name' => $this->position?->name,
                ]
            ),
            'creator' => $this->when(
                $request->boolean('include_creator') && $this->relationLoaded('creator'),
                fn() => [
                    'id'   => $this->creator?->id,
                    'name' => $this->creator?->name,
                    'email' => $this->creator?->email,
                ]
            ),
            'updater' => $this->when(
                $request->boolean('include_updater') && $this->relationLoaded('updater'),
                fn() => [
                    'id'   => $this->updater?->id,
                    'name' => $this->updater?->name,
                    'email' => $this->updater?->email,
                ]
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

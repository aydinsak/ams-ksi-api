<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiskAssessmentResource extends JsonResource
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
            'code'        => $this->code,
            'period'      => $this->period,
            'rev'         => $this->rev,
            'status'      => $this->status,
            'sasaran'     => $this->sasaran,
            'perusahaan_id' => $this->perusahaan_id,
            'object_id'     => $this->object_id,
            'type_id'       => $this->type_id,

            'perusahaan' => $this->when(
                $request->boolean('include_perusahaan') && $this->relationLoaded('perusahaan'),
                fn() => [
                    'id'   => $this->perusahaan?->id,
                    'code' => $this->perusahaan?->code,
                    'name' => $this->perusahaan?->name,
                ]
            ),
            'object' => $this->when(
                $request->boolean('include_object') && $this->relationLoaded('object'),
                fn() => [
                    'id'   => $this->object?->id,
                    'code' => $this->object?->code,
                    'name' => $this->object?->name,
                ]
            ),
            'type' => $this->when(
                $request->boolean('include_type') && $this->relationLoaded('type'),
                fn() => [
                    'id'   => $this->type?->id,
                    'name' => $this->type?->name,
                    'code' => $this->type?->code,
                ]
            ),
            'details' => $this->when(
                $request->boolean('include_details') && $this->relationLoaded('details'),
                fn() => $this->details->map(fn($d) => [
                    'id'          => $d->id,
                    'description' => $d->description,
                ])
            ),
        ];
    }
}

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
            'id'             => $this->id,
            'periode'        => $this->periode,
            'status'         => $this->status,
            'sasaran'        => $this->sasaran,
            'perusahaan_id'  => $this->perusahaan_id,
            'unit_kerja_id'  => $this->unit_kerja_id,
            'type_id'        => $this->type_id,
            'risk_rating_id' => $this->risk_rating_id,
            'upgrade_reject' => $this->upgrade_reject,

            // Perusahaan (ref_org_structs)
            'perusahaan' => $this->when(
                $request->boolean('include_perusahaan') && $this->relationLoaded('perusahaan'),
                fn() => [
                    'id'   => $this->perusahaan?->id,
                    'code' => $this->perusahaan?->code,
                    'name' => $this->perusahaan?->name,
                ]
            ),

            // Unit Kerja (ref_org_structs)
            'unit_kerja' => $this->when(
                $request->boolean('include_unit_kerja') && $this->relationLoaded('unitKerja'),
                fn() => [
                    'id'   => $this->unitKerja?->id,
                    'code' => $this->unitKerja?->code,
                    'name' => $this->unitKerja?->name,
                ]
            ),
        ];
    }
}

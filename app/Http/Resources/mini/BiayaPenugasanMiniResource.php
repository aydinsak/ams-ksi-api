<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BiayaPenugasanMiniResource extends JsonResource
{
    public function toArray($request): array
    {
        $assignment = $this->relationLoaded('assignment') ? $this->assignment : null;
        $summary    = $assignment && $assignment->relationLoaded('summary') ? $assignment->summary : null;
        $rkia       = $summary && $summary->relationLoaded('rkia') ? $summary->rkia : null;

        return [
            'id'       => $this->id,
            'label'    => trim(collect([$this->no_sppd, $assignment?->letter_manual, $rkia?->year])->filter()->implode(' - ')),
            'no_sppd'  => $this->no_sppd,
            'tahun'    => $rkia?->year,
            'status'   => $this->status,
        ];
    }
}

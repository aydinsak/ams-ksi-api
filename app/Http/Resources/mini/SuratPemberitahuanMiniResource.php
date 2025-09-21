<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuratPemberitahuanMiniResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $summary = $this->relationLoaded('summary') ? $this->summary : null;
        $rkia    = $summary && $summary->relationLoaded('rkia') ? $summary->rkia : null;
        $objek   = $summary && $summary->relationLoaded('object') ? $summary->object : null;

        $label = trim(
            collect([
                $this->letter_manual,
                $objek?->name ? '– '.$objek->name : null,
                $rkia?->year  ? '(' . $rkia->year . ')' : null,
            ])->filter()->implode(' ')
        );

        return [
            'id'        => $this->id,
            'nomor'     => $this->letter_manual,
            'tanggal'   => $this->letter_date ? $this->letter_date->format('Y-m-d') : null,
            'tahun'     => $rkia->year ?? ($this->letter_date ? $this->letter_date->format('Y') : null),
            'status'    => $this->status,
            'rev'       => (int) ($this->version ?? 0),

            'label'     => $label,

            'object_id' => $objek?->id,
            'company_id'=> $rkia?->perusahaan_id,
        ];
    }
}

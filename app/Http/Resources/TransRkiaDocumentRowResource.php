<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransRkiaDocumentRowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tahun = optional($this->date_document)->format('Y');
        if (!$tahun && $this->relationLoaded('rkia') && $this->rkia && !empty($this->rkia->period)) {
            $tahun = (string) $this->rkia->period;
        }

        return [
            'id'       => $this->id,
            'tahun'    => $tahun,
            'no_pkat'  => $this->no_document,
            'tgl_pkat' => optional($this->date_document)->format('Y-m-d'),
            'rev'      => (int) $this->version,
            'status'   => $this->status,
        ];
    }
}

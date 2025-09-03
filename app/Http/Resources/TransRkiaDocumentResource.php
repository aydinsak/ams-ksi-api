<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransRkiaDocumentResource extends JsonResource
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
            'rkia_id'        => $this->rkia_id,
            'no_document'    => $this->no_document,
            'date_document'  => $this->date_document,
            'status'         => $this->status,
            'version'        => (int) $this->version,
            'upgrade_reject' => $this->upgrade_reject,

            'chapters' => [
                'first'   => $this->first_chapter,
                'second'  => $this->second_chapter,
                'third'   => $this->third_chapter,
                'fourth'  => $this->fourth_chapter,
                'fifth'   => $this->fifth_chapter,
                'sixth'   => $this->sixth_chapter,
                'seventh' => $this->seventh_chapter,
            ],

            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id'    => $this->creator->id,
                    'name'  => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),

            'updater' => $this->whenLoaded('updater', function () {
                return [
                    'id'    => $this->updater->id,
                    'name'  => $this->updater->name,
                    'email' => $this->updater->email,
                ];
            }),

            'rkia' => $this->whenLoaded('rkia', function () {
                return [
                    'id'            => $this->rkia->id,
                    'code'          => $this->rkia->code ?? null,
                    'period'        => $this->rkia->period ?? null,
                    'perusahaan_id' => $this->rkia->perusahaan_id ?? null,
                    'type_id'       => $this->rkia->type_id ?? null,
                    'date'          => $this->rkia->date ?? null,
                ];
            }),

            // aman walau relasi ccUsers() belum ada — akan jadi [] / null
            'cc_users' => $this->whenLoaded('ccUsers', function () {
                return $this->ccUsers
                    ->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])
                    ->values();
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

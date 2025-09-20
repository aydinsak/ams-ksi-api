<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramAuditResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $assignment = $this->rel($this, 'assignment');

        $summary = $assignment
            ? $this->rel($assignment, 'summary')
            : $this->rel($this, 'summary');

        $rkia  = $summary ? $this->rel($summary, 'rkia')   : null;
        $objek = $summary ? $this->rel($summary, 'object') : null;

        return [
            'id'             => $this->id,
            'no_program'     => $this->no_program,
            'date_program'   => optional($this->date_program)->format('Y-m-d'),
            'status'         => $this->status,
            'version'        => (int) $this->version,
            'assignment_id'  => $this->assignment_id,
            'summary_id'     => $this->summary_id,

            // kolom untuk tabel list
            'tahun'          => $rkia->year ?? null,
            'objek_audit'    => $objek->name ?? null,
            'surat_tugas'    => $assignment->letter_manual ?? null,
            'program_audit'  => $this->no_program,
            'auditor'        => $this->buildAuditors($assignment),
            'rev'            => (int) $this->version,

            'summary' => $this->when(
                $request->boolean('include_summary') && $summary,
                [
                    'id'         => $summary->id,
                    'type_audit' => ($this->rel($summary, 'type')->name ?? null),
                    'object'     => $objek->name ?? null,
                    'rkia'       => [
                        'id'   => $rkia->id   ?? null,
                        'year' => $rkia->year ?? null,
                    ],
                ]
            ),

            'details' => $this->when(
                $request->boolean('include_details') && $this->relationLoaded('details'),
                $this->details->map(fn($d) => [
                    'id'            => $d->id,
                    'no_kka'        => $d->no_kka,
                    'kka_title'     => $d->kka_title,
                    'langkah_kerja' => $d->langkah_kerja,
                    'tipe_sampel'   => $d->tipe_sampel,
                    'jumlah_sampel' => $d->jumlah_sampel,
                    'populasi'      => $d->populasi,
                    'keterangan'    => $d->keterangan,
                ])
            ),
        ];
    }

    private function rel($model, string $relation)
    {
        return $model && method_exists($model, 'relationLoaded') && $model->relationLoaded($relation)
            ? $model->{$relation}
            : null;
    }

    private function buildAuditors($assignment): ?array
    {
        if (!$assignment) {
            return null;
        }

        $leader = $this->rel($assignment, 'leader');
        $pic    = $this->rel($assignment, 'pic');

        return [
            'leader'  => $leader->name ?? null,
            'pic'     => $pic->name ?? null,
            'members' => $assignment->relationLoaded('members')
                ? $assignment->members->pluck('name')->values()
                : [],
            'users'   => $assignment->relationLoaded('users')
                ? $assignment->users->pluck('name')->values()
                : [],
        ];
    }
}

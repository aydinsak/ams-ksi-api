<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuratPemberitahuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $summary = $this->relationLoaded('summary') ? $this->summary : null;
        $rkia    = $summary && $summary->relationLoaded('rkia') ? $summary->rkia : null;
        $objek   = $summary && $summary->relationLoaded('object') ? $summary->object : null;
        $jenis   = $summary && $summary->relationLoaded('type') ? $summary->type : null;
        $perush  = $rkia && $rkia->relationLoaded('perusahaan') ? $rkia->perusahaan : null;

        $auditors = collect();
        if ($request->boolean('include_auditors')) {
            if ($this->relationLoaded('leader') && $this->leader)   $auditors->push(['id'=>$this->leader->id, 'name'=>$this->leader->name, 'role'=>'leader']);
            if ($this->relationLoaded('pic') && $this->pic)         $auditors->push(['id'=>$this->pic->id,    'name'=>$this->pic->name,    'role'=>'pic']);
            if ($this->relationLoaded('users'))   foreach ($this->users as $u)   $auditors->push(['id'=>$u->id, 'name'=>$u->name, 'role'=>'team']);
            if ($this->relationLoaded('members')) foreach ($this->members as $m) $auditors->push(['id'=>$m->id, 'name'=>$m->name, 'role'=>'member']);
        }

        return [
            'id'    => $this->id,
            'tahun' => $rkia->year ?? ($this->letter_date ? $this->letter_date->format('Y') : null),
            'surat' => [
                'nomor'   => $this->letter_manual,
                'tanggal' => $this->letter_date ? $this->letter_date->format('Y-m-d') : null,
            ],

            'rev'    => (int) ($this->version ?? 0),
            'status' => $this->status,

            'perusahaan' => $this->when(
                $request->boolean('include_perusahaan') && $perush,
                fn() => [
                    'id' => $perush->id,
                    'code' => $perush->code,
                    'name' => $perush->name,
                ]
            ),

            'objek_audit' => $this->when(
                $request->boolean('include_objek') && $objek,
                fn() => [
                    'id' => $objek->id,
                    'code' => $objek->code,
                    'name' => $objek->name,
                ]
            ),

            'jenis_audit' => $this->when(
                $request->boolean('include_jenis_audit') && $jenis,
                fn() => [
                    'id' => $jenis->id,
                    'name' => $jenis->name,
                ]
            ),

            'auditors' => $this->when(
                $request->boolean('include_auditors'),
                fn() => $auditors->unique('id')->values()
            ),
        ];
    }
}

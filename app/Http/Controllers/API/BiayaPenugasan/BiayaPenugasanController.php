<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BiayaPenugasanResource;
use App\Http\Resources\BiayaPenugasanMiniResource;
use App\Models\Pelaporan\TransBiayaPenugasan;
use App\Models\Pelaporan\TransBiayaPenugasanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class BiayaPenugasanController extends Controller
{
    /** GET /api/biaya-penugasan */
    public function index(Request $r)
    {
        $q            = $r->query('q');
        $status       = $r->query('status');
        $year         = $r->query('year');
        $perusahaanId = $r->query('perusahaan_id');
        $objectId     = $r->query('object_id');
        $typeId       = $r->query('type_id');      // jika diperlukan
        $auditorId    = $r->query('auditor_id');
        $perPage      = (int) $r->query('per_page', 15);

        $with = [
            'assignment' => fn($q) => $q->select('id','summary_id','letter_manual','letter_date')
                ->with([
                    'summary' => fn($s) => $s->select('id','rkia_id','object_id','type_id')
                        ->with(['rkia:id,year,perusahaan_id','object:id,name','type:id,name']),
                    'leader:id,name','pic:id,name','members:id,name','users:id,name',
                ]),
        ];
        if ($r->boolean('include_details') || $r->boolean('include_totals')) $with[] = 'details';

        $rows = TransBiayaPenugasan::query()
            ->with($with)
            ->when($q,      fn(Builder $qq) => $qq->where('no_sppd','like',"%{$q}%"))
            ->when($status, fn(Builder $qq) => $qq->where('status',$status))
            ->when($year || $perusahaanId || $objectId || $typeId, function (Builder $qq) use ($year,$perusahaanId,$objectId,$typeId) {
                $qq->whereHas('assignment.summary', function (Builder $w) use ($year,$perusahaanId,$objectId,$typeId) {
                    $w->when($objectId, fn(Builder $x) => $x->where('object_id',$objectId))
                      ->when($typeId,   fn(Builder $x) => $x->where('type_id', $typeId))
                      ->when($year || $perusahaanId, function (Builder $x) use ($year,$perusahaanId) {
                          $x->whereHas('rkia', function (Builder $z) use ($year,$perusahaanId) {
                              $z->when($year,         fn(Builder $y) => $y->where('year',$year))
                                ->when($perusahaanId, fn(Builder $y) => $y->where('perusahaan_id',$perusahaanId));
                          });
                      });
                });
            })
            ->when($auditorId, function (Builder $qq) use ($auditorId) {
                $qq->whereHas('assignment', function (Builder $a) use ($auditorId) {
                    $a->where(function (Builder $w) use ($auditorId) {
                        $w->where('leader_id',$auditorId)
                          ->orWhere('pic_id',$auditorId)
                          ->orWhereHas('members', fn(Builder $m)=>$m->where('sys_users.id',$auditorId))
                          ->orWhereHas('users',   fn(Builder $u)=>$u->where('sys_users.id',$auditorId));
                    });
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($r->query());

        if ($r->boolean('mini')) {
            $rows->loadMissing(['assignment.summary.rkia']);
            return BiayaPenugasanMiniResource::collection($rows);
        }
        return BiayaPenugasanResource::collection($rows);
    }

    /** GET /api/biaya-penugasan/{id} */
    public function show(Request $r, int $id)
    {
        $with = [
            'assignment' => fn($q) => $q->with([
                'summary.rkia','summary.object','summary.type','leader','pic','members','users'
            ]),
        ];
        if ($r->boolean('include_details') || $r->boolean('include_totals')) $with[] = 'details';

        $row = TransBiayaPenugasan::with($with)->findOrFail($id);
        return new BiayaPenugasanResource($row);
    }

    /** POST /api/biaya-penugasan */
    public function store(Request $r)
    {
        $data = $r->validate([
            'assignment_id' => ['required','integer','exists:trans_assignments,id'],
            'summary_id'    => ['nullable','integer','exists:trans_rkia_summary,id'],
            'no_sppd'       => ['nullable','string','max:255'],
            'date_sppd'     => ['nullable','date'],
            'status'        => ['nullable','string', Rule::in(['draft','proses','selesai','batal'])],
            'version'       => ['nullable','integer','min:0'],
        ]);

        if (empty($data['summary_id'])) {
            $data['summary_id'] = \App\Models\TransAssignment::findOrFail($data['assignment_id'])->summary_id;
        }

        $data['status']     = $data['status'] ?? 'draft';
        $data['version']    = $data['version'] ?? 0;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $row = TransBiayaPenugasan::create($data);

        return response()->json([
            'message' => 'Biaya Penugasan dibuat',
            'data'    => new BiayaPenugasanResource($row->load('assignment.summary.rkia')),
        ], 201);
    }

    /** PATCH /api/biaya-penugasan/{id} */
    public function update(Request $r, int $id)
    {
        $row = TransBiayaPenugasan::findOrFail($id);

        $data = $r->validate([
            'no_sppd'   => ['nullable','string','max:255'],
            'date_sppd' => ['nullable','date'],
            'status'    => ['nullable','string', Rule::in(['draft','proses','selesai','batal'])],
            'version'   => ['nullable','integer','min:0'],
        ]);

        $data['updated_by'] = Auth::id();
        $row->update($data);

        return response()->json([
            'message' => 'Biaya Penugasan diperbarui',
            'data'    => new BiayaPenugasanResource($row->fresh()->load('assignment.summary.rkia')),
        ]);
    }

    /** ========== DETAILS (opsional CRUD baris biaya) ========== */

    // POST /api/biaya-penugasan/{id}/details
    public function addDetail(Request $r, int $id)
    {
        $biaya = TransBiayaPenugasan::findOrFail($id);
        $data = $r->validate([
            'user_id'    => ['nullable','integer','exists:sys_users,id'],
            'komponen'   => ['nullable','string','max:100'],
            'keterangan' => ['nullable','string'],
            'qty'        => ['nullable','integer','min:1'],
            'amount'     => ['nullable','numeric','min:0'],
        ]);
        $data['qty']      = $data['qty'] ?? 1;
        $data['amount']   = $data['amount'] ?? 0;
        $data['subtotal'] = $data['qty'] * $data['amount'];
        $data['biaya_id'] = $biaya->id;

        $detail = TransBiayaPenugasanDetail::create($data);
        return response()->json(['message'=>'Detail ditambahkan', 'data'=>$detail], 201);
    }

    // PATCH /api/biaya-penugasan/{id}/details/{detailId}
    public function updateDetail(Request $r, int $id, int $detailId)
    {
        $detail = TransBiayaPenugasanDetail::where('biaya_id',$id)->findOrFail($detailId);
        $data = $r->validate([
            'user_id'    => ['nullable','integer','exists:sys_users,id'],
            'komponen'   => ['nullable','string','max:100'],
            'keterangan' => ['nullable','string'],
            'qty'        => ['nullable','integer','min:1'],
            'amount'     => ['nullable','numeric','min:0'],
        ]);
        if (isset($data['qty']) || isset($data['amount'])) {
            $qty    = $data['qty']    ?? $detail->qty;
            $amount = $data['amount'] ?? $detail->amount;
            $data['subtotal'] = $qty * $amount;
        }
        $detail->update($data);
        return response()->json(['message'=>'Detail diperbarui', 'data'=>$detail->fresh()]);
    }

    // DELETE /api/biaya-penugasan/{id}/details/{detailId}
    public function deleteDetail(int $id, int $detailId)
    {
        TransBiayaPenugasanDetail::where('biaya_id',$id)->findOrFail($detailId)->delete();
        return response()->json(['message'=>'Detail dihapus']);
    }
}

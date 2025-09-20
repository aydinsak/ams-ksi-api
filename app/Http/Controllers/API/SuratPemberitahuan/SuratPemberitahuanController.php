<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuratPemberitahuanResource;
use App\Models\TransAssignment;
use Illuminate\Http\Request;

class SuratPemberitahuanController extends Controller
{
    public function index(Request $request)
    {
        $q            = $request->string('q');
        $year         = $request->input('year');
        $perusahaanId = $request->input('perusahaan_id');
        $typeId       = $request->input('type_id');
        $objectId     = $request->input('object_id');
        $auditorId    = $request->input('auditor_id');
        $status       = $request->string('status');
        $perPage      = (int) $request->input('per_page', 15);

        $with = ['summary.rkia'];
        if ($request->boolean('include_perusahaan')) {
            $with[] = 'summary.rkia.perusahaan:id,code,name';
        }
        if ($request->boolean('include_objek')) {
            $with[] = 'summary.object:id,code,name';
        }
        if ($request->boolean('include_jenis_audit')) {
            $with[] = 'summary.type:id,name';
        }
        if ($request->boolean('include_auditors')) {
            $with[] = 'leader:id,name';
            $with[] = 'pic:id,name';
            $with[] = 'members:id,name';
            $with[] = 'users:id,name';
        }

        $query = TransAssignment::query()
            ->with($with)
            ->join('trans_rkia_summary AS s', 's.id', '=', 'trans_assignments.summary_id')
            ->join('trans_rkia AS r', 'r.id', '=', 's.rkia_id')
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('trans_assignments.letter_manual', 'like', "%{$q}%")
                      ->orWhere('r.no_audit_plan', 'like', "%{$q}%");
                });
            })
            ->when($year,         fn($qq) => $qq->where('r.year', $year))
            ->when($perusahaanId, fn($qq) => $qq->where('r.perusahaan_id', $perusahaanId))
            ->when($typeId,       fn($qq) => $qq->where('s.type_id', $typeId))
            ->when($objectId,     fn($qq) => $qq->where('s.object_id', $objectId))
            ->when($request->filled('status'), fn($qq) => $qq->where('trans_assignments.status', $status))
            ->when($auditorId, function ($qq) use ($auditorId) {
                $qq->where(function ($w) use ($auditorId) {
                    $w->where('trans_assignments.leader_id', $auditorId)
                      ->orWhere('trans_assignments.pic_id', $auditorId)
                      ->orWhereExists(function ($sub) use ($auditorId) {
                          $sub->selectRaw(1)
                              ->from('trans_assignments_members AS m')
                              ->whereColumn('m.assignment_id', 'trans_assignments.id')
                              ->where('m.user_id', $auditorId);
                      })
                      ->orWhereExists(function ($sub) use ($auditorId) {
                          $sub->selectRaw(1)
                              ->from('trans_assignments_users AS u')
                              ->whereColumn('u.assignment_id', 'trans_assignments.id')
                              ->where('u.user_id', $auditorId);
                      });
                });
            })
            ->orderByDesc('trans_assignments.id')
            ->select('trans_assignments.*');

        $page = $query->paginate($perPage)->appends($request->query());

        return SuratPemberitahuanResource::collection($page);
    }

    public function show(Request $request, int $id)
    {
        $with = ['summary.rkia'];
        if ($request->boolean('include_perusahaan')) {
            $with[] = 'summary.rkia.perusahaan:id,code,name';
        }
        if ($request->boolean('include_objek')) {
            $with[] = 'summary.object:id,code,name';
        }
        if ($request->boolean('include_jenis_audit')) {
            $with[] = 'summary.type:id,name';
        }
        if ($request->boolean('include_auditors')) {
            $with = array_merge($with, [
                'leader:id,name',
                'pic:id,name',
                'members:id,name',
                'users:id,name',
            ]);
        }

        $record = TransAssignment::with($with)->findOrFail($id);

        return new SuratPemberitahuanResource($record);
    }
}

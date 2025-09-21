<?php

namespace App\Http\Controllers\API\Program_Audit;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramAuditResource;
use App\Models\ProgramAudit\TransProgram;
use App\Models\ProgramAudit\TransProgramDetail;
use App\Models\TransAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProgramAuditController extends Controller
{
    public function index(Request $r)
    {
        $q            = (string) $r->query('q', '');
        $status       = $r->query('status');
        $year         = $r->query('year');
        $perusahaanId = $r->query('perusahaan_id');
        $objectId     = $r->query('object_id');
        $typeId       = $r->query('type_id');
        $auditorId    = $r->query('auditor_id');
        $perPage      = (int) $r->query('per_page', 15);

        $with = [
            'assignment' => fn($q) => $q->select('id','summary_id','no_assignment','date_assignment')
                ->with([
                    'summary' => fn($w) => $w->select('id','rkia_id','object_id','type_audit_id')
                        ->with(['rkia:id,year,perusahaan_id','object:id,name','typeAudit:id,name']),
                    'leader:id,name', 'pic:id,name', 'members:id,name', 'users:id,name',
                ]),
        ];

        $data = TransProgram::query()
            ->with($with)
            ->when($q, fn($qq) => $qq->where('no_program','like',"%$q%"))
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($year || $perusahaanId || $objectId || $typeId, function ($qq) use ($year,$perusahaanId,$objectId,$typeId) {
                $qq->whereHas('assignment.summary', function ($w) use ($year,$perusahaanId,$objectId,$typeId) {
                    if ($objectId) $w->where('object_id', $objectId);
                    if ($typeId)   $w->where('type_audit_id', $typeId);
                    if ($year || $perusahaanId) {
                        $w->whereHas('rkia', function ($x) use ($year,$perusahaanId) {
                            if ($year)         $x->where('year', $year);
                            if ($perusahaanId) $x->where('perusahaan_id', $perusahaanId);
                        });
                    }
                });
            })
            ->when($auditorId, fn($qq) => $qq->whereHas('assignment.users', fn($w) => $w->where('sys_users.id', $auditorId)))
            ->orderByDesc('id')
            ->paginate($perPage);

        return ProgramAuditResource::collection($data);
    }

    public function show(Request $r, $id)
    {
        $with = [
            'assignment' => fn($q) => $q->with(['summary.rkia','summary.object','summary.typeAudit','leader','pic','members','users']),
        ];
        if ($r->boolean('include_details')) $with[] = 'details';

        $row = TransProgram::with($with)->findOrFail($id);
        return new ProgramAuditResource($row);
    }
}

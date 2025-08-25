<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\RiskAssessmentResource;
use App\Models\Penilaian_Resiko\TransRiskAssessmentRegister;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiskAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $q            = $request->string('q');
        $status       = $request->string('status');
        $perusahaanId = $request->input('perusahaan_id');
        $typeId       = $request->input('type_id');
        $objectId     = $request->input('object_id');
        $period       = $request->string('period');
        $perPage      = (int) $request->input('per_page', 15);

        $with = [];
        if ($request->boolean('include_perusahaan')) $with[] = 'perusahaan:id,code,name';
        if ($request->boolean('include_object'))     $with[] = 'object:id,code,name';
        if ($request->boolean('include_type'))       $with[] = 'type:id,name';
        if ($request->boolean('include_details'))    $with[] = 'details';

        $data = TransRiskAssessmentRegister::query()
            ->when(!empty($with), fn($q) => $q->with($with))
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('code', 'like', "%$q%")
                    ->orWhere('period', 'like', "%$q%")
                    ->orWhere('sasaran', 'like', "%$q%");
            }))
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($period, fn($qq) => $qq->where('period', $period))
            ->when($perusahaanId, fn($qq) => $qq->where('perusahaan_id', $perusahaanId))
            ->when($typeId, fn($qq) => $qq->where('type_id', $typeId))
            ->when($objectId, fn($qq) => $qq->where('object_id', $objectId))
            ->orderByDesc('id')
            ->paginate($perPage);

        return RiskAssessmentResource::collection($data);
    }

    public function show(Request $request, $id)
    {
        $with = [];
        if ($request->boolean('include_perusahaan')) $with[] = 'perusahaan:id,code,name';
        if ($request->boolean('include_object'))     $with[] = 'object:id,code,name';
        if ($request->boolean('include_type'))       $with[] = 'type:id,name';
        if ($request->boolean('include_details'))    $with[] = 'details';

        $row = TransRiskAssessmentRegister::with($with)->findOrFail($id);

        return new RiskAssessmentResource($row);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'perusahaan_id' => ['required', 'integer', 'exists:ref_org_structs,id'],
            'period'        => ['required', 'string', 'max:20'],
            'type_id'       => ['required', 'integer', 'exists:ref_type_audit,id'],
            'object_id'     => ['required', 'integer', 'exists:ref_org_structs,id'],
            'sasaran'       => ['nullable', 'string'],
            'status'        => ['nullable', 'string', 'max:30'],
            'rev'           => ['nullable', 'integer'],
            'code'          => ['nullable', 'string', 'max:50', Rule::unique('trans_risk_assessment_register', 'code')],
        ]);

        $data['created_by'] = $request->user()->id ?? null;
        $row = TransRiskAssessmentRegister::create($data);

        return response()->json([
            'message' => 'Penilaian risiko berhasil dibuat',
            'data'    => new RiskAssessmentResource($row),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $row = TransRiskAssessmentRegister::findOrFail($id);

        $data = $request->validate([
            'perusahaan_id' => ['sometimes', 'integer', 'exists:ref_org_structs,id'],
            'period'        => ['sometimes', 'string', 'max:20'],
            'type_id'       => ['sometimes', 'integer', 'exists:ref_type_audit,id'],
            'object_id'     => ['sometimes', 'integer', 'exists:ref_org_structs,id'],
            'sasaran'       => ['sometimes', 'nullable', 'string'],
            'status'        => ['sometimes', 'nullable', 'string', 'max:30'],
            'rev'           => ['sometimes', 'nullable', 'integer'],
            'code'          => ['sometimes', 'nullable', 'string', 'max:50', Rule::unique('trans_risk_assessment_register', 'code')->ignore($row->id)],
        ]);

        $data['updated_by'] = $request->user()->id ?? null;
        $row->update($data);

        return response()->json([
            'message' => 'Penilaian risiko diperbarui',
            'data'    => new RiskAssessmentResource($row->fresh()),
        ]);
    }

    public function destroy($id)
    {
        $row = TransRiskAssessmentRegister::findOrFail($id);
        $row->delete();

        return response()->json(['message' => 'Penilaian risiko dihapus']);
    }
}

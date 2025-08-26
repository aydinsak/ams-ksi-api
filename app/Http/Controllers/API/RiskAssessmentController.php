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
        $unitKerjaId     = $request->input('unit_kerja_id');
        $periode       = $request->string('periode');
        $perPage      = (int) $request->input('per_page', 15);

        $with = [];
        if ($request->boolean('include_perusahaan')) $with[] = 'perusahaan:id,code,name';
        if ($request->boolean('include_unit_kerja'))     $with[] = 'unitKerja:id,code,name';

        $data = TransRiskAssessmentRegister::query()
            ->when(!empty($with), fn($q) => $q->with($with))
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('sasaran', 'like', "%$q%")
                    ->orWhere('status', 'like', "%$q%")
                    ->orWhere('periode', 'like', "%$q%");
            }))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $status))
            ->when($request->filled('periode'), fn($qq) => $qq->where('periode', $periode))
            ->when($perusahaanId, fn($qq) => $qq->where('perusahaan_id', $perusahaanId))
            ->when($typeId,       fn($qq) => $qq->where('type_id', $typeId))
            ->when($unitKerjaId,  fn($qq) => $qq->where('unit_kerja_id', $unitKerjaId))
            ->orderByDesc('id')
            ->paginate($perPage);

        return RiskAssessmentResource::collection($data);
    }

    public function show(Request $request, $id)
    {
        $with = [];
        if ($request->boolean('include_perusahaan')) $with[] = 'perusahaan:id,code,name';
        if ($request->boolean('include_unit_kerja')) $with[] = 'unitKerja:id,code,name';

        $row = TransRiskAssessmentRegister::with($with)->findOrFail($id);

        return new RiskAssessmentResource($row);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'perusahaan_id' => ['required', 'integer', 'exists:ref_org_structs,id'],
            'periode'       => ['required', 'date'],
            'type_id'       => ['nullable', 'integer'],
            'unit_kerja_id' => ['required', 'integer', 'exists:ref_org_structs,id'],
            'sasaran'       => ['nullable', 'string'],
            'status'        => ['nullable', 'string', 'max:30'],
            'version'       => ['nullable', 'integer'],
            'risk_rating_id' => ['nullable', 'integer'],
            'upgrade_reject' => ['nullable', 'string'],
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
            'periode'       => ['sometimes', 'date'],
            'type_id'       => ['sometimes', 'nullable', 'integer'],
            'unit_kerja_id' => ['sometimes', 'integer', 'exists:ref_org_structs,id'],
            'sasaran'       => ['sometimes', 'nullable', 'string'],
            'status'        => ['sometimes', 'nullable', 'string', 'max:30'],
            'version'       => ['sometimes', 'nullable', 'integer'],
            'risk_rating_id' => ['sometimes', 'nullable', 'integer'],
            'upgrade_reject' => ['sometimes', 'nullable', 'string'],
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

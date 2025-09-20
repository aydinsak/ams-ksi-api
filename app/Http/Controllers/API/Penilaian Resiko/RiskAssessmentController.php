<?php

namespace App\Http\Controllers\API\Penilaian_Resiko;

use App\Http\Controllers\Controller;
use App\Http\Resources\RiskAssessmentResource;
use App\Models\Penilaian_Resiko\TransRiskAssessmentRegister;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiskAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $perPage      = (int) $request->input('per_page', 15);
        $query = TransRiskAssessmentRegister::with([
            'perusahaan',
            'unitKerja',
            'creator',
            'updater',
        ])->orderByDesc('id');

        return response()->json($query->paginate($perPage));
    }

    public function show($id)
    {
        $row = TransRiskAssessmentRegister::with([
            'perusahaan',
            'unitKerja',
            'creator',
            'updater',
            'details'
        ])->findOrFail($id);

        return response()->json($row);
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

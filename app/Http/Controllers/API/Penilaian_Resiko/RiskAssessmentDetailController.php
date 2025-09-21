<?php

namespace App\Http\Controllers\API\Penilaian_Resiko;

use App\Http\Controllers\Controller;
use App\Models\Penilaian_Resiko\TransRiskAssessmentRegisterDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class RiskAssessmentDetailController extends Controller
{
    public function index(Request $request, $riskRegisterId)
    {
        $perPage = (int) $request->query('per_page', 15);

        $query = TransRiskAssessmentRegisterDetail::with(['register', 'riskType', 'riskCode', 'creator', 'updater'])
            ->where('risk_register_id', $riskRegisterId);

        return response()->json($query->paginate($perPage));
    }

    public function show($riskRegisterId, $detailId)
    {
        $detail = TransRiskAssessmentRegisterDetail::with(['register', 'riskType', 'riskCode', 'creator', 'updater'])
            ->where('risk_register_id', $riskRegisterId)
            ->whereKey($detailId)
            ->firstOrFail();

        return response()->json($detail);
    }

    public function store(Request $request, $riskRegisterId)
    {
        $rules = [
            'id_resiko' => [
                'required',
                Rule::unique('trans_risk_assessment_register_detail', 'id_resiko')
                    ->where(fn($q) => $q->where('risk_register_id', $riskRegisterId)),
            ],
            'nama_resiko' => ['required', 'string', 'max:255'],
            'peristiwa'   => ['required', 'string'],
            'penyebab'    => ['required', 'string'],
            'dampak'      => ['required', 'string'],
        ];

        if (Schema::hasColumn('trans_risk_assessment_register_detail', 'description')) {
            $rules['description'] = ['nullable', 'string'];
        }
        if (Schema::hasColumn('trans_risk_assessment_register_detail', 'source')) {
            $rules['source'] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);
        $validated['risk_register_id'] = (int) $riskRegisterId;
        $validated['created_by'] = Auth::id();

        $detail = TransRiskAssessmentRegisterDetail::create($validated);

        return response()->json(
            $detail->load(['register', 'riskType', 'riskCode', 'creator', 'updater']),
            201
        );
    }

    public function update(Request $request, $riskRegisterId, $detailId)
    {
        $detail = TransRiskAssessmentRegisterDetail::where('risk_register_id', $riskRegisterId)
            ->whereKey($detailId)
            ->firstOrFail();

        $rules = [
            'nama_resiko' => ['required', 'string', 'max:255'],
            'peristiwa'   => ['required', 'string'],
            'penyebab'    => ['required', 'string'],
            'dampak'      => ['required', 'string'],
        ];

        if (Schema::hasColumn('trans_risk_assessment_register_detail', 'description')) {
            $rules['description'] = ['nullable', 'string'];
        }
        if (Schema::hasColumn('trans_risk_assessment_register_detail', 'source')) {
            $rules['source'] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);
        $validated['updated_by'] = Auth::id();

        unset($validated['id_resiko'], $validated['risk_register_id']);

        $detail->update($validated);

        return response()->json(
            $detail->load(['register', 'riskType', 'riskCode', 'creator', 'updater'])
        );
    }

    public function destroy($riskRegisterId, $detailId)
    {
        $detail = TransRiskAssessmentRegisterDetail::where('risk_register_id', $riskRegisterId)
            ->whereKey($detailId)
            ->firstOrFail();

        $detail->delete();

        return response()->json(
            ['message' => 'Detail penilaian risiko dihapus']
        );
    }
}

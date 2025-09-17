<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PKAT\TransRkia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TransRkiaController extends Controller
{
    public function index(Request $request)
    {
        $q            = (string) $request->query('q', '');
        $year         = $request->query('year');
        $perusahaanId = $request->query('perusahaan_id');
        $status       = $request->query('status');
        $perPage      = (int) $request->query('per_page', 15);

        $query = TransRkia::query()
            ->with(['perusahaan', 'creator', 'updater', 'pic'])
            ->when($q, fn($qq) => $qq->where(fn($w) => $w
                ->where('no_audit_plan', 'like', "%$q%")
                ->orWhere('closing', 'like', "%$q%")))
            ->when($year, fn($qq) => $qq->where('year', $year))
            ->when($perusahaanId, fn($qq) => $qq->where('perusahaan_id', $perusahaanId))
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->orderByDesc('id');

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year'           => ['required', 'string', 'size:4'],
            'perusahaan_id'  => ['required', 'integer', 'exists:ref_org_structs,id'],
            'date_audit_plan' => ['nullable', 'date'],
            'closing'        => ['nullable', 'string'],
            'version'        => ['nullable', 'integer', 'min:0'],
            'pic_id'         => ['nullable', 'integer', 'exists:sys_users,id'],
        ]);

        $data['status']     = 'new';
        $data['version']    = $data['version'] ?? 0;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        // generate no_audit_plan
        $data['no_audit_plan'] = $request->input('no_audit_plan') ?: $this->generateNoAuditPlan($data['year'], $data['perusahaan_id']);

        $pkat = TransRkia::create($data);

        return response()->json([
            'message' => 'Created',
            'data'    => $pkat->load(['perusahaan', 'creator', 'updater', 'pic']),
        ], 201);
    }

    public function show(TransRkia $pkat)
    {
        return response()->json($pkat->load(['perusahaan', 'creator', 'updater', 'pic']));
    }

    public function update(Request $request, TransRkia $pkat)
    {
        $data = $request->validate([
            'year'           => ['sometimes', 'string', 'size:4'],
            'perusahaan_id'  => ['sometimes', 'integer', 'exists:ref_org_structs,id'],
            'date_audit_plan' => ['nullable', 'date'],
            'closing'        => ['nullable', 'string'],
            'version'        => ['nullable', 'integer', 'min:0'],
            'pic_id'         => ['nullable', 'integer', 'exists:sys_users,id'],
            'status'         => ['nullable', 'string', Rule::in(['new', 'draft', 'shared', 'approved', 'rejected'])],
            'no_audit_plan'  => ['nullable', 'string', 'max:255'],
        ]);

        // regenerate no_audit_plan
        if (($data['year'] ?? null) && empty($data['no_audit_plan'])) {
            $data['no_audit_plan'] = $this->generateNoAuditPlan($data['year'], $data['perusahaan_id'] ?? $pkat->perusahaan_id);
        }

        $data['updated_by'] = Auth::id();

        $pkat->update($data);

        return response()->json([
            'message' => 'Updated',
            'data'    => $pkat->fresh()->load(['perusahaan', 'creator', 'updater', 'pic']),
        ]);
    }

    public function destroy(TransRkia $pkat)
    {
        $pkat->delete();
        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Helper generate nomor PKAT otomatis.
     * PKAT/{YEAR}/{PERUSAHAAN_ID}/{running-number 4 digit}
     */
    protected function generateNoAuditPlan(string $year, int $perusahaanId): string
    {
        $countThisYear = TransRkia::where('year', $year)->where('perusahaan_id', $perusahaanId)->count();
        $seq = str_pad((string) ($countThisYear + 1), 4, '0', STR_PAD_LEFT);
        return "PKAT/{$year}/{$perusahaanId}/{$seq}";
    }
}

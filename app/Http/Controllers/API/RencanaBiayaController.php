<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Rencana_Biaya\TransRencanaBiaya;
use App\Models\Rencana_Biaya\TransRencanaBiayaDetail;
use App\Models\Rencana_Biaya\TransRencanaBiayaAktiva;
use App\Models\PKAT\TransRkia;

class RencanaBiayaController extends Controller
{
    /**
     * GET /api/rencana-biaya
     * Filter: year, perusahaan_id, status, q, per_page
     * Output: paginated list + aggregate total_biaya per rencana
     */
    public function index(Request $request)
    {
        $year         = $request->query('year');
        $perusahaanId = $request->query('perusahaan_id');
        $status       = $request->query('status');
        $q            = (string) $request->query('q', '');
        $perPage      = (int) $request->query('per_page', 15);

        $query = TransRencanaBiaya::query()
            ->with(['rkia' => function ($q) {
                $q->select('id', 'perusahaan_id', 'year', 'no_audit_plan');
            }])
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($q, fn($qq) => $qq->where('description', 'like', "%$q%"))
            // join ke trans_rkia untuk filter tahun & perusahaan
            ->when($year || $perusahaanId, function ($qq) use ($year, $perusahaanId) {
                $qq->whereHas('rkia', function ($w) use ($year, $perusahaanId) {
                    if ($year)         $w->where('year', $year);
                    if ($perusahaanId) $w->where('perusahaan_id', $perusahaanId);
                });
            })
            ->orderByDesc('id');


        $page = $query->paginate($perPage);

        $page->getCollection()->transform(function ($item) {
            $detail = TransRencanaBiayaDetail::where('rencana_biaya_id', $item->id)->get(['tw_1', 'tw_2', 'tw_3', 'tw_4', 'jumlah_unit', 'harga_unit']);
            $aktiva = TransRencanaBiayaAktiva::where('rencana_biaya_id', $item->id)->get(['qty', 'harga_satuan']);

            $sumDetail = 0;
            foreach ($detail as $d) {
                $ju = is_numeric($d->jumlah_unit) ? (float)$d->jumlah_unit : 0;
                $hu = is_numeric($d->harga_unit)  ? (float)$d->harga_unit  : 0;
                $byJumlahHarga = $ju * $hu;

                $tw1 = is_numeric($d->tw_1) ? (float)$d->tw_1 : 0;
                $tw2 = is_numeric($d->tw_2) ? (float)$d->tw_2 : 0;
                $tw3 = is_numeric($d->tw_3) ? (float)$d->tw_3 : 0;
                $tw4 = is_numeric($d->tw_4) ? (float)$d->tw_4 : 0;
                $byTriwulan = $tw1 + $tw2 + $tw3 + $tw4;

                $sumDetail += max($byJumlahHarga, $byTriwulan); // pilih skema isi yang dipakai
            }

            $sumAktiva = 0;
            foreach ($aktiva as $a) {
                $qty   = is_numeric($a->qty) ? (float)$a->qty : 0;
                $harga = is_numeric($a->harga_satuan) ? (float)$a->harga_satuan : 0;
                $sumAktiva += $qty * $harga;
            }

            $item->total_biaya = $sumDetail + $sumAktiva;
            $item->tahun = $item->rkia->year ?? null;
            $item->pkat  = $item->rkia->no_audit_plan ?? null;
            return $item;
        });

        return response()->json($page);
    }

    /**
     * POST /api/rencana-biaya
     * Body: rkia_id, description?, status?, version?
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'rkia_id'     => ['required', 'integer', 'exists:trans_rkia,id'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'string', Rule::in(['new', 'draft', 'shared', 'approved', 'rejected'])],
            'version'     => ['nullable', 'integer', 'min:0'],
        ]);

        $data['status']     = $data['status'] ?? 'new';
        $data['version']    = $data['version'] ?? 0;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $rb = TransRencanaBiaya::create($data);

        return response()->json(['message' => 'Created', 'data' => $rb->load('rkia')], 201);
    }

    /**
     * GET /api/rencana-biaya/{id}
     * return header + details + aktiva
     */
    public function show($id)
    {
        $rb = TransRencanaBiaya::with('rkia')->findOrFail($id);

        $details = TransRencanaBiayaDetail::where('rencana_biaya_id', $id)->with(['jenisBiaya', 'komponenBiaya'])->get();
        $aktiva  = TransRencanaBiayaAktiva::where('rencana_biaya_id', $id)->with(['jenisAktiva'])->get();

        return response()->json([
            'data' => [
                'header'  => $rb,
                'details' => $details,
                'aktiva'  => $aktiva,
            ],
        ]);
    }

    /**
     * PATCH /api/rencana-biaya/{id}
     */
    public function update(Request $request, $id)
    {
        $rb = TransRencanaBiaya::findOrFail($id);

        $data = $request->validate([
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'string', Rule::in(['new', 'draft', 'shared', 'approved', 'rejected'])],
            'version'     => ['nullable', 'integer', 'min:0'],
        ]);

        $data['updated_by'] = Auth::id();
        $rb->update($data);

        return response()->json(['message' => 'Updated', 'data' => $rb->fresh('rkia')]);
    }

    /**
     * DELETE /api/rencana-biaya/{id}
     */
    public function destroy($id)
    {
        $rb = TransRencanaBiaya::findOrFail($id);
        $rb->delete();
        return response()->json(['message' => 'Deleted']);
    }

    /* ===================== DETAILS ===================== */

    /**
     * POST /api/rencana-biaya/{id}/details
     * Body minimal: komponen_biaya_id
     */
    public function addDetail(Request $request, $id)
    {
        $rb = TransRencanaBiaya::findOrFail($id);

        $data = $request->validate([
            'jenis_biaya_id'    => ['nullable', 'integer', 'exists:ref_cost_type,id'],
            'komponen_biaya_id' => ['required', 'integer', 'exists:ref_cost_component,id'],
            'tw_1'              => ['nullable'],
            'tw_2'              => ['nullable'],
            'tw_3'              => ['nullable'],
            'tw_4'              => ['nullable'],
            'jumlah_unit'       => ['nullable'],
            'harga_unit'        => ['nullable'],
            'keterangan'        => ['nullable', 'string'],
            'bulan'             => ['nullable', 'date'],
        ]);

        $data['rencana_biaya_id'] = $rb->id;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $detail = TransRencanaBiayaDetail::create($data);

        return response()->json(['message' => 'Detail Added', 'data' => $detail], 201);
    }

    /**
     * PATCH /api/rencana-biaya/{id}/details/{detailId}
     */
    public function updateDetail(Request $request, $id, $detailId)
    {
        $rb = TransRencanaBiaya::findOrFail($id);
        $detail = TransRencanaBiayaDetail::where('rencana_biaya_id', $rb->id)->findOrFail($detailId);

        $data = $request->validate([
            'jenis_biaya_id'    => ['nullable', 'integer', 'exists:ref_cost_type,id'],
            'komponen_biaya_id' => ['nullable', 'integer', 'exists:ref_cost_component,id'],
            'tw_1'              => ['nullable'],
            'tw_2'              => ['nullable'],
            'tw_3'              => ['nullable'],
            'tw_4'              => ['nullable'],
            'jumlah_unit'       => ['nullable'],
            'harga_unit'        => ['nullable'],
            'keterangan'        => ['nullable', 'string'],
            'bulan'             => ['nullable', 'date'],
        ]);

        $data['updated_by'] = Auth::id();
        $detail->update($data);

        return response()->json(['message' => 'Detail Updated', 'data' => $detail->fresh()]);
    }

    /**
     * DELETE /api/rencana-biaya/{id}/details/{detailId}
     */
    public function deleteDetail($id, $detailId)
    {
        $rb = TransRencanaBiaya::findOrFail($id);
        $detail = TransRencanaBiayaDetail::where('rencana_biaya_id', $rb->id)->findOrFail($detailId);
        $detail->delete();

        return response()->json(['message' => 'Detail Deleted']);
    }

    /* ===================== AKTIVA ===================== */

    /**
     * POST /api/rencana-biaya/{id}/aktiva
     */
    public function addAktiva(Request $request, $id)
    {
        $rb = TransRencanaBiaya::findOrFail($id);

        $data = $request->validate([
            'jenis_aktiva_id' => ['nullable', 'integer', 'exists:ref_asset_type,id'],
            'qty'             => ['required', 'numeric', 'min:0'],
            'realisasi'       => ['required', 'string'],
            'harga_satuan'    => ['required', 'numeric', 'min:0'],
            'keterangan'      => ['nullable', 'string'],
        ]);

        $data['rencana_biaya_id'] = $rb->id;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $row = TransRencanaBiayaAktiva::create($data);

        return response()->json(['message' => 'Aktiva Added', 'data' => $row], 201);
    }

    /**
     * PATCH /api/rencana-biaya/{id}/aktiva/{rowId}
     */
    public function updateAktiva(Request $request, $id, $rowId)
    {
        $rb = TransRencanaBiaya::findOrFail($id);
        $row = TransRencanaBiayaAktiva::where('rencana_biaya_id', $rb->id)->findOrFail($rowId);

        $data = $request->validate([
            'jenis_aktiva_id' => ['nullable', 'integer', 'exists:ref_asset_type,id'],
            'qty'             => ['nullable', 'numeric', 'min:0'],
            'realisasi'       => ['nullable', 'string'],
            'harga_satuan'    => ['nullable', 'numeric', 'min:0'],
            'keterangan'      => ['nullable', 'string'],
        ]);

        $data['updated_by'] = Auth::id();
        $row->update($data);

        return response()->json(['message' => 'Aktiva Updated', 'data' => $row->fresh()]);
    }

    /**
     * DELETE /api/rencana-biaya/{id}/aktiva/{rowId}
     */
    public function deleteAktiva($id, $rowId)
    {
        $rb = TransRencanaBiaya::findOrFail($id);
        $row = TransRencanaBiayaAktiva::where('rencana_biaya_id', $rb->id)->findOrFail($rowId);
        $row->delete();

        return response()->json(['message' => 'Aktiva Deleted']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Dokumen_PKAT\TransRkiaDocument;

class TransRkiaDocumentController extends Controller
{
    public function index(Request $request)
    {
        $tahun         = $request->query('tahun');
        $jenisAuditId  = $request->query('jenis_audit_id');
        $perusahaanId  = $request->query('perusahaan_id');
        $perPage       = (int) $request->query('per_page', 15);

        $query = TransRkiaDocument::query()
            ->when($tahun, fn($q) => $q->where('tahun', $tahun))
            ->when($jenisAuditId, fn($q) => $q->where('jenis_audit_id', $jenisAuditId))
            ->when($perusahaanId, fn($q) => $q->where('perusahaan_id', $perusahaanId))
            ->orderByDesc('tgl_pkat')
            ->orderByDesc('id');

        $paginator = $query->paginate($perPage)->through(function ($m) {
            return [
                'id'       => $m->id,
                'tahun'    => (int) $m->tahun,
                'no_pkat'  => $m->no_pkat,
                'tgl_pkat' => optional($m->tgl_pkat)->format('Y-m-d'),
                'rev'      => $m->rev,
                'status'   => $m->status,         // kalau mau teks, tambahkan accessor getStatusTextAttribute di model
            ];
        });

        return response()->json($paginator);
    }
}

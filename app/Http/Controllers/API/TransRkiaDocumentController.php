<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Dokumen_PKAT\TransRkiaDocument;
use App\Http\Resources\TransRkiaDocumentResource;
use App\Http\Resources\TransRkiaDocumentRowResource;

class TransRkiaDocumentController extends Controller
{
    public function index(Request $request)
    {
        $q            = (string) $request->query('q', '');
        $year         = $request->query('year');
        $status       = $request->query('status');
        $perusahaanId = $request->query('perusahaan_id');
        $jenisAudit   = $request->query('jenis_audit');
        $perPage      = (int) $request->query('per_page', 15);

        $query = TransRkiaDocument::query()
            ->with([
                'creator:id,name,email',
                'updater:id,name,email',
                'rkia',
            ])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('no_document', 'like', "%{$q}%")
                        ->orWhere('status', 'like', "%{$q}%")
                        ->orWhere('upgrade_reject', 'like', "%{$q}%")
                        ->orWhere('first_chapter', 'like', "%{$q}%")
                        ->orWhere('second_chapter', 'like', "%{$q}%")
                        ->orWhere('third_chapter', 'like', "%{$q}%")
                        ->orWhere('fourth_chapter', 'like', "%{$q}%")
                        ->orWhere('fifth_chapter', 'like', "%{$q}%")
                        ->orWhere('sixth_chapter', 'like', "%{$q}%")
                        ->orWhere('seventh_chapter', 'like', "%{$q}%");
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($year, function ($qq) use ($year) {
                $qq->whereYear('date_document', $year)
                    ->orWhereHas('rkia', fn($r) => $r->where('period', $year));
            })
            ->when($perusahaanId, fn($qq) => $qq->whereHas('rkia', fn($r) => $r->where('perusahaan_id', $perusahaanId)))
            ->when($jenisAudit,   fn($qq) => $qq->whereHas('rkia', fn($r) => $r->where('type_id', $jenisAudit)))
            ->orderByDesc('date_document')
            ->orderByDesc('id');
        $paginator = $query->paginate($perPage);

        // resources
        $data = TransRkiaDocumentResource::collection($paginator);
        $rows = TransRkiaDocumentRowResource::collection($paginator->getCollection());

        return $data->additional([
            'table' => [
                'columns' => ['Tahun', 'No. PKAT', 'Tgl PKAT', 'Rev', 'Status'],
                'rows'    => $rows,
            ],
        ]);
    }

    public function show($id)
    {
        $doc = TransRkiaDocument::with([
            'creator:id,name,email',
            'updater:id,name,email',
            'rkia'
        ])->findOrFail($id);

        return new TransRkiaDocumentResource($doc);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rkia_id'         => ['required', 'integer'],
            'no_document'     => ['nullable', 'string', 'max:255'],
            'date_document'   => ['nullable', 'date'],
            'status'          => ['nullable', 'string', 'max:255'],
            'version'         => ['nullable', 'integer', 'min:0'],
            'first_chapter'   => ['nullable', 'string'],
            'second_chapter'  => ['nullable', 'string'],
            'third_chapter'   => ['nullable', 'string'],
            'fourth_chapter'  => ['nullable', 'string'],
            'fifth_chapter'   => ['nullable', 'string'],
            'sixth_chapter'   => ['nullable', 'string'],
            'seventh_chapter' => ['nullable', 'string'],
            'upgrade_reject'  => ['nullable', 'string'],
        ]);

        $userId = auth()->id();

        $doc = new TransRkiaDocument();
        $doc->rkia_id        = $validated['rkia_id'];
        $doc->no_document    = $validated['no_document'] ?? null;
        $doc->date_document  = $validated['date_document'] ?? null;
        $doc->status         = $validated['status'] ?? 'new';
        $doc->version        = $validated['version'] ?? 0;
        $doc->first_chapter   = $validated['first_chapter']  ?? null;
        $doc->second_chapter  = $validated['second_chapter'] ?? null;
        $doc->third_chapter   = $validated['third_chapter']  ?? null;
        $doc->fourth_chapter  = $validated['fourth_chapter'] ?? null;
        $doc->fifth_chapter   = $validated['fifth_chapter']  ?? null;
        $doc->sixth_chapter   = $validated['sixth_chapter']  ?? null;
        $doc->seventh_chapter = $validated['seventh_chapter'] ?? null;
        $doc->upgrade_reject  = $validated['upgrade_reject'] ?? null;
        $doc->created_by      = $userId;
        $doc->updated_by      = $userId;
        $doc->save();

        $doc->load(['creator:id,name,email', 'updater:id,name,email', 'rkia']);

        return (new TransRkiaDocumentResource($doc))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'rkia_id'         => ['sometimes', 'integer'],
            'no_document'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'date_document'   => ['sometimes', 'nullable', 'date'],
            'status'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'version'         => ['sometimes', 'nullable', 'integer', 'min:0'],
            'first_chapter'   => ['sometimes', 'nullable', 'string'],
            'second_chapter'  => ['sometimes', 'nullable', 'string'],
            'third_chapter'   => ['sometimes', 'nullable', 'string'],
            'fourth_chapter'  => ['sometimes', 'nullable', 'string'],
            'fifth_chapter'   => ['sometimes', 'nullable', 'string'],
            'sixth_chapter'   => ['sometimes', 'nullable', 'string'],
            'seventh_chapter' => ['sometimes', 'nullable', 'string'],
            'upgrade_reject'  => ['sometimes', 'nullable', 'string'],
        ]);

        $doc = TransRkiaDocument::findOrFail($id);
        $doc->fill($validated);
        $doc->updated_by = auth()->id();
        $doc->save();

        $doc->load(['creator:id,name,email', 'updater:id,name,email', 'rkia']);

        return (new TransRkiaDocumentResource($doc))
            ->additional(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        $doc = TransRkiaDocument::findOrFail($id);
        $doc->delete();

        return response()->json(['message' => 'Deleted']);
    }
}

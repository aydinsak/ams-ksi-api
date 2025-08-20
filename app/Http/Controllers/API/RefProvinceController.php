<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RefProvince;
use App\Http\Resources\ProvinceResource;
use Illuminate\Support\Facades\Schema;

class RefProvinceController extends Controller
{
    public function index(Request $r)
    {
        $q       = $r->string('q');
        $perPage = (int) $r->input('per_page', 20);
        $with    = [];

        if ($r->boolean('include_cities')) {
            $with[] = 'cities';
        }

        $query = RefProvince::query()
            ->when(!empty($with), fn($qq) => $qq->with($with))
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->when(Schema::hasColumn('ref_province', 'name'), fn($x) => $x->orWhere('name', 'like', "%$q%"))
                    ->when(Schema::hasColumn('ref_province', 'code'), fn($x) => $x->orWhere('code', 'like', "%$q%"));
            }))
            ->orderBy('id');

        return ProvinceResource::collection($query->paginate($perPage));
    }

    public function show(Request $r, $id)
    {
        $with = $r->boolean('include_cities') ? ['cities'] : [];
        $prov = RefProvince::with($with)->findOrFail($id);
        return new ProvinceResource($prov);
    }
}

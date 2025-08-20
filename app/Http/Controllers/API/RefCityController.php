<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RefCity;
use App\Http\Resources\CityResource;
use Illuminate\Support\Facades\Schema;

class RefCityController extends Controller
{
    public function index(Request $r)
    {
        $q          = $r->string('q');
        $provinceId = $r->input('province_id');
        $perPage    = (int) $r->input('per_page', 20);

        $query = RefCity::query()
            ->when($r->boolean('include_province'), fn($qq) => $qq->with('province'))
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->when(Schema::hasColumn('ref_city', 'name'), fn($x) => $x->orWhere('name', 'like', "%$q%"))
                    ->when(Schema::hasColumn('ref_city', 'city'), fn($x) => $x->orWhere('city', 'like', "%$q%"))
                    ->when(Schema::hasColumn('ref_city', 'code'), fn($x) => $x->orWhere('code', 'like', "%$q%"));
            }))
            ->when($provinceId, fn($qq) => $qq->where('province_id', $provinceId))
            ->orderBy('id');

        return CityResource::collection($query->paginate($perPage));
    }

    public function show(Request $r, $id)
    {
        $city = RefCity::when($r->boolean('include_province'), fn($qq) => $qq->with('province'))
            ->findOrFail($id);
        return new CityResource($city);
    }
}

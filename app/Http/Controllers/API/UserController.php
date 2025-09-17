<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SysUser;
use Illuminate\Validation\Rule;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/* ADMIN */

class UserController extends Controller
{

    public function index(Request $request)
    {
        $q            = (string) $request->query('q', '');
        $status       = $request->query('status');
        $type         = $request->query('type');
        $perusahaanId = $request->query('perusahaan_id');
        $providerId   = $request->query('provider_id');
        $positionId   = $request->query('position_id');
        $perPage      = 15;

        $with = ['perusahaan', 'provider', 'position'];

        $users = SysUser::with($with)
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('username', 'like', "%{$q}%");
                });
            })
            ->when($status,       fn($qq) => $qq->where('status', $status))
            ->when($type,         fn($qq) => $qq->where('type', $type))
            ->when($perusahaanId, fn($qq) => $qq->where('perusahaan_id', $perusahaanId))
            ->when($providerId,   fn($qq) => $qq->where('provider_id', $providerId))
            ->when($positionId,   fn($qq) => $qq->where('position_id', $positionId))
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json($users);
    }

    public function show(Request $request, $id)
    {
        $with = ['perusahaan', 'provider', 'position'];

        $user = SysUser::when(!empty($with), fn($q) => $q->with($with))->findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:sys_users,email'],
            'username' => ['nullable', 'string', 'max:255', 'unique:sys_users,username'],
            'password' => ['required', 'string', 'min:8'],
            'status'   => ['nullable', 'string', 'max:50'],
            'type'     => ['nullable', 'string', 'max:50'],
            'phone'    => ['nullable', 'string', 'max:255'],
            'provider_id'   => ['nullable', 'integer', 'exists:ref_org_structs,id'],
            'perusahaan_id' => ['nullable', 'integer', 'exists:ref_org_structs,id'],
            'position_id'   => ['nullable', 'integer', 'exists:ref_positions,id'],
            'image'         => ['nullable', 'string', 'max:255'],
            'npp'           => ['nullable', 'string', 'max:255'],
            'nik'           => ['nullable', 'string', 'max:255'],
            'jabatan_provider' => ['nullable', 'string', 'max:255'],
        ]);

        $data['password'] = Hash::make($data['password']);

        if (Auth::check()) {
            $data['created_by'] = $data['created_by'] ?? Auth::id();
            $data['updated_by'] = $data['updated_by'] ?? Auth::id();
        }

        $user = SysUser::create($data);

        return response()->json([
            'message' => 'User created successfully',
            'user' => new UserResource($user)
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = SysUser::findOrFail($id);

        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', 'max:255', Rule::unique('sys_users', 'email')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('sys_users', 'username')->ignore($user->id)],
            'password' => ['required', 'string', 'min:8'],
            'status'   => ['nullable', 'string', 'max:50'],
            'type'     => ['nullable', 'string', 'max:50'],
            'phone'    => ['nullable', 'string', 'max:255'],
            'provider_id'   => ['nullable', 'integer'],
            'perusahaan_id' => ['nullable', 'integer'],
            'position_id'   => ['nullable', 'integer'],
            'image'         => ['nullable', 'string', 'max:255'],
            'npp'           => ['nullable', 'string', 'max:255'],
            'nik'           => ['nullable', 'string', 'max:255'],
            'jabatan_provider' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($data['password'])) unset($data['password']);

        if (Auth::check()) {
            $data['updated_by'] = $data['updated_by'] ?? Auth::id();
        }

        $user->update($data);
        return new UserResource($user);
    }

    public function destroy($id)
    {
        SysUser::findOrFail($id)->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function me(Request $request)
    {
        $with = ['perusahaan', 'provider', 'position'];

        $user = SysUser::when(!empty($with), fn($q) => $q->with($with))->find($request->user()->id);
        return response()->json($user);
    }
}

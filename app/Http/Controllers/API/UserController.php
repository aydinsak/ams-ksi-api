<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SysUser;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q');
        $per = (int) $request->input('per_page', 15);

        $query = SysUser::query()
            ->when($q, fn($qq) => $qq->where(fn($w) => $w
                ->where('name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")
                ->orWhere('username', 'like', "%$q%")))
            ->orderBy('id');

        return response()->json($query->paginate($per));
    }

    public function show($id)
    {
        return response()->json(SysUser::findOrFail($id));
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
        ]);

        $user = SysUser::create($data);
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user_update = SysUser::findOrFail($id);

        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', 'max:255', Rule::unique('sys_users', 'email')->ignore($user_update->id)],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('sys_users', 'username')->ignore($user_update->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'status'   => ['nullable', 'string', 'max:50'],
            'type'     => ['nullable', 'string', 'max:50'],
            'phone'    => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($data['password'])) unset($data['password']);
        $user_update->update($data);

        return response()->json($user_update);
    }
    public function destroy($id)
    {
        SysUser::findOrFail($id)->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}

<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SysUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
/* Auth user*/

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:sys_users,email'],
            'username' => ['nullable', 'string', 'max:255', 'unique:sys_users,username'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = SysUser::create($data);
        $token = $user->createToken('register')->accessToken;

        return response()->json([
            'message' => 'Registered',
            'user'    => $user,
            'token'   => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->merge([
            'login' => $request->input('login')
                ?? $request->input('username')
                ?? $request->input('email'),
        ]);

        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = SysUser::where('email', $credentials['login'])
        ->orWhere('username', $credentials['login'])
        ->firstOrFail();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('login')->accessToken;

        return response()->json([
            'message' => 'Logged in',
            'user'    => $user,
            'token'   => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()?->token()?->revoke();
        return response()->json(['message' => 'Logged out']);
    }
}

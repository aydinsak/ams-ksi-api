<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SysUser;
use Illuminate\Support\Facades\Auth;
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


        $user = SysUser::create($data);
        $token = $user->createToken('register')->accessToken;
        return response()->json([
            'message' => 'Registered',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate('email', 'password');

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = SysUser::where('email', $credentials['email'])->firstOrFail();
        $token = $user->createToken('login')->accessToken;

        return response()->json([
            'message' => 'Logged in',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $token = $request->user()?->token();
        if ($token) {
            $token->revoke();
        }
        return response()->json(['message' => 'Logged out']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if (Auth::attempt($credentials, true)) {
            Auth::user()->tokens()->delete();

            $plainTextToken = Auth::user()->createToken(Str::uuid())->plainTextToken;

            return response()
                ->json(
                    [
                        'data' => [
                            'token' => $plainTextToken
                        ]
                    ]
                );
        }

        return response()->json(
            ['message' => 'The provided credentials do not match our records.'],
            401,
        );
    }
}

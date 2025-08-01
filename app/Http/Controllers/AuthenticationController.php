<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException as ValidationException;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
       
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = $request->user();
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'code' => Response::HTTP_OK,
            'message' => 'Successfully Logged in',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
            ],
        ], Response::HTTP_OK);
    }

    public function logout(Request $request)
    {
        // Revoke current token only
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'code' => Response::HTTP_OK,
            'message' => 'Successfully logged out',
        ], Response::HTTP_OK);
    }
}

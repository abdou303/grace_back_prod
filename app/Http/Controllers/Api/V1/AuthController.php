<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    /*public function login(Request $request)
    {
     
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Bonjour',
                'success' => false,
                'arrors' => 'informations incorrectes',
            ], 401);
            //throw ValidationException::withMessages(['email' => 'informations incorrectes']);
        }
        if (!hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'تسجيل الدخول',
                'success' => false,
                'arrors' => 'معلومات الولوج غير صحيحة',
            ], 401);
            // throw ValidationException::withMessages(['email' => 'informations incorrectes']);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'message' => 'informations correctes',
            'success' => true,
            'token' => $token,
            'user' => $user->name,
        ]);

    }*/

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Customize the JWT payload by adding custom claims
        $customClaims = [
            'userId' => $user->id,
            'name' => $user->name,
            'isAdmin' => $user->is_admin,
        ];

        // Generate the token with custom claims
        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return response()->json([
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}

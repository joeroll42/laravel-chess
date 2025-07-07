<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public function gen_access_token(Request $request): JsonResponse
    {
        // ✅ Validate request input
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        // ✅ Retrieve user
        $user = User::where('email', $validatedData['email'])->first();

        // ✅ Check password
        if (!Hash::check($validatedData['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // ✅ Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // ✅ Return token
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
}

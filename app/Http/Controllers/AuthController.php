<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function getCurrentUser()
    {
        return response()->json(Auth::user());
    }

    public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'role' => 'required|in:supervisor,student',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => $validated['role'],
    ]);

    return response()->json(['message' => 'Registration successful', 'user' => $user], 201);
}




public function login(Request $request)
{

    $validated = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);


    if (!Auth::attempt($validated)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }


    $user = Auth::user();

    // Generate API token for the authenticated user
    $token = $user->createToken('auth_token')->plainTextToken;

    // Return success response with token, user data, and role
    return response()->json([
        'message' => 'Login successful',
        'token' => $token,
        'user' => $user,
        'role' => $user->role, // Assuming 'role' field exists in the database
    ], 200);
}
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List users with pagination, search, and role filter
     * Query params: search, role, page, per_page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Debug logging
        Log::info('Admin API accessed', [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ] : null,
            'authenticated' => Auth::check(),
            'role' => $user ? $user->role : 'no user'
        ]);
        
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $query = User::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        $perPage = $request->input('per_page', 15);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Show a specific user
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $targetUser = User::with(['supervisor', 'student'])->find($id);

        if (!$targetUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Add additional data based on role
        $userData = $targetUser->toArray();

        if ($targetUser->role === 'supervisor') {
            $userData['supervisor'] = [
                'department' => $targetUser->supervisor?->department ?? 'N/A',
                'expertise' => $targetUser->supervisor?->expertise ?? 'N/A',
                'projects_count' => $targetUser->supervisor?->projects_count ?? 0,
            ];
        }

        if ($targetUser->role === 'student') {
            $userData['student'] = [
                'student_id' => $targetUser->student?->student_id ?? 'N/A',
                'major' => $targetUser->student?->major ?? 'N/A',
                'projects_count' => $targetUser->student?->projects_count ?? 0,
            ];
        }

        // Add recent activity (simplified)
        $userData['recent_activity'] = [];

        return response()->json($userData);
    }

    /**
     * Toggle user status (activate/deactivate)
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $targetUser = User::find($id);

        if (!$targetUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Toggle status
        $targetUser->status = $targetUser->status === 'active' ? 'inactive' : 'active';
        $targetUser->save();

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $targetUser
        ]);
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:student,supervisor,admin',
        ]);

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $newUser
        ], 201);
    }
} 
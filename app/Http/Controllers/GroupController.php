<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'members' => 'required|array',
            'members.*' => 'exists:users,id',
        ]);

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => Auth::id(),
        ]);

        $group->members()->attach($request->members);
        $group->members()->attach(Auth::id()); // Add the creator to the group

        $group->load(['members', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Group created successfully.',
            'data' => $group
        ]);
    }

    public function getGroups()
    {
        $groups = Group::whereHas('members', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->with(['members', 'creator', 'lastMessage'])
        ->orderBy('updated_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $groups
        ]);
    }

    public function getGroup($id)
    {
        $group = Group::with(['members', 'creator', 'messages.sender'])
            ->findOrFail($id);

        // Check if user is member of the group
        if (!$group->members->contains(Auth::id())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $group
        ]);
    }

    public function updateGroup(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        // Check if user is the creator of the group
        if ($group->created_by !== Auth::id()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $group->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $group->load(['members', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Group updated successfully.',
            'data' => $group
        ]);
    }

    public function addMembers(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        // Check if user is the creator of the group
        if ($group->created_by !== Auth::id()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'members' => 'required|array',
            'members.*' => 'exists:users,id',
        ]);

        $group->members()->attach($request->members);

        $group->load(['members', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Members added successfully.',
            'data' => $group
        ]);
    }

    public function removeMember(Request $request, $groupId, $memberId)
    {
        $group = Group::findOrFail($groupId);

        // Check if user is the creator of the group or removing themselves
        if ($group->created_by !== Auth::id() && Auth::id() !== $memberId) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Prevent removing the creator
        if ($group->created_by === $memberId) {
            return response()->json(['error' => 'Cannot remove group creator'], 400);
        }

        $group->members()->detach($memberId);

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully.'
        ]);
    }

    public function leaveGroup($id)
    {
        $group = Group::findOrFail($id);

        // Check if user is a member of the group
        if (!$group->members->contains(Auth::id())) {
            return response()->json(['error' => 'Not a member of this group'], 400);
        }

        // Prevent the creator from leaving (they should delete the group instead)
        if ($group->created_by === Auth::id()) {
            return response()->json(['error' => 'Group creator cannot leave. Delete the group instead.'], 400);
        }

        $group->members()->detach(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Left group successfully.'
        ]);
    }

    public function deleteGroup($id)
    {
        $group = Group::findOrFail($id);

        // Check if user is the creator of the group
        if ($group->created_by !== Auth::id()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Group deleted successfully.'
        ]);
    }

    public function getAvailableUsers()
    {
        $user = Auth::user();
        $query = User::query();

        // Filter by user role
        if ($user->role === 'supervisor') {
            $query->where('role', 'student');
        } else {
            $query->where('role', 'supervisor');
        }

        $users = $query->select('id', 'name', 'email', 'role')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function getGroupStats($id)
    {
        $group = Group::with(['members', 'messages'])->findOrFail($id);

        // Check if user is member of the group
        if (!$group->members->contains(Auth::id())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $stats = [
            'total_members' => $group->members->count(),
            'total_messages' => $group->messages->count(),
            'recent_messages' => $group->messages->where('created_at', '>=', now()->subDays(7))->count(),
            'active_members' => $group->members->where('last_seen_at', '>=', now()->subDays(1))->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}


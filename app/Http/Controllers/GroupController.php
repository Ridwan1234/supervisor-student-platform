<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'members' => 'required|array',
            'members.*' => 'exists:users,id',
        ]);

        $group = Group::create([
            'name' => $request->name,
            'created_by' => auth()->id(),
        ]);

        $group->members()->attach($request->members);
        $group->members()->attach(auth()->id()); // Add the creator to the group

        return response()->json(['message' => 'Group created successfully.', 'group' => $group]);
    }

    public function getGroups()
    {
        $groups = Group::whereHas('members', function ($query) {
            $query->where('user_id', auth()->id());
        })->with('members')->get();

        return response()->json($groups);
    }
}


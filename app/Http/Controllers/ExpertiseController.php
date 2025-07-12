<?php

namespace App\Http\Controllers;

use App\Models\Expertise;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ExpertiseController extends Controller
{
    public function index(Request $request)
    {
        $expertise = Expertise::where('supervisor_id', 1)->get();
        return response()->json($expertise);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'proficiency_level' => 'nullable|integer|min:0|max:100',
            'years_experience' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'projects_completed' => 'nullable|integer|min:0',
        ]);

        $expertise = Expertise::create([
            'supervisor_id' => 1,
            'name' => $request->name,
            'description' => $request->description,
            'proficiency_level' => $request->proficiency_level ?? 0,
            'years_experience' => $request->years_experience ?? 0,
            'icon' => $request->icon ?? 'bi bi-award',
            'tags' => $request->tags ?? [],
            'projects_completed' => $request->projects_completed ?? 0,
        ]);

        return response()->json([
            'message' => 'Expertise created successfully.',
            'data' => $expertise
        ], 201);
    }

    public function show($id)
    {
        $expertise = Expertise::findOrFail($id);
        return response()->json($expertise);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'proficiency_level' => 'nullable|integer|min:0|max:100',
            'years_experience' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'projects_completed' => 'nullable|integer|min:0',
        ]);

        $expertise = Expertise::findOrFail($id);
        $expertise->update([
            'name' => $request->name,
            'description' => $request->description,
            'proficiency_level' => $request->proficiency_level ?? 0,
            'years_experience' => $request->years_experience ?? 0,
            'icon' => $request->icon ?? 'bi bi-award',
            'tags' => $request->tags ?? [],
            'projects_completed' => $request->projects_completed ?? 0,
        ]);

        return response()->json([
            'message' => 'Expertise updated successfully.',
            'data' => $expertise
        ]);
    }

    public function destroy($id)
    {
        $expertise = Expertise::findOrFail($id);
        $expertise->delete();

        return response()->json(['message' => 'Expertise deleted successfully.']);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\ProjectProgress;
use Illuminate\Http\Request;
use ProgressUpdated;

class ProjectProgressController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completion_percentage' => 'required|integer|between:0,100',
        ]);

        $progress = ProjectProgress::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'description' => $request->description,
            'completion_percentage' => $request->completion_percentage,
            'updated_by' => auth()->id(),
        ]);

        event(new ProgressUpdated($progress));

        return response()->json(['message' => 'Progress updated successfully.', 'progress' => $progress]);
    }

    public function index($projectId)
    {
        $progressUpdates = ProjectProgress::where('project_id', $projectId)->with('updatedBy')->get();
        return response()->json($progressUpdates);
    }
}


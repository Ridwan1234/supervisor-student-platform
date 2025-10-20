<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $query = Project::with(['supervisor', 'students']);

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        $projects = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform the data to match frontend expectations
        $transformedProjects = collect($projects->items())->map(function ($project) {
            return [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'status' => $project->status,
                'progress' => $project->progress ?? 0,
                'supervisor' => $project->supervisor ? [
                    'id' => $project->supervisor->id,
                    'name' => $project->supervisor->name,
                ] : null,
                'students_count' => $project->students->count(),
                'created_at' => $project->created_at,
            ];
        });

        return response()->json([
            'data' => $transformedProjects,
            'current_page' => $projects->currentPage(),
            'per_page' => $projects->perPage(),
            'total' => $projects->total(),
            'last_page' => $projects->lastPage(),
        ]);
    }

    public function getStats()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $inProgressProjects = Project::where('status', 'in_progress')->count();
        $completedProjects = Project::where('status', 'completed')->count();

        // Calculate percentages
        $activePercentage = $totalProjects > 0 ? round(($activeProjects / $totalProjects) * 100, 1) : 0;
        $inProgressPercentage = $activeProjects > 0 ? round(($inProgressProjects / $activeProjects) * 100, 1) : 0;
        $completedPercentage = $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100, 1) : 0;

        // New projects this month
        $thisMonth = Carbon::now()->startOfMonth();
        $newProjectsThisMonth = Project::where('created_at', '>=', $thisMonth)->count();

        return response()->json([
            'totalProjects' => $totalProjects,
            'activeProjects' => $activeProjects,
            'inProgressProjects' => $inProgressProjects,
            'completedProjects' => $completedProjects,
            'newProjectsThisMonth' => $newProjectsThisMonth,
            'activePercentage' => $activePercentage,
            'inProgressPercentage' => $inProgressPercentage,
            'completedPercentage' => $completedPercentage,
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $project = Project::with(['supervisor', 'students', 'tasks', 'files'])->find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        return response()->json([
            'id' => $project->id,
            'title' => $project->title,
            'description' => $project->description,
            'status' => $project->status,
            'progress' => $project->progress ?? 0,
            'start_date' => $project->start_date,
            'due_date' => $project->due_date,
            'created_at' => $project->created_at,
            'updated_at' => $project->updated_at,
            'supervisor' => $project->supervisor ? [
                'id' => $project->supervisor->id,
                'name' => $project->supervisor->name,
                'email' => $project->supervisor->email,
            ] : null,
            'students' => $project->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                ];
            }),
            'students_count' => $project->students->count(),
            'tasks_count' => $project->tasks->count(),
            'files_count' => $project->files->count(),
        ]);
    }
}

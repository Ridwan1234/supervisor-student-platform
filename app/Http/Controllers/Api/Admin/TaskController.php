<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $query = Task::with(['creator', 'assignee', 'project']);

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

        $tasks = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform the data to match frontend expectations
        $transformedTasks = collect($tasks->items())->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'progress' => $task->progress ?? 0,
                'due_date' => $task->due_date,
                'created_by' => $task->creator ? [
                    'id' => $task->creator->id,
                    'name' => $task->creator->name,
                ] : null,
                'assigned_to' => $task->assignee ? [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->name,
                ] : null,
                'project' => $task->project ? [
                    'id' => $task->project->id,
                    'title' => $task->project->title,
                ] : null,
                'created_at' => $task->created_at,
            ];
        });

        return response()->json([
            'data' => $transformedTasks,
            'current_page' => $tasks->currentPage(),
            'per_page' => $tasks->perPage(),
            'total' => $tasks->total(),
            'last_page' => $tasks->lastPage(),
        ]);
    }

    public function getStats()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $totalTasks = Task::count();
        $pendingTasks = Task::where('status', 'pending')->count();
        $inProgressTasks = Task::where('status', 'in_progress')->count();
        $completedTasks = Task::where('status', 'completed')->count();

        // Calculate percentages
        $pendingPercentage = $totalTasks > 0 ? round(($pendingTasks / $totalTasks) * 100, 1) : 0;
        $inProgressPercentage = $totalTasks > 0 ? round(($inProgressTasks / $totalTasks) * 100, 1) : 0;
        $completedPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // New tasks this month
        $thisMonth = Carbon::now()->startOfMonth();
        $newTasksThisMonth = Task::where('created_at', '>=', $thisMonth)->count();

        return response()->json([
            'totalTasks' => $totalTasks,
            'pendingTasks' => $pendingTasks,
            'inProgressTasks' => $inProgressTasks,
            'completedTasks' => $completedTasks,
            'newTasksThisMonth' => $newTasksThisMonth,
            'pendingPercentage' => $pendingPercentage,
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

        $task = Task::with(['creator', 'assignee', 'project'])->find($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'progress' => $task->progress ?? 0,
            'start_date' => $task->start_date,
            'due_date' => $task->due_date,
            'created_at' => $task->created_at,
            'updated_at' => $task->updated_at,
            'creator' => $task->creator ? [
                'id' => $task->creator->id,
                'name' => $task->creator->name,
                'email' => $task->creator->email,
            ] : null,
            'assignee' => $task->assignee ? [
                'id' => $task->assignee->id,
                'name' => $task->assignee->name,
                'email' => $task->assignee->email,
            ] : null,
            'project' => $task->project ? [
                'id' => $task->project->id,
                'title' => $task->project->title,
                'description' => $task->project->description,
            ] : null,
            'comments_count' => 0,
        ]);
    }
}

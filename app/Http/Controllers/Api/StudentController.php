<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\ProjectActivity;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function getProjects()
    {
        $user = Auth::user();
        
        // Get projects assigned to the current student
        $projects = Project::with(['supervisor', 'activities'])
            ->whereHas('students', function($query) use ($user) {
                $query->where('student_id', $user->id);
            })
            ->orWhere('supervisor_id', $user->id) // Also get projects where user is supervisor
            ->get()
            ->map(function($project) {
                // Calculate days left
                $daysLeft = Carbon::parse($project->due_date)->diffInDays(Carbon::now(), false);
                
                // Get task counts
                $totalTasks = $project->tasks()->count();
                $completedTasks = $project->tasks()->where('status', 'completed')->count();
                
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'description' => $project->description,
                    'supervisor' => $project->supervisor ? $project->supervisor->name : 'Unknown',
                    'status' => $project->status,
                    'progress' => $project->progress,
                    'totalTasks' => $totalTasks,
                    'completedTasks' => $completedTasks,
                    'daysLeft' => $daysLeft,
                    'startDate' => $project->start_date->format('Y-m-d'),
                    'dueDate' => $project->due_date->format('Y-m-d'),
                    'lastUpdated' => $project->last_updated ? $project->last_updated->format('Y-m-d') : $project->updated_at->format('Y-m-d'),
                    'activities' => $project->activities->map(function($activity) {
                        return [
                            'id' => $activity->id,
                            'title' => $activity->title,
                            'description' => $activity->description,
                            'date' => $activity->activity_date->format('Y-m-d')
                        ];
                    })
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function getTasks()
    {
        $user = Auth::user();
        
        $tasks = Task::with(['project', 'assignedBy'])
            ->where('assigned_to', $user->id)
            ->get()
            ->map(function($task) {
                // Calculate days left
                $daysLeft = Carbon::parse($task->due_date)->diffInDays(Carbon::now(), false);
                
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'project' => $task->project ? $task->project->title : 'No Project',
                    'priority' => $task->priority,
                    'status' => $task->status,
                    'progress' => $task->progress,
                    'assignedDate' => $task->created_at->format('Y-m-d'),
                    'dueDate' => $task->due_date->format('Y-m-d'),
                    'lastUpdated' => $task->updated_at->format('Y-m-d'),
                    'daysLeft' => $daysLeft,
                    'requirements' => [
                        'Complete the task according to specifications',
                        'Submit progress updates regularly',
                        'Meet the deadline requirements'
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    public function updateProjectProgress(Request $request, $projectId)
    {
        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'status' => 'required|in:not_started,in_progress,completed,on_hold',
            'notes' => 'nullable|string'
        ]);

        $project = Project::findOrFail($projectId);
        
        // Update project
        $project->update([
            'progress' => $request->progress,
            'status' => $request->status,
            'last_updated' => now()
        ]);

        // Create activity record
        if ($request->notes) {
            ProjectActivity::create([
                'project_id' => $project->id,
                'title' => 'Progress Updated',
                'description' => $request->notes,
                'activity_date' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Project progress updated successfully'
        ]);
    }

    public function updateTaskProgress(Request $request, $taskId)
    {
        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'status' => 'required|in:pending,in_progress,completed,overdue',
            'notes' => 'nullable|string'
        ]);

        $task = Task::findOrFail($taskId);
        
        $task->update([
            'progress' => $request->progress,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task progress updated successfully'
        ]);
    }

    public function createTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'required|date'
        ]);

        $user = Auth::user();

        $task = Task::create([
            'assigned_by' => $user->id,
            'assigned_to' => $user->id, // Self-assigned for now
            'project_id' => $request->project_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'pending',
            'progress' => 0,
            'due_date' => $request->due_date
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task
        ]);
    }
}

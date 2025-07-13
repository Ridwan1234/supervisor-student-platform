<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Student;
use App\Models\Expertise;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupervisorController extends Controller
{
    public function getProjects()
    {
        $user = Auth::user();
        
        $projects = Project::with(['supervisor', 'students', 'activities'])
            ->where('supervisor_id', $user->id)
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
                    'students' => $project->students->map(function($student) {
                        return [
                            'id' => $student->id,
                            'name' => $student->name,
                            'email' => $student->email
                        ];
                    }),
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
        
        $tasks = Task::with(['project', 'assignedTo', 'assignedBy'])
            ->where('assigned_by', $user->id)
            ->get()
            ->map(function($task) {
                // Calculate days left
                $daysLeft = Carbon::parse($task->due_date)->diffInDays(Carbon::now(), false);
                
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'project' => $task->project ? $task->project->title : 'No Project',
                    'assigned_to' => $task->assignedTo ? $task->assignedTo->name : 'Unassigned',
                    'priority' => $task->priority,
                    'status' => $task->status,
                    'progress' => $task->progress,
                    'assignedDate' => $task->created_at->format('Y-m-d'),
                    'dueDate' => $task->due_date->format('Y-m-d'),
                    'lastUpdated' => $task->updated_at->format('Y-m-d'),
                    'estimated_hours' => $task->estimated_hours,
                    'daysLeft' => $daysLeft
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    public function getStudents()
    {
        $user = Auth::user();
        
        // Get students assigned to supervisor's projects
        $students = Student::whereHas('assignedProjects', function($query) use ($user) {
            $query->whereHas('project', function($query2) use($user){
                $query2->where('supervisor_id', $user->id);
            });
        })
        ->with('assignedProjects', function($query) use ($user) {
            $query->whereHas('project', function($query2) use($user){
                $query2->where('supervisor_id', $user->id);
            });
        })
        ->with('user')
        ->get()
        ->map(function($student) {
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'email' => $student->user->email,
                'user_id'   => $student->user_id,
                'projects' => $student->assignedProjects->map(function($project) {
                    return [
                        'id' => $project->id,
                        'title' => $project->title,
                        'status' => $project->status,
                        'progress' => $project->progress
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    public function getExpertise()
    {
        $user = Auth::user();
        
        $expertise = Expertise::where('supervisor_id', $user->id)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'level' => $item->level,
                    'created_at' => $item->created_at->format('Y-m-d')
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $expertise
        ]);
    }

    public function createProject(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after:start_date',
            'student_ids' => 'array'
        ]);

        $user = Auth::user();

        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'supervisor_id' => $user->id,
            'status' => 'not_started',
            'progress' => 0,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'last_updated' => now()
        ]);

        // Assign students to project
        if ($request->student_ids) {
            $project->students()->attach($request->student_ids);
        }

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'data' => $project
        ]);
    }

    public function assignTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'required|date'
        ]);

        $user = Auth::user();

        $task = Task::create([
            'assigned_by' => $user->id,
            'assigned_to' => $request->assigned_to,
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
            'message' => 'Task assigned successfully',
            'data' => $task
        ]);
    }

    public function updateProject(Request $request, $projectId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:not_started,in_progress,completed,on_hold',
            'progress' => 'required|integer|min:0|max:100',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after:start_date'
        ]);

        $project = Project::findOrFail($projectId);
        
        $project->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'progress' => $request->progress,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'last_updated' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully'
        ]);
    }

    public function updateTask(Request $request, $taskId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed,overdue',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'required|date'
        ]);

        $task = Task::findOrFail($taskId);
        
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully'
        ]);
    }

    public function getAvailableStudents()
    {
        $user = Auth::user();
        
        // Get students who are not assigned to any of supervisor's projects
        $assignedStudentIds = DB::table('student_project_assignments')
            ->join('projects', 'student_project_assignments.project_id', '=', 'projects.id')
            ->where('projects.supervisor_id', $user->id)
            ->pluck('student_project_assignments.student_id');

        $availableStudents = User::whereNotIn('id', $assignedStudentIds)
            ->where('role', 'student')
            ->get(['id', 'name', 'email']);

        return response()->json([
            'success' => true,
            'data' => $availableStudents
        ]);
    }
}

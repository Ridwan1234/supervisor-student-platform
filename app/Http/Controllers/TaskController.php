<?php

// app/Http/Controllers/TaskController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\Project;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class TaskController extends Controller
{
    // Fetch all students
    public function getStudents()
    {
        $students = User::where('role', 'student')->get();
        return response()->json($students);
    }

    // Get tasks for supervisor
    public function getSupervisorTasks()
    {
        $user = Auth::user();
        $supervisorId = $user ? $user->id : 1; // Default to supervisor ID 1 if not authenticated
        $tasks = Task::with(['creator', 'assignee', 'project'])
            ->where('created_by', $supervisorId)
            ->orWhere('assigned_by', $supervisorId)
            ->get();

        return response()->json($tasks);
    }

    public function getTasks()
    {
        $user = Auth::user();
        $userId = $user ? $user->id : 1; // Default to user ID 1 if not authenticated
        $tasks = Task::with(['creator', 'assignee', 'group'])
            ->where('created_by', $userId)
            ->orWhere('assigned_to', $userId)
            ->orWhereHas('group.members', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        return response()->json($tasks);
    }

    public function index()
    {
        $tasks = Task::with(['creator', 'assignee', 'project'])->get();
        return response()->json($tasks);
    }

    public function show($id)
    {
        $task = Task::with(['creator', 'assignee', 'project'])->findOrFail($id);
        return response()->json($task);
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id', // Must be a valid user ID
            'project_id' => 'nullable|exists:projects,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:pending,in_progress,completed,overdue',
            'due_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();
        $userId = $user ? $user->id : 1; // Default to user ID 1 if not authenticated
        
        // Verify that the assigned user is a student
        $assignedUser = User::find($request->assigned_to);
        if (!$assignedUser || $assignedUser->role !== 'student') {
            return response()->json([
                'message' => 'Assigned user must be a student.',
                'error' => 'invalid_assignment'
            ], 422);
        }
        
        // Verify that the creator is a supervisor
        // if ($user->role !== 'supervisor') {
        //     return response()->json([
        //         'message' => 'Only supervisors can create tasks.',
        //         'error' => 'unauthorized'
        //     ], 403);
        // }
        
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => $userId,
            'assigned_to' => $request->assigned_to,
            'project_id' => $request->project_id,
            'priority' => $request->priority ?? 'low',
            'status' => $request->status ?? 'pending',
            'due_date' => $request->due_date,
            'progress' => $request->progress ?? 0,
            'estimated_hours' => $request->estimated_hours ?? 0,
            'assigned_by' => $userId,
        ]);

        // Notify the assigned user
        NotificationService::taskAssigned($task, $assignedUser);

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => $task->load(['assignee', 'project'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:pending,in_progress,completed,overdue',
            'due_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();
        
        // Verify that the assigned user is a student
        $assignedUser = User::find($request->assigned_to);
        if (!$assignedUser || $assignedUser->role !== 'student') {
            return response()->json([
                'message' => 'Assigned user must be a student.',
                'error' => 'invalid_assignment'
            ], 422);
        }
        
        // Verify that the updater is a supervisor
        if ($user->role !== 'supervisor') {
            return response()->json([
                'message' => 'Only supervisors can update tasks.',
                'error' => 'unauthorized'
            ], 403);
        }

        $task = Task::findOrFail($id);
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'project_id' => $request->project_id,
            'priority' => $request->priority ?? 'low',
            'status' => $request->status ?? 'pending',
            'due_date' => $request->due_date,
            'progress' => $request->progress ?? 0,
            'estimated_hours' => $request->estimated_hours ?? 0,
        ]);

        // Notify the assigned user about the update
        NotificationService::taskAssigned($task, $assignedUser);

        return response()->json([
            'message' => 'Task updated successfully.',
            'data' => $task->load(['assignee', 'project'])
        ]);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.']);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task = Task::findOrFail($id);
        $task->update(['status' => $request->status]);

        // Notify the assigned user
        if ($task->assigned_to) {
            NotificationService::taskAssigned($task, $task->assignee);
        }
        // Notify group members
        if ($task->group_id) {
            $group = $task->group;
            foreach ($group->members as $member) {
                if ($member->id !== $task->assigned_to) {
                    NotificationService::taskAssigned($task, $member);
                }
            }
        }

        return response()->json(['message' => 'Task status updated.', 'task' => $task]);
    }

    public function updateTaskHistory()
    {
        $updateTaskHistory = TaskHistory::all(); // Or filter by assigned user if needed
        return response()->json($updateTaskHistory);
    }

    // Assign a task to a student
    public function assignTask(Request $request)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'assigned_by' => 'required|exists:users,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'deadline' => 'required|date',
        ]);

        $task = Task::create([
            'assigned_by' => $validated['assigned_by'],
            'assigned_to' => $validated['assigned_to'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'deadline' => $validated['deadline'],
        ]);

        // Create the first task history entry (when assigned)
        TaskHistory::create([
            'task_id' => $task->id,
            'status_update' => 'Task assigned',
            'feedback' => 'No feedback yet',
        ]);

        // Notify the assigned user
        $assignedUser = User::find($validated['assigned_to']);
        NotificationService::taskAssigned($task, $assignedUser);

        return response()->json(['message' => 'Task assigned successfully!']);
    }

    // Update task status (status update)
    public function updateTaskStatus(Request $request, $taskId)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $task = Task::findOrFail($taskId);
        $task->status = $validated['status'];
        $task->save();

        // Optionally create a task history entry for the status update
        TaskHistory::create([
            'task_id' => $task->id,
            'status_update' => "Status updated to: {$task->status}",
            'feedback' => 'No feedback yet',
        ]);

        // Notify the assigned user
        if ($task->assigned_to) {
            NotificationService::taskAssigned($task, $task->assignee);
        }

        return response()->json(['message' => 'Task status updated successfully!']);
    }
}

<?php

// app/Http/Controllers/TaskController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\TaskHistory;
use Illuminate\Http\Request;

class TaskController extends Controller
{


    // Fetch all students
    public function getStudents()
    {
        $students = User::where('role', 'student')->get();
        return response()->json($students);
    }

    public function getTasks()
    {
        $tasks = Task::all(); // Or filter by assigned user if needed
        return response()->json($tasks);
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

        return response()->json(['message' => 'Task status updated successfully!']);
    }
}

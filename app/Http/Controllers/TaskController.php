<?php

// app/Http/Controllers/TaskController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;

class TaskController extends Controller
{


    // Fetch all students
    public function getStudents()
    {
        $students = User::where('role', 'student')->get();
        return response()->json($students);
    }

    // public function getTasks()
    // {
    //     $tasks = Task::all(); // Or filter by assigned user if needed
    //     return response()->json($tasks);
    // }

    public function getTasks()
    {
        $tasks = Task::with(['creator', 'assignee', 'group'])
            ->where('created_by', auth()->id())
            ->orWhere('assigned_to', auth()->id())
            ->orWhereHas('group.members', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();

        return response()->json($tasks);
    }

    public function create(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'assigned_to' => 'nullable|exists:users,id',
        'group_id' => 'nullable|exists:groups,id',
        'due_date' => 'nullable|date|after:today',
    ]);

    $task = Task::create([
        'title' => $request->title,
        'description' => $request->description,
        'created_by' => auth()->id(),
        'assigned_to' => $request->assigned_to,
        'group_id' => $request->group_id,
        'due_date' => $request->due_date,
    ]);

    // Notify the assigned user
    if ($task->assigned_to) {
        $task->assignee->notify(new GeneralNotification(
            "New Task Assigned: {$task->title}",
            $task->description,
            "/tasks/{$task->id}"
        ));
    }

    // Notify group members
    if ($task->group_id) {
        $group = $task->group;
        foreach ($group->members as $member) {
            $member->notify(new GeneralNotification(
                "New Task in Group: {$group->name}",
                $task->description,
                "/tasks/{$task->id}"
            ));
        }
    }

    return response()->json(['message' => 'Task created successfully.', 'task' => $task]);
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
        $task->assignee->notify(new GeneralNotification(
            "Task Status Updated: {$task->title}",
            "The status is now {$task->status}.",
            "/tasks/{$task->id}"
        ));
    }

    // Notify group members
    if ($task->group_id) {
        $group = $task->group;
        foreach ($group->members as $member) {
            $member->notify(new GeneralNotification(
                "Task Status Updated in Group: {$group->name}",
                "The task '{$task->title}' is now {$task->status}.",
                "/tasks/{$task->id}"
            ));
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

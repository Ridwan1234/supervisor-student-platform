<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ProjectAssignmentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:not_started,in_progress,completed,on_hold',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after:start_date',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:users,id', // Changed to users table
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Authentication required.',
                'error' => 'unauthenticated'
            ], 401);
        }
        
        // Verify that the creator is a supervisor
        if ($user->role !== 'supervisor') {
            return response()->json([
                'message' => 'Only supervisors can create projects.',
                'error' => 'unauthorized'
            ], 403);
        }
        
        // Verify that all assigned users are students
        if ($request->has('student_ids')) {
            $assignedUsers = User::whereIn('id', $request->student_ids)->get();
            $nonStudents = $assignedUsers->where('role', '!=', 'student');
            if ($nonStudents->count() > 0) {
                return response()->json([
                    'message' => 'All assigned users must be students.',
                    'error' => 'invalid_assignment'
                ], 422);
            }
        }

        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'not_started',
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'supervisor_id' => $user->id,
        ]);

        // Assign students if provided
        if ($request->has('student_ids')) {
            $project->students()->attach($request->student_ids);
            // Notify each assigned student
            $assignedUsers = User::whereIn('id', $request->student_ids)->get();
            foreach ($assignedUsers as $student) {
                NotificationService::projectAssigned($project, $student);
            }
        }

        return response()->json([
            'message' => 'Project created successfully.', 
            'data' => $project->load('students')
        ], 201);
    }

    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Authentication required.',
                'error' => 'unauthenticated'
            ], 401);
        }
        
        $supervisorId = $user->role === 'supervisor' ? $user->id : 1;
        
        $projects = Project::with('students')->where('supervisor_id', $supervisorId)->get();
        return response()->json(['data' => $projects]);
    }

    public function show($id)
    {
        $project = Project::with('students')->findOrFail($id);
        return response()->json(['data' => $project]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:not_started,in_progress,completed,on_hold',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:users,id', // Changed to users table
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Authentication required.',
                'error' => 'unauthenticated'
            ], 401);
        }

        // Verify that the updater is a supervisor
        // if ($user->role !== 'supervisor') {
        //     return response()->json([
        //         'message' => 'Only supervisors can update projects.',
        //         'error' => 'unauthorized'
        //     ], 403);
        // }
        
        // Verify that all assigned users are students
        if ($request->has('student_ids')) {
            $assignedUsers = User::whereIn('id', $request->student_ids)->get();
            $nonStudents = $assignedUsers->where('role', '!=', 'student');
            if ($nonStudents->count() > 0) {
                return response()->json([
                    'message' => 'All assigned users must be students.',
                    'error' => 'invalid_assignment'
                ], 422);
            }
        }

        $project = Project::findOrFail($id);
        
        // Verify that the project belongs to the supervisor
        if ($project->supervisor_id !== $user->id) {
            return response()->json([
                'message' => 'You can only update your own projects.',
                'error' => 'unauthorized'
            ], 403);
        }
        
        $project->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? $project->status,
            'start_date' => $request->start_date ?? $project->start_date,
            'due_date' => $request->due_date ?? $project->due_date,
            'progress' => $request->progress ?? $project->progress,
        ]);

        // Update student assignments if provided
        if ($request->has('student_ids')) {
            $project->students()->sync($request->student_ids);
            // Notify each assigned student
            $assignedUsers = User::whereIn('id', $request->student_ids)->get();
            foreach ($assignedUsers as $student) {
                NotificationService::projectAssigned($project, $student);
            }
        }

        return response()->json([
            'message' => 'Project updated successfully.',
            'data' => $project->load('students')
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Authentication required.',
                'error' => 'unauthenticated'
            ], 401);
        }
        
        $project = Project::findOrFail($id);
        
        // Verify that the project belongs to the supervisor
        if ($user->role !== 'supervisor' || $project->supervisor_id !== $user->id) {
            return response()->json([
                'message' => 'You can only delete your own projects.',
                'error' => 'unauthorized'
            ], 403);
        }
        
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully.']);
    }

    public function assignStudent(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'student_id' => 'required|exists:users,id', // Changed to users table
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Authentication required.',
                'error' => 'unauthenticated'
            ], 401);
        }
        
        // Verify that the assigner is a supervisor
        if ($user->role !== 'supervisor') {
            return response()->json([
                'message' => 'Only supervisors can assign students.',
                'error' => 'unauthorized'
            ], 403);
        }
        
        // Verify that the assigned user is a student
        $assignedUser = User::find($request->student_id);
        if (!$assignedUser || $assignedUser->role !== 'student') {
            return response()->json([
                'message' => 'Assigned user must be a student.',
                'error' => 'invalid_assignment'
            ], 422);
        }

        $project = Project::findOrFail($request->project_id);
        
        // Verify that the project belongs to the supervisor
        if ($project->supervisor_id !== $user->id) {
            return response()->json([
                'message' => 'You can only assign students to your own projects.',
                'error' => 'unauthorized'
            ], 403);
        }

        // Assign the student
        $project->students()->attach($request->student_id);

        // Notify the student
        NotificationService::projectAssigned($project, $assignedUser);

        return response()->json(['message' => 'Student assigned and notified successfully.']);
    }

    public function availableStudents()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Authentication required.',
                'error' => 'unauthenticated'
            ], 401);
        }
        
        // Only supervisors can see available students
        if ($user->role !== 'supervisor') {
            return response()->json([
                'message' => 'Only supervisors can view available students.',
                'error' => 'unauthorized'
            ], 403);
        }
        
        $students = User::where('role', 'student')->get();
        return response()->json(['data' => $students]);
    }
}


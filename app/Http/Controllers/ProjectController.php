<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ProjectAssignmentNotification;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'supervisor_id' => 1,
        ]);

        return response()->json(['message' => 'Project created successfully.', 'project' => $project]);
    }

    public function index()
    {
        $projects = Project::with('students')->where('supervisor_id', 1)->get();
        return response()->json($projects);
    }

    public function assignStudent(Request $request)
{
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'student_id' => 'required|exists:students,id',
    ]);

    $project = Project::findOrFail($request->project_id);

    // Assign the student
    $project->students()->attach($request->student_id);

    // Notify the student
    $student = User::findOrFail(2);
    $student->notify(new ProjectAssignmentNotification($project));

    return response()->json(['message' => 'Student assigned and notified successfully.']);
}

    public function availableStudents()
{
    $students = Student::get();
    return response()->json($students);
}
}


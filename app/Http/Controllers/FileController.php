<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = File::with(['uploader', 'project', 'task']);
        
        // Filter by user role
        if ($user->role === 'supervisor') {
            $query->whereHas('project', function($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            })->orWhere('uploaded_by', $user->id);
        } else {
            $query->where('uploaded_by', $user->id)
                ->orWhereHas('project.students', function($q) use ($user) {
                    $q->where('student_id', $user->id);
                });
        }
        
        // Apply filters
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->task_id) {
            $query->where('task_id', $request->task_id);
        }
        
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        $files = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($files);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max file size
            'project_id' => 'nullable|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'folder' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $user = Auth::user();
        
        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        
        // Create folder path
        $folderPath = 'uploads/' . date('Y/m');
        if ($request->folder) {
            $folderPath .= '/' . $request->folder;
        }
        
        // Store file
        $path = $file->storeAs($folderPath, $filename);
        
        // Create file record
        $fileRecord = File::create([
            'name' => $originalName,
            'path' => $path,
            'type' => $extension,
            'size' => $file->getSize(),
            'uploaded_by' => $user->id,
            'project_id' => $request->project_id,
            'task_id' => $request->task_id,
            'folder' => $request->folder,
            'description' => $request->description,
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully!',
            'data' => $fileRecord->load('uploader', 'project', 'task')
        ], 201);
    }

    public function download($id)
    {
        $file = File::with(['uploader', 'project', 'task'])->findOrFail($id);
        $user = Auth::user();
        
        // Check permissions
        if ($file->uploaded_by !== $user->id) {
            if ($user->role === 'supervisor') {
                if (!$file->project || $file->project->supervisor_id !== $user->id) {
                    return response()->json(['error' => 'Access denied'], 403);
                }
            } else {
                if (!$file->project || !$file->project->students->contains($user->id)) {
                    return response()->json(['error' => 'Access denied'], 403);
                }
            }
        }

        if (!Storage::exists($file->path)) {
            return response()->json(['error' => 'File not found!'], 404);
        }

        return Storage::download($file->path, $file->name);
    }

    public function delete($id)
    {
        $file = File::findOrFail($id);
        $user = Auth::user();
        
        // Check permissions
        if ($file->uploaded_by !== $user->id) {
            if ($user->role === 'supervisor') {
                if (!$file->project || $file->project->supervisor_id !== $user->id) {
                    return response()->json(['error' => 'Access denied'], 403);
                }
            } else {
                return response()->json(['error' => 'Access denied'], 403);
            }
        }

        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully!'
        ]);
    }

    public function getFolders(Request $request)
    {
        $user = Auth::user();
        $query = File::select('folder')->distinct();
        
        if ($user->role === 'supervisor') {
            $query->whereHas('project', function($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            })->orWhere('uploaded_by', $user->id);
        } else {
            $query->where('uploaded_by', $user->id)
                ->orWhereHas('project.students', function($q) use ($user) {
                    $q->where('student_id', $user->id);
                });
        }
        
        $folders = $query->whereNotNull('folder')
            ->where('folder', '!=', '')
            ->pluck('folder')
            ->unique()
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => $folders
        ]);
    }

    public function getFileTypes()
    {
        $types = File::select('type')
            ->distinct()
            ->whereNotNull('type')
            ->pluck('type')
            ->filter()
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    public function getFileStats()
    {
        $user = Auth::user();
        $query = File::query();
        
        if ($user->role === 'supervisor') {
            $query->whereHas('project', function($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            })->orWhere('uploaded_by', $user->id);
        } else {
            $query->where('uploaded_by', $user->id)
                ->orWhereHas('project.students', function($q) use ($user) {
                    $q->where('student_id', $user->id);
                });
        }
        
        $stats = [
            'total_files' => $query->count(),
            'total_size' => $query->sum('size'),
            'recent_uploads' => $query->where('created_at', '>=', now()->subDays(7))->count(),
            'by_type' => $query->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}


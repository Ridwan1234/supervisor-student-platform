<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Project;
use App\Models\Task;
use App\Models\FileComment;
use App\Models\FileShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\NotificationService;

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
        // Handle both single file and multiple files
        // if ($request->hasFile('file')) {
        //     // Single file upload
            $request->validate([
                'files' => 'required|file|max:10240', // 10MB max file size
                'project_id' => 'nullable|exists:projects,id',
                'task_id' => 'nullable|exists:tasks,id',
                'folder' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:500',
            ]);
            
            $files = [$request->file('files')];

            // $files = $request->file(files;

        // } else {
            // Multiple files upload
            // $request->validate([
            //     'files' => 'required|array',
            //     'files.*' => 'file|max:10240', // 10MB max file size
            //     'project_id' => 'nullable|exists:projects,id',
            //     'task_id' => 'nullable|exists:tasks,id',
            //     'folder' => 'nullable|string|max:255',
            //     'description' => 'nullable|string|max:500',
            // ]);
            
            // $files = $request->file('files');
        // }
        $user = Auth::user();
        $uploadedFiles = [];

        foreach ($files as $file) {
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

            $uploadedFiles[] = $fileRecord->load('uploader', 'project', 'task');
        }

        $message = count($uploadedFiles) === 1 ? 'File uploaded successfully!' : count($uploadedFiles) . ' files uploaded successfully!';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $uploadedFiles
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

    public function preview($id)
    {
        $file = File::with(['uploader', 'project', 'task', 'comments.user'])->findOrFail($id);
        $user = Auth::user();
        
        // Check permissions
        if (!$this->canAccessFile($file, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Increment view count
        $file->increment('view_count');

        return response()->json([
            'success' => true,
            'data' => $file
        ]);
    }

    public function addComment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:file_comments,id',
        ]);

        $file = File::findOrFail($id);
        $user = Auth::user();
        
        if (!$this->canAccessFile($file, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $comment = FileComment::create([
            'file_id' => $id,
            'user_id' => $user->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'data' => $comment->load('user')
        ], 201);
    }

    public function getComments($id)
    {
        $file = File::findOrFail($id);
        $user = Auth::user();
        
        if (!$this->canAccessFile($file, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $comments = FileComment::with(['user', 'replies.user'])
            ->where('file_id', $id)
            ->topLevel()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    public function deleteComment($id)
    {
        $comment = FileComment::with('user')->findOrFail($id);
        $user = Auth::user();
        
        // Check if user can delete this comment
        if (!$comment->canBeDeletedBy($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully!'
        ]);
    }

    public function resolveComment($id)
    {
        $comment = FileComment::findOrFail($id);
        $user = Auth::user();
        
        // Check if user can resolve this comment
        if ($comment->file->uploaded_by !== $user->id && $user->role !== 'supervisor') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $comment->resolve($user);

        return response()->json([
            'success' => true,
            'message' => 'Comment resolved successfully!'
        ]);
    }

    public function share(Request $request, $id)
    {
        $request->validate([
            'shared_with' => 'required|exists:users,id',
            'permission' => 'required|in:view,download,edit,admin',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $file = File::findOrFail($id);
        $user = Auth::user();
        
        if ($file->uploaded_by !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if already shared
        $existingShare = FileShare::where('file_id', $id)
            ->where('shared_with', $request->shared_with)
            ->first();

        if ($existingShare) {
            $existingShare->update([
                'permission' => $request->permission,
                'expires_at' => $request->expires_at,
            ]);
            $share = $existingShare;
        } else {
            $share = FileShare::create([
                'file_id' => $id,
                'shared_by' => $user->id,
                'shared_with' => $request->shared_with,
                'permission' => $request->permission,
                'expires_at' => $request->expires_at,
                'access_token' => Str::random(32),
            ]);
        }

        // Notify the user the file is shared with
        $sharedWith = \App\Models\User::find($request->shared_with);
        NotificationService::fileShared($file, $sharedWith);

        return response()->json([
            'success' => true,
            'data' => $share->load('sharedWith')
        ]);
    }

    public function uploadVersion(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'description' => 'nullable|string|max:500',
        ]);

        $originalFile = File::findOrFail($id);
        $user = Auth::user();
        
        if ($originalFile->uploaded_by !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $file = $request->file('file');
        
        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        
        // Create folder path
        $folderPath = 'uploads/' . date('Y/m');
        if ($originalFile->folder) {
            $folderPath .= '/' . $originalFile->folder;
        }
        
        // Store file
        $path = $file->storeAs($folderPath, $filename);
        
        // Get next version number
        $nextVersion = $originalFile->versions()->max('version') + 1;
        
        // Create new version
        $newVersion = File::create([
            'name' => $originalName,
            'path' => $path,
            'type' => $extension,
            'size' => $file->getSize(),
            'uploaded_by' => $user->id,
            'project_id' => $originalFile->project_id,
            'task_id' => $originalFile->task_id,
            'folder' => $originalFile->folder,
            'description' => $request->description ?: $originalFile->description,
            'mime_type' => $file->getMimeType(),
            'version' => $nextVersion,
            'parent_id' => $originalFile->id,
            'is_public' => $originalFile->is_public,
            'tags' => $originalFile->tags,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'New version uploaded successfully!',
            'data' => $newVersion->load('uploader', 'project', 'task')
        ], 201);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:files,id',
        ]);

        $user = Auth::user();
        $deletedCount = 0;

        foreach ($request->file_ids as $fileId) {
            $file = File::find($fileId);
            
            if ($file && $this->canDeleteFile($file, $user)) {
                if (Storage::exists($file->path)) {
                    Storage::delete($file->path);
                }
                $file->delete();
                $deletedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} files deleted successfully!"
        ]);
    }

    public function bulkMove(Request $request)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:files,id',
            'folder' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $movedCount = 0;

        foreach ($request->file_ids as $fileId) {
            $file = File::find($fileId);
            
            if ($file && $this->canEditFile($file, $user)) {
                $file->update(['folder' => $request->folder]);
                $movedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$movedCount} files moved successfully!"
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'filters' => 'nullable|array',
        ]);

        $user = Auth::user();
        $query = File::with(['uploader', 'project', 'task']);
        
        // Apply role-based filtering
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

        // Search in name, description, and tags
        $query->where(function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->query . '%')
              ->orWhere('description', 'like', '%' . $request->query . '%')
              ->orWhereJsonContains('tags', $request->query);
        });

        // Apply additional filters
        if ($request->filters) {
            if (isset($request->filters['type'])) {
                $query->where('type', $request->filters['type']);
            }
            if (isset($request->filters['project_id'])) {
                $query->where('project_id', $request->filters['project_id']);
            }
            if (isset($request->filters['date_from'])) {
                $query->where('created_at', '>=', $request->filters['date_from']);
            }
            if (isset($request->filters['date_to'])) {
                $query->where('created_at', '<=', $request->filters['date_to']);
            }
        }

        $files = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    private function canAccessFile($file, $user)
    {
        // File owner can always access
        if ($file->uploaded_by === $user->id) {
            return true;
        }

        // Check if file is shared with user
        $share = FileShare::where('file_id', $file->id)
            ->where('shared_with', $user->id)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($share && !$share->isExpired()) {
            return true;
        }

        // Check project-based access
        if ($file->project) {
            if ($user->role === 'supervisor' && $file->project->supervisor_id === $user->id) {
                return true;
            }
            if ($user->role === 'student' && $file->project->students->contains($user->id)) {
                return true;
            }
        }

        return false;
    }

    private function canEditFile($file, $user)
    {
        return $file->uploaded_by === $user->id || 
               ($user->role === 'supervisor' && $file->project && $file->project->supervisor_id === $user->id);
    }

    private function canDeleteFile($file, $user)
    {
        return $file->uploaded_by === $user->id || 
               ($user->role === 'supervisor' && $file->project && $file->project->supervisor_id === $user->id);
    }
}


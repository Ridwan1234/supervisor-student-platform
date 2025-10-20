<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $query = File::with(['uploader', 'comments']);

        // Apply filters
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('type') && $request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $files = $query->paginate($perPage);

        return response()->json([
            'data' => $files->items(),
            'current_page' => $files->currentPage(),
            'per_page' => $files->perPage(),
            'total' => $files->total(),
            'last_page' => $files->lastPage()
        ]);
    }

    public function getStats()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $totalFiles = File::count();
        $totalSize = File::sum('size');
        $sharedFiles = File::where('is_public', true)->count();
        $totalDownloads = File::sum('download_count');

        // Calculate storage percentage (assuming 100GB limit)
        $storageLimit = 100 * 1024 * 1024 * 1024; // 100GB in bytes
        $storagePercentage = $totalSize > 0 ? min(($totalSize / $storageLimit) * 100, 100) : 0;

        // Files added this month
        $thisMonth = Carbon::now()->startOfMonth();
        $newFilesThisMonth = File::where('created_at', '>=', $thisMonth)->count();

        // Shared percentage
        $sharedPercentage = $totalFiles > 0 ? ($sharedFiles / $totalFiles) * 100 : 0;

        return response()->json([
            'totalFiles' => $totalFiles,
            'storageUsed' => $this->formatBytes($totalSize),
            'sharedFiles' => $sharedFiles,
            'totalDownloads' => $totalDownloads,
            'newFilesThisMonth' => $newFilesThisMonth,
            'storagePercentage' => round($storagePercentage, 1),
            'sharedPercentage' => round($sharedPercentage, 1)
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $file = File::with(['uploader', 'project', 'task', 'comments.user'])->find($id);

        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->json([
            'id' => $file->id,
            'name' => $file->name,
            'original_name' => $file->name, // Use name since original_name doesn't exist in migration
            'path' => $file->path,
            'size' => $file->size,
            'type' => $file->type,
            'mime_type' => $file->type, // Use type since mime_type doesn't exist in migration
            'is_public' => $file->is_public ?? false,
            'download_count' => $file->download_count ?? 0,
            'created_at' => $file->created_at,
            'updated_at' => $file->updated_at,
            'uploader' => $file->uploader ? [
                'id' => $file->uploader->id,
                'name' => $file->uploader->name,
                'email' => $file->uploader->email,
            ] : null,
            'project' => $file->project ? [
                'id' => $file->project->id,
                'title' => $file->project->title,
            ] : null,
            'task' => $file->task ? [
                'id' => $file->task->id,
                'title' => $file->task->title,
            ] : null,
            'comments_count' => $file->comments->count(),
        ]);
    }

    private function formatBytes($bytes)
    {
        if ($bytes == 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}

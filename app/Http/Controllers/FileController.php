<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Request $request){
        $files = File::get();
        return response()->json($files);
    }
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max file size
            'project_id' => 'nullable|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads'); // Save file in the "uploads" directory

        $fileRecord = File::create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'type' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'uploaded_by' => 1, //auth()->id(),
            'project_id' => $request->project_id,
            'task_id' => $request->task_id,
        ]);

        return response()->json(['message' => 'File uploaded successfully!', 'file' => $fileRecord], 201);
    }

    public function download($id)
    {
        $file = File::findOrFail($id);

        if (!Storage::exists($file->path)) {
            return response()->json(['error' => 'File not found!'], 404);
        }

        return Storage::download($file->path, $file->name);
    }

    public function delete($id)
    {
        $file = File::findOrFail($id);

        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        $file->delete();

        return response()->json(['message' => 'File deleted successfully!']);
    }
}


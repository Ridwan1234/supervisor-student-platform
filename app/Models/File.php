<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'path', 
        'type', 
        'size', 
        'uploaded_by', 
        'project_id', 
        'task_id',
        'folder',
        'description',
        'mime_type',
        'version',
        'parent_id',
        'is_public',
        'download_count',
        'view_count',
        'tags'
    ];

    protected $casts = [
        'size' => 'integer',
        'version' => 'integer',
        'is_public' => 'boolean',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'tags' => 'array',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function parent()
    {
        return $this->belongsTo(File::class, 'parent_id');
    }

    public function versions()
    {
        return $this->hasMany(File::class, 'parent_id')->orderBy('version', 'desc');
    }

    public function comments()
    {
        return $this->hasMany(FileComment::class);
    }

    public function shares()
    {
        return $this->hasMany(FileShare::class);
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIconAttribute()
    {
        $type = strtolower($this->type);
        
        $icons = [
            'pdf' => 'bi bi-file-pdf',
            'doc' => 'bi bi-file-word',
            'docx' => 'bi bi-file-word',
            'xls' => 'bi bi-file-excel',
            'xlsx' => 'bi bi-file-excel',
            'ppt' => 'bi bi-file-ppt',
            'pptx' => 'bi bi-file-ppt',
            'txt' => 'bi bi-file-text',
            'jpg' => 'bi bi-file-image',
            'jpeg' => 'bi bi-file-image',
            'png' => 'bi bi-file-image',
            'gif' => 'bi bi-file-image',
            'zip' => 'bi bi-file-zip',
            'rar' => 'bi bi-file-zip',
            'mp4' => 'bi bi-file-play',
            'avi' => 'bi bi-file-play',
            'mp3' => 'bi bi-file-music',
            'wav' => 'bi bi-file-music',
        ];
        
        return $icons[$type] ?? 'bi bi-file-earmark';
    }
}

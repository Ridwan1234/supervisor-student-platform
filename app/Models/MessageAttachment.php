<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'name',
        'path',
        'type',
        'size',
        'mime_type'
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
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

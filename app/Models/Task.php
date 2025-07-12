<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'assigned_by', 
        'assigned_to', 
        'project_id',
        'created_by',
        'title', 
        'description', 
        'status',
        'priority',
        'progress',
        'due_date',
        'estimated_hours',
        'group_id'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'progress' => 'integer',
        'estimated_hours' => 'decimal:2'
    ];

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function histories()
    {
        return $this->hasMany(TaskHistory::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}

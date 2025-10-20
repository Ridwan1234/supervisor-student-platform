<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'description',
        'supervisor_id',
        'status',
        'progress',
        'start_date',
        'due_date',
        'last_updated'
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'last_updated' => 'datetime',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_project_assignments', 'project_id', 'student_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function activities()
    {
        return $this->hasMany(ProjectActivity::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }
}

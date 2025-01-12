<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use Notifiable;

    protected $guarded = ['id', 'created_at'];

    public function assignedProjects()
    {
        return $this->belongsToMany(Project::class, 'student_project_assignments', 'student_id', 'project_id');
    }
}

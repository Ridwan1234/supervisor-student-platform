<?php

namespace App\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = ['id', 'created_at'];

    public function supervisor()
    {
        return $this->belongsTo('supervisor_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_project_assignments', 'project_id', 'student_id');
    }
}

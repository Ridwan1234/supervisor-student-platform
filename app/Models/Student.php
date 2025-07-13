<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use Notifiable;

    protected $guarded = ['id', 'created_at'];

     // Project relationships
     public function assignedProjects()
     {
         return $this->hasMany(StudentProjectAssignment::class);
     }

     // Student/Supervisor specific relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}

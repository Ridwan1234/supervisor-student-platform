<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    protected $guarded = ['id', 'created_at'];
    public function expertise()
    {
        return $this->hasMany(Expertise::class, 'supervisor_id');
    }

    public function supervisedProjects()
    {
        return $this->hasMany(Project::class, 'supervisor_id');
    }
}

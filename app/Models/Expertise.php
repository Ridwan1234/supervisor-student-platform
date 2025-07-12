<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expertise extends Model
{
    protected $fillable = [
        'supervisor_id',
        'name',
        'description',
        'proficiency_level',
        'years_experience',
        'icon',
        'tags',
        'projects_completed'
    ];

    protected $casts = [
        'tags' => 'array',
        'proficiency_level' => 'integer',
        'years_experience' => 'integer',
        'projects_completed' => 'integer'
    ];

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id');
    }
}

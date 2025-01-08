<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;


    protected $fillable = ['assigned_by', 'assigned_to', 'title', 'description', 'deadline', 'status'];

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by'); // User who assigned the task
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to'); // Student assigned the task
    }

    public function histories()
    {
        return $this->hasMany(TaskHistory::class);
    }
}

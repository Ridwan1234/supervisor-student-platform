<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{

    use HasFactory, Notifiable;
    use Notifiable;
    use HasApiTokens;


    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_by'); // Tasks assigned by this user
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to'); // Tasks assigned to this user
    }

    // Project relationships
    public function assignedProjects()
    {
        return $this->hasMany(Project::class, 'supervisor_id');
    }

    public function supervisedProjects()
    {
        return $this->hasMany(Project::class, 'supervisor_id');
    }

    public function studentProjects()
    {
        return $this->belongsToMany(Project::class, 'student_project_assignments', 'student_id', 'project_id');
    }

    // Messaging relationships
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'receiver_id')->latest();
    }

    public function unreadMessagesCount()
    {
        return $this->receivedMessages()
            ->whereNull('read_at')
            ->count();
    }

    // Student/Supervisor specific relationships
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function supervisor()
    {
        return $this->hasOne(Supervisor::class);
    }

    // File relationships
    public function uploadedFiles()
    {
        return $this->hasMany(File::class, 'uploaded_by');
    }

    // Group relationships
    public function createdGroups()
    {
        return $this->hasMany(Group::class, 'created_by');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }
}

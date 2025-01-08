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
}

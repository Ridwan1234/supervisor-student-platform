<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getDashboardStats()
    {
        $user = Auth::user();
        $userRole = $user->role ?? 'student';
        
        if ($userRole === 'supervisor') {
            return $this->getSupervisorStats($user);
        } else {
            return $this->getStudentStats($user);
        }
    }

    private function getSupervisorStats($user)
    {
        // Get supervisor's projects
        $projects = Project::where('supervisor_id', $user->id)->get();
        
        // Get all students assigned to supervisor's projects
        $students = Student::whereHas('assignedProjects', function($query) use ($user) {
            $query->whereHas('project', function($query2) use($user){
                $query2->where('supervisor_id', $user->id);
            });
        })->count();
        
        // Get tasks assigned by supervisor
        $pendingTasks = Task::where('assigned_by', $user->id)
            ->where('status', 'pending')
            ->count();
        
        $activeProjects = $projects->where('status', 'in_progress')->count();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);
        
        // Get notifications
        $notifications = $this->getNotifications($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'students' => $students,
                    'pendingTasks' => $pendingTasks,
                    'activeProjects' => $activeProjects
                ],
                'recentActivities' => $recentActivities,
                'notifications' => $notifications,
                'notificationCount' => count($notifications)
            ]
        ]);
    }

    private function getStudentStats($user)
    {
        // Get student's assigned projects
        $projects = Project::whereHas('students', function($query) use ($user) {
            $query->where('student_id', $user->id);
        })->get();
        
        // Get student's tasks
        $tasks = Task::where('assigned_to', $user->id)->get();
        
        $activeProjects = $projects->where('status', 'in_progress')->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();
        $completedProjects = $projects->where('status', 'completed')->count();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);
        
        // Get notifications
        $notifications = $this->getNotifications($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'activeProjects' => $activeProjects,
                    'pendingTasks' => $pendingTasks,
                    'completedProjects' => $completedProjects
                ],
                'recentActivities' => $recentActivities,
                'notifications' => $notifications,
                'notificationCount' => count($notifications)
            ]
        ]);
    }

    private function getRecentActivities($user)
    {
        $activities = [];
        
        // Get recent project activities
        $projectActivities = \App\Models\ProjectActivity::whereHas('project', function($query) use ($user) {
            if ($user->role === 'supervisor') {
                $query->where('supervisor_id', $user->id);
            } else {
                $query->whereHas('students', function($q) use ($user) {
                    $q->where('student_id', $user->id);
                });
            }
        })
        ->with('project')
        ->latest()
        ->take(5)
        ->get();
        
        foreach ($projectActivities as $activity) {
            $activities[] = [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'time' => $activity->created_at->diffForHumans(),
                'type' => 'project_activity'
            ];
        }
        
        // Get recent task updates
        $taskActivities = Task::where(function($query) use ($user) {
            if ($user->role === 'supervisor') {
                $query->where('assigned_by', $user->id);
            } else {
                $query->where('assigned_to', $user->id);
            }
        })
        ->latest()
        ->take(5)
        ->get();
        
        foreach ($taskActivities as $task) {
            $activities[] = [
                'id' => 'task_' . $task->id,
                'title' => 'Task Updated: ' . $task->title,
                'description' => 'Task status changed to ' . $task->status,
                'time' => $task->updated_at->diffForHumans(),
                'type' => 'task_update'
            ];
        }
        
        // Sort by time and take latest 5
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        return array_slice($activities, 0, 5);
    }

    private function getNotifications($user)
    {
        $notifications = [];
        
        // Get overdue tasks
        $overdueTasks = Task::where(function($query) use ($user) {
            if ($user->role === 'supervisor') {
                $query->where('assigned_by', $user->id);
            } else {
                $query->where('assigned_to', $user->id);
            }
        })
        ->where('due_date', '<', now())
        ->where('status', '!=', 'completed')
        ->count();
        
        if ($overdueTasks > 0) {
            $notifications[] = [
                'id' => 1,
                'message' => "You have {$overdueTasks} overdue task(s)"
            ];
        }
        
        // Get projects due soon
        $projectsDueSoon = Project::where(function($query) use ($user) {
            if ($user->role === 'supervisor') {
                $query->where('supervisor_id', $user->id);
            } else {
                $query->whereHas('students', function($q) use ($user) {
                    $q->where('student_id', $user->id);
                });
            }
        })
        ->where('due_date', '<=', now()->addDays(7))
        ->where('status', '!=', 'completed')
        ->count();
        
        if ($projectsDueSoon > 0) {
            $notifications[] = [
                'id' => 2,
                'message' => "You have {$projectsDueSoon} project(s) due soon"
            ];
        }
        
        // Get new assignments
        $newTasks = Task::where(function($query) use ($user) {
            if ($user->role === 'supervisor') {
                $query->where('assigned_by', $user->id);
            } else {
                $query->where('assigned_to', $user->id);
            }
        })
        ->where('status', 'pending')
        ->where('created_at', '>=', now()->subDays(1))
        ->count();
        
        if ($newTasks > 0) {
            $notifications[] = [
                'id' => 3,
                'message' => "You have {$newTasks} new task(s) assigned"
            ];
        }
        
        return $notifications;
    }

    public function getOverviewCards()
    {
        $user = Auth::user();
        $userRole = $user->role ?? 'student';
        
        if ($userRole === 'supervisor') {
            return $this->getSupervisorOverviewCards($user);
        } else {
            return $this->getStudentOverviewCards($user);
        }
    }

    private function getSupervisorOverviewCards($user)
    {
        // Count unique students assigned to supervisor's projects
        $students = Student::whereHas('assignedProjects', function($query) use ($user) {
            $query->whereHas('project', function($query2) use($user){
                $query2->where('supervisor_id', $user->id);
            });
        })->count();
        
        $pendingTasks = Task::where('assigned_by', $user->id)
            ->where('status', 'pending')
            ->count();
        
        $activeProjects = Project::where('supervisor_id', $user->id)
            ->where('status', 'in_progress')
            ->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'title' => 'Students Connected',
                    'value' => $students,
                    'change' => 12,
                    'icon' => 'bi bi-people',
                    'route' => 'supervisor-dashboard'
                ],
                [
                    'title' => 'Tasks Pending',
                    'value' => $pendingTasks,
                    'change' => -5,
                    'icon' => 'bi bi-list-check',
                    'route' => 'task-management'
                ],
                [
                    'title' => 'Projects Active',
                    'value' => $activeProjects,
                    'change' => 8,
                    'icon' => 'bi bi-folder',
                    'route' => 'project-management'
                ]
            ]
        ]);
    }

    private function getStudentOverviewCards($user)
    {
        $activeProjects = Project::whereHas('students', function($query) use ($user) {
            $query->where('student_id', $user->id);
        })
        ->where('status', 'in_progress')
        ->count();
        
        $pendingTasks = Task::where('assigned_to', $user->id)
            ->where('status', 'pending')
            ->count();
        
        $completedProjects = Project::whereHas('students', function($query) use ($user) {
            $query->where('student_id', $user->id);
        })
        ->where('status', 'completed')
        ->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'title' => 'Active Projects',
                    'value' => $activeProjects,
                    'change' => 8,
                    'icon' => 'bi bi-folder',
                    'route' => 'student-project'
                ],
                [
                    'title' => 'Pending Tasks',
                    'value' => $pendingTasks,
                    'change' => -3,
                    'icon' => 'bi bi-list-check',
                    'route' => 'student-task'
                ],
                [
                    'title' => 'Completed Projects',
                    'value' => $completedProjects,
                    'change' => 15,
                    'icon' => 'bi bi-check-circle',
                    'route' => 'student-project'
                ]
            ]
        ]);
    }
}

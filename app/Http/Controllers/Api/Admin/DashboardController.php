<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getDashboardStats()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Get user statistics
        $totalUsers = User::count();
        $supervisors = User::where('role', 'supervisor')->count();
        $students = User::where('role', 'student')->count();
        $projects = Project::count();

        // Get monthly statistics
        $thisMonth = Carbon::now()->startOfMonth();
        $newUsersThisMonth = User::where('created_at', '>=', $thisMonth)->count();
        
        // Get active counts (users with recent activity)
        $activeSupervisors = User::where('role', 'supervisor')
            ->where('updated_at', '>=', Carbon::now()->subDays(30))
            ->count();
            
        $activeStudents = User::where('role', 'student')
            ->where('updated_at', '>=', Carbon::now()->subDays(30))
            ->count();
            
        $activeProjects = Project::where('status', 'in_progress')->count();

        // Get recent activity (simplified for now)
        $recentActivity = [
            [
                'id' => 1,
                'type' => 'user_created',
                'description' => 'New user registered: John Doe',
                'created_at' => Carbon::now()->subHours(2)
            ],
            [
                'id' => 2,
                'type' => 'project_created',
                'description' => 'New project created: Research Project A',
                'created_at' => Carbon::now()->subHours(4)
            ],
            [
                'id' => 3,
                'type' => 'user_updated',
                'description' => 'User profile updated: Jane Smith',
                'created_at' => Carbon::now()->subHours(6)
            ]
        ];

        return response()->json([
            'stats' => [
                'totalUsers' => $totalUsers,
                'supervisors' => $supervisors,
                'students' => $students,
                'projects' => $projects,
                'newUsersThisMonth' => $newUsersThisMonth,
                'activeSupervisors' => $activeSupervisors,
                'activeStudents' => $activeStudents,
                'activeProjects' => $activeProjects
            ],
            'recentActivity' => $recentActivity
        ]);
    }
} 
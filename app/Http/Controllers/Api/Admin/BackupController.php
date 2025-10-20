<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Mock backup data - replace with actual backup system
        $perPage = $request->get('per_page', 15);
        $currentPage = $request->get('page', 1);

        $mockBackups = $this->getMockBackups();

        // Apply status filter
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $mockBackups = array_filter($mockBackups, function($backup) use ($request) {
                return $backup['status'] === $request->status;
            });
        }

        // Paginate
        $total = count($mockBackups);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedBackups = array_slice($mockBackups, $offset, $perPage);

        return response()->json([
            'data' => array_values($paginatedBackups),
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage)
        ]);
    }

    public function getStats()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Mock backup statistics
        return response()->json([
            'totalBackups' => 25,
            'successfulBackups' => 22,
            'storageUsed' => '45.2 GB',
            'storagePercentage' => 45.2,
            'lastBackupTime' => Carbon::now()->subHours(6)->format('M j, Y g:i A'),
            'lastBackupStatus' => 'Successful',
            'nextBackupTime' => Carbon::now()->addHours(18)->format('M j, Y g:i A')
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Mock backup creation - replace with actual backup logic
        // This would typically trigger a backup job/process

        return response()->json([
            'message' => 'Backup initiated successfully',
            'backup_id' => uniqid('backup_')
        ]);
    }

    private function getMockBackups()
    {
        return [
            [
                'id' => 1,
                'name' => 'Daily Backup - ' . Carbon::now()->subDays(1)->format('Y-m-d'),
                'type' => 'full',
                'size' => 2147483648, // 2GB in bytes
                'status' => 'successful',
                'created_at' => Carbon::now()->subDays(1)->toISOString(),
                'duration' => '2h 15m',
                'description' => 'Automated daily backup'
            ],
            [
                'id' => 2,
                'name' => 'Weekly Backup - ' . Carbon::now()->subWeeks(1)->format('Y-m-d'),
                'type' => 'incremental',
                'size' => 536870912, // 512MB in bytes
                'status' => 'successful',
                'created_at' => Carbon::now()->subWeeks(1)->toISOString(),
                'duration' => '45m',
                'description' => 'Weekly incremental backup'
            ],
            [
                'id' => 3,
                'name' => 'Manual Backup - Project Data',
                'type' => 'partial',
                'size' => 1073741824, // 1GB in bytes
                'status' => 'successful',
                'created_at' => Carbon::now()->subDays(3)->toISOString(),
                'duration' => '1h 30m',
                'description' => 'Manual backup of project data'
            ],
            [
                'id' => 4,
                'name' => 'Daily Backup - ' . Carbon::now()->subDays(2)->format('Y-m-d'),
                'type' => 'full',
                'size' => 3221225472, // 3GB in bytes
                'status' => 'failed',
                'created_at' => Carbon::now()->subDays(2)->toISOString(),
                'duration' => null,
                'description' => 'Automated daily backup - Failed due to disk space'
            ],
            [
                'id' => 5,
                'name' => 'System Backup - Pre-Update',
                'type' => 'full',
                'size' => 1610612736, // 1.5GB in bytes
                'status' => 'successful',
                'created_at' => Carbon::now()->subDays(7)->toISOString(),
                'duration' => '3h 20m',
                'description' => 'Backup before system update'
            ]
        ];
    }
}

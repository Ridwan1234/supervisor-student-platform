<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // For now, we'll return mock data since Laravel doesn't have a built-in log table
        // In a real application, you'd want to create a logs table or use a logging package

        $perPage = $request->get('per_page', 50);
        $currentPage = $request->get('page', 1);

        // Mock log data - replace with actual log retrieval logic
        $mockLogs = $this->getMockLogs();

        // Apply filters
        if ($request->has('level') && $request->level && $request->level !== 'all') {
            $mockLogs = array_filter($mockLogs, function($log) use ($request) {
                return $log['level'] === $request->level;
            });
        }

        if ($request->has('search') && $request->search) {
            $search = strtolower($request->search);
            $mockLogs = array_filter($mockLogs, function($log) use ($search) {
                return strpos(strtolower($log['message']), $search) !== false ||
                       strpos(strtolower($log['context']), $search) !== false;
            });
        }

        // Apply date filter
        if ($request->has('date_filter') && $request->date_filter) {
            $dateFilter = $request->date_filter;
            $startDate = null;

            switch ($dateFilter) {
                case 'today':
                    $startDate = Carbon::today();
                    break;
                case 'yesterday':
                    $startDate = Carbon::yesterday();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
            }

            if ($startDate) {
                $mockLogs = array_filter($mockLogs, function($log) use ($startDate) {
                    return Carbon::parse($log['timestamp'])->gte($startDate);
                });
            }
        }

        // Paginate
        $total = count($mockLogs);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedLogs = array_slice($mockLogs, $offset, $perPage);

        return response()->json([
            'data' => array_values($paginatedLogs),
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage)
        ]);
    }

    private function getMockLogs()
    {
        return [
            [
                'id' => 1,
                'timestamp' => Carbon::now()->subMinutes(5)->toISOString(),
                'level' => 'info',
                'message' => 'User login successful',
                'context' => 'Authentication module',
                'user' => ['name' => 'John Doe'],
                'ip_address' => '192.168.1.100'
            ],
            [
                'id' => 2,
                'timestamp' => Carbon::now()->subMinutes(15)->toISOString(),
                'level' => 'warning',
                'message' => 'File upload failed',
                'context' => 'File management system',
                'user' => ['name' => 'Jane Smith'],
                'ip_address' => '192.168.1.101'
            ],
            [
                'id' => 3,
                'timestamp' => Carbon::now()->subMinutes(30)->toISOString(),
                'level' => 'error',
                'message' => 'Database connection timeout',
                'context' => 'Database layer',
                'user' => null,
                'ip_address' => '127.0.0.1'
            ],
            [
                'id' => 4,
                'timestamp' => Carbon::now()->subHours(1)->toISOString(),
                'level' => 'info',
                'message' => 'Project created successfully',
                'context' => 'Project management',
                'user' => ['name' => 'Admin User'],
                'ip_address' => '192.168.1.102'
            ],
            [
                'id' => 5,
                'timestamp' => Carbon::now()->subHours(2)->toISOString(),
                'level' => 'debug',
                'message' => 'Cache cleared',
                'context' => 'System maintenance',
                'user' => null,
                'ip_address' => '127.0.0.1'
            ]
        ];
    }

    public function show($id)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // For now, return mock data for a specific log entry
        $mockLogs = $this->getMockLogs();
        $log = collect($mockLogs)->firstWhere('id', (int)$id);

        if (!$log) {
            return response()->json(['error' => 'Log entry not found'], 404);
        }

        return response()->json($log);
    }
}

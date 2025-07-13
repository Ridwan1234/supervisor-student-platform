<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpertiseController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectProgressController;
use App\Http\Controllers\TaskController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;



Route::middleware([EnsureFrontendRequestsAreStateful::class, 'auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/tasks', [TaskController::class, 'index']);
//     Route::post('/tasks', [TaskController::class, 'store']);
//     Route::put('/tasks/{task}', [TaskController::class, 'update']);
// });

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/students', [TaskController::class, 'getStudents']);
    Route::post('/assign-task', [TaskController::class, 'assignTask']);
    Route::post('/task-history', [TaskController::class, 'updateTaskHistory']);
    Route::post('/update-task-history/{taskId}', [TaskController::class, 'updateTaskHistory']);
    Route::get('/tasks', [TaskController::class, 'getTasks']);
    Route::post('/tasks', [TaskController::class, 'assignTask']);
    Route::put('/tasks/{taskId}', [TaskController::class, 'updateTaskStatus']);
    Route::get('/user', [AuthController::class, 'getCurrentUser']);
    Route::post('/tasks', [TaskController::class, 'create']);
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Expertise routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/expertise', [ExpertiseController::class, 'index']);
    Route::post('/expertise', [ExpertiseController::class, 'store']);
    Route::get('/expertise/{id}', [ExpertiseController::class, 'show']);
    Route::put('/expertise/{id}', [ExpertiseController::class, 'update']);
    Route::delete('/expertise/{id}', [ExpertiseController::class, 'destroy']);
});

// Task routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::post('/tasks', [TaskController::class, 'create']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
});

// Project routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::post('/assign-student', [ProjectController::class, 'assignStudent']);
    Route::get('/students', [ProjectController::class, 'availableStudents']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/profile', [ProfileController::class, 'show']);
    // Route::post('/expertise', [ExpertiseController::class, 'store']);
    // Route::get('/projects', [ProjectController::class, 'index']);
    // Route::post('/projects', [ProjectController::class, 'store']);
    // Route::post('/assign-student', [ProjectController::class, 'assignStudent']);

    
    // expertise routes
    // Route::get('/expertise', [ExpertiseController::class, 'index']);
    // Route::post('/expertise', [ExpertiseController::class, 'store']);
    // Route::delete('/expertise/{id}', [ExpertiseController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->get('/notifications', function (Request $request) {
    return $request->user()->notifications;
});

// Route::middleware('auth')->get('/notifications', function () {
//     return auth()->user()->notifications;
// });

Route::post('/notifications/mark-read', function (Request $request) {
    $request->user()->unreadNotifications->markAsRead();
    return response()->json(['message' => 'Notifications marked as read.']);
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects/{id}/progress', [ProjectProgressController::class, 'index']);
    Route::post('/projects/progress', [ProjectProgressController::class, 'store']);
});

// Enhanced Messaging Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/messages', [MessageController::class, 'fetchMessages']);
    Route::post('/send-message', [MessageController::class, 'sendMessage']);
    Route::get('/conversations', [MessageController::class, 'getConversations']);
    Route::post('/messages/mark-read', [MessageController::class, 'markAsRead']);
    Route::delete('/messages/{id}', [MessageController::class, 'deleteMessage']);
    Route::get('/messages/attachments/{id}/download', [MessageController::class, 'downloadAttachment']);
    
    // Group messaging
    Route::post('/groups', [GroupController::class, 'create']);
    Route::get('/groups', [GroupController::class, 'getGroups']);
    Route::get('/groups/{id}', [GroupController::class, 'getGroup']);
    Route::put('/groups/{id}', [GroupController::class, 'updateGroup']);
    Route::post('/groups/{id}/members', [GroupController::class, 'addMembers']);
    Route::delete('/groups/{groupId}/members/{memberId}', [GroupController::class, 'removeMember']);
    Route::post('/groups/{id}/leave', [GroupController::class, 'leaveGroup']);
    Route::delete('/groups/{id}', [GroupController::class, 'deleteGroup']);
    Route::get('/groups/{id}/stats', [GroupController::class, 'getGroupStats']);
    Route::get('/available-users', [GroupController::class, 'getAvailableUsers']);
    Route::get('/groups/{id}/messages', [MessageController::class, 'fetchGroupMessages']);
    Route::post('/groups/{id}/send-message', [MessageController::class, 'sendGroupMessage']);
});

// Enhanced File Management Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/files', [FileController::class, 'index']);
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::get('/files/download/{id}', [FileController::class, 'download']);
    Route::delete('/files/{id}', [FileController::class, 'delete']);
    Route::get('/files/folders', [FileController::class, 'getFolders']);
    Route::get('/files/types', [FileController::class, 'getFileTypes']);
    Route::get('/files/stats', [FileController::class, 'getFileStats']);
    
    // Advanced File Management Features
    Route::get('/files/{id}/preview', [FileController::class, 'preview']);
    Route::get('/files/{id}/comments', [FileController::class, 'getComments']);
    Route::post('/files/{id}/comments', [FileController::class, 'addComment']);
    Route::delete('/files/comments/{id}', [FileController::class, 'deleteComment']);
    Route::patch('/files/comments/{id}/resolve', [FileController::class, 'resolveComment']);
    Route::post('/files/{id}/share', [FileController::class, 'share']);
    Route::post('/files/{id}/versions', [FileController::class, 'uploadVersion']);
    Route::post('/files/bulk-delete', [FileController::class, 'bulkDelete']);
    Route::post('/files/bulk-move', [FileController::class, 'bulkMove']);
    Route::post('/files/search', [FileController::class, 'search']);
});

// Student API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student/projects', [App\Http\Controllers\Api\StudentController::class, 'getProjects']);
    Route::get('/student/tasks', [App\Http\Controllers\Api\StudentController::class, 'getTasks']);
    Route::put('/student/projects/{projectId}/progress', [App\Http\Controllers\Api\StudentController::class, 'updateProjectProgress']);
    Route::put('/student/tasks/{taskId}/progress', [App\Http\Controllers\Api\StudentController::class, 'updateTaskProgress']);
    Route::post('/student/tasks', [App\Http\Controllers\Api\StudentController::class, 'createTask']);
});

// Dashboard API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/stats', [App\Http\Controllers\Api\DashboardController::class, 'getDashboardStats']);
    Route::get('/dashboard/overview-cards', [App\Http\Controllers\Api\DashboardController::class, 'getOverviewCards']);
});

// Supervisor API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/supervisor/projects', [App\Http\Controllers\Api\SupervisorController::class, 'getProjects']);
    Route::get('/supervisor/tasks', [App\Http\Controllers\Api\SupervisorController::class, 'getTasks']);
    Route::get('/supervisor/students', [App\Http\Controllers\Api\SupervisorController::class, 'getStudents']);
    Route::get('/supervisor/expertise', [App\Http\Controllers\Api\SupervisorController::class, 'getExpertise']);
    Route::post('/supervisor/projects', [App\Http\Controllers\Api\SupervisorController::class, 'createProject']);
    Route::post('/supervisor/tasks', [App\Http\Controllers\Api\SupervisorController::class, 'assignTask']);
    Route::put('/supervisor/projects/{projectId}', [App\Http\Controllers\Api\SupervisorController::class, 'updateProject']);
    Route::put('/supervisor/tasks/{taskId}', [App\Http\Controllers\Api\SupervisorController::class, 'updateTask']);
    Route::get('/supervisor/available-students', [App\Http\Controllers\Api\SupervisorController::class, 'getAvailableStudents']);
});





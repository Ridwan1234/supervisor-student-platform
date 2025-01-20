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

Route::get('/students', [TaskController::class, 'getStudents']);
Route::post('/assign-task', [TaskController::class, 'assignTask']);

Route::post('/task-history', [TaskController::class, 'updateTaskHistory']);
Route::post('/update-task-history/{taskId}', [TaskController::class, 'updateTaskHistory']);
Route::get('/tasks', [TaskController::class, 'getTasks']);
Route::middleware('auth:sanctum')->post('/tasks', [TaskController::class, 'assignTask']);
Route::put('/tasks/{taskId}', [TaskController::class, 'updateTaskStatus']);
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'getCurrentUser']);

Route::post('/tasks', [TaskController::class, 'create']);
Route::get('/tasks', [TaskController::class, 'getTasks']);
Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Route::get('/expertise', [ExpertiseController::class, 'index']);
Route::get('/expertise', [ExpertiseController::class, 'index']);
    Route::post('/expertise', [ExpertiseController::class, 'store']);
    Route::delete('/expertise/{id}', [ExpertiseController::class, 'destroy']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::post('/assign-student', [ProjectController::class, 'assignStudent']);
    Route::get('/students', [ProjectController::class, 'availableStudents']);


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

Route::get('/notifications', function () {
    // return auth()->user()->notifications;
    $user = User::find(2);
    return $user->notifications;
});

// Route::middleware('auth')->get('/notifications', function () {
//     return auth()->user()->notifications;
// });

Route::post('/notifications/mark-read', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return response()->json(['message' => 'Notifications marked as read.']);
})->middleware('auth');

Route::get('/projects/{id}/progress', [ProjectProgressController::class, 'index']);
Route::post('/projects/progress', [ProjectProgressController::class, 'store']);

Route::post('/messages', [MessageController::class, 'fetchMessages']);
Route::post('/send-message', [MessageController::class, 'sendMessage']);

Route::post('/groups', [GroupController::class, 'create']);
Route::get('/groups', [GroupController::class, 'getGroups']);
Route::post('/groups/{id}/messages', [MessageController::class, 'fetchGroupMessages']);
Route::post('/groups/{id}/send-message', [MessageController::class, 'sendGroupMessage']);


Route::post('/files/upload', [FileController::class, 'upload']);
Route::get('/files', [FileController::class, 'index']);
Route::get('/files/download/{id}', [FileController::class, 'download']);
Route::delete('/files/{id}', [FileController::class, 'delete']);





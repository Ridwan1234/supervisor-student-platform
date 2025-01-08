<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


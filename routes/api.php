<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\admin\EmployeeController;
use App\Http\Controllers\Api\employee\EmployeeController as employee;
use App\Http\Controllers\Api\employee\TaskController as Taskemployee;
use App\Http\Controllers\Api\Admin\WorkSpaceController;
use App\Http\Controllers\Api\Manager\ProjectController;
use App\Http\Controllers\TaskController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/Taskemployee', [Taskemployee::class, 'index'])->middleware('auth:api');
// Route::post('/employee', [employee::class, 'logout']);


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
});


Route::middleware('auth:api')->prefix('workspaces')->group(function () {
    Route::get('/', [WorkSpaceController::class, 'index']);
    Route::post('/', [WorkSpaceController::class, 'store']);
    Route::get('/{id}',  [WorkSpaceController::class, 'show']);
    Route::post('/{id}/update', [WorkSpaceController::class, 'update']);
    Route::delete('/{id}', [WorkSpaceController::class, 'destroy']);
});


Route::middleware(['auth:api'])->group(function () {
    Route::prefix('projects')->group(function () {
        Route::get('index/{workspace_id}',  [ProjectController::class, 'index']);
        Route::post('/',  action: [ProjectController::class, 'store']);
        Route::get('{id}', [ProjectController::class, 'show']);
        Route::post('{id}/update', [ProjectController::class, 'update']);
        Route::delete('{id}', [ProjectController::class, 'destroy']);


    });
});


Route::middleware('auth:api')->group(function () {
    Route::prefix( 'employees')->group(function () {
        Route::get('/{workspace_id}', [EmployeeController::class, 'index']);
        Route::post('/{workspace_id}', [EmployeeController::class, 'store']);
        Route::get('{id}', [EmployeeController::class, 'show']);
        Route::put('{id}', [EmployeeController::class, 'update']);
        Route::post('{id}/role', [EmployeeController::class, 'updateRole']);
        Route::delete('{id}', [EmployeeController::class, 'destroy']);
    });
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/tasks/{id}/show', [TaskController::class, 'show']);
    Route::get('/tasks/{id}', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::post('/tasks/update', [TaskController::class, 'updatePosition']);
    Route::post('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

});

Route::get('auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('auth/github', [AuthController::class, 'redirectToGitHub']);
Route::get('auth/github/callback', [AuthController::class, 'handleGitHubCallback']);
// use App\Http\Controllers\Api\NotificationController;

// Route::middleware('auth:api')->group(function () {
//     Route::get('/notifications', [NotificationController::class, 'index']);
//     Route::get('/notifications/unread', [NotificationController::class, 'unread']);
//     Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
//     Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
// });

// use App\Events\NewMessage;

// Route::post('/send-message', function (Request $request) {
//     broadcast(new NewMessage($request->message));
//     return response()->json(['message' => 'تم إرسال الرسالة عبر WebSocket']);
// });

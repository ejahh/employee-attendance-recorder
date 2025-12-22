<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::patch('employees/bulk-update', [EmployeeController::class, 'patchUpdateMultiple']);

Route::get('attendance/employee/{employee_id}', [AttendanceController::class, 'byEmployee']);
Route::get('users/multiple', [UserController::class, 'multiple']);
Route::patch('/users/bulk-update', [UserController::class, 'updateMultiple']);
Route::apiResource('users', UserController::class);
Route::get('employees/test-validator', [EmployeeController::class, 'testValidator']);
Route::patch('employees/{id}', [EmployeeController::class, 'patchUpdate']);
Route::get('employees/multiple', [EmployeeController::class, 'multiple']);
Route::delete('employees/bulk-delete', [EmployeeController::class, 'destroyMultiple']);
Route::put('employees/bulk-update', [EmployeeController::class, 'updateMultiple']);
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('attendance', AttendanceController::class);

use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

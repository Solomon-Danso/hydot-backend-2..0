<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;
use App\Http\Middleware\ApiAuthenticator;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthenticationController;


// Route for setting up the admin, accessible without authentication
Route::post('SetUpCreateAdmin', [AdminUserController::class, 'SetUpCreateAdmin']);
Route::post('LogIn', [AuthenticationController::class, 'LogIn']);
Route::post('VerifyToken', [AuthenticationController::class, 'VerifyToken']);


// Routes that require authentication
Route::middleware([ApiAuthenticator::class])->group(function () {



    Route::post('Unlocker', [AuthenticationController::class, 'Unlocker']);


    Route::post('CreateAdmin', [AdminUserController::class, 'CreateAdmin']);
    Route::post('UpdateAdmin', [AdminUserController::class, 'UpdateAdmin']);
    Route::post('ViewSingleAdmin', [AdminUserController::class, 'ViewSingleAdmin']);
    Route::post('VieViewAllAdmin', [AdminUserController::class, 'ViewAllAdmin']);
    Route::post('DeleteAdmin', [AdminUserController::class, 'DeleteAdmin']);

    Route::post('CreateEmployee', [EmployeeController::class, 'CreateEmployee']);
    Route::post('UpdateEmployee', [EmployeeController::class, 'UpdateEmployee']);
    Route::post('ViewSingleEmployee', [EmployeeController::class, 'ViewSingleEmployee']);
    Route::post('VieViewAllEmployee', [EmployeeController::class, 'ViewAllEmployee']);
    Route::post('DeleteEmployee', [EmployeeController::class, 'DeleteEmployee']);






});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;
use App\Http\Middleware\ApiAuthenticator;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CustomersController;

// Route for setting up the admin, accessible without authentication
Route::post('SetUpCreateAdmin', [AdminUserController::class, 'SetUpCreateAdmin']);
Route::post('LogIn', [AuthenticationController::class, 'LogIn']);
Route::post('VerifyToken', [AuthenticationController::class, 'VerifyToken']);

Route::post('ForgetPasswordStep1', [AuthenticationController::class, 'ForgetPasswordStep1']);
Route::post('ForgetPasswordStep2', [AuthenticationController::class, 'ForgetPasswordStep2']);

Route::post('UnLocker', [AdminUserController::class, 'UnLocker']);





// Routes that require authentication
Route::middleware([ApiAuthenticator::class])->group(function () {



  
    Route::post('CreateAdmin', [AdminUserController::class, 'CreateAdmin']);
    Route::post('UpdateAdmin', [AdminUserController::class, 'UpdateAdmin']);
    Route::post('EditAdmin', [AdminUserController::class, 'EditAdmin']);
    Route::post('ViewSingleAdmin', [AdminUserController::class, 'ViewSingleAdmin']);
    Route::post('ViewAllAdmin', [AdminUserController::class, 'ViewAllAdmin']);
    Route::post('DeleteAdmin', [AdminUserController::class, 'DeleteAdmin']);
    Route::post('UnBlockAdmin', [AdminUserController::class, 'UnBlockAdmin']);
    Route::post('BlockAdmin', [AdminUserController::class, 'BlockAdmin']);


    Route::post('CreateEmployee', [EmployeeController::class, 'CreateEmployee']);
    Route::post('UpdateEmployee', [EmployeeController::class, 'UpdateEmployee']);
    Route::post('ViewSingleEmployee', [EmployeeController::class, 'ViewSingleEmployee']);
    Route::post('ViewAllEmployee', [EmployeeController::class, 'ViewAllEmployee']);
    Route::post('DeleteEmployee', [EmployeeController::class, 'DeleteEmployee']);

    Route::post('CreateCustomers', [CustomersController::class, 'CreateCustomers']);
    Route::post('UpdateCustomers', [CustomersController::class, 'UpdateCustomers']);
    Route::post('ViewSingleCustomers', [CustomersController::class, 'ViewSingleCustomers']);
    Route::post('ViewAllCustomers', [CustomersController::class, 'ViewAllCustomers']);
    Route::post('DeleteCustomers', [CustomersController::class, 'DeleteCustomers']);





});

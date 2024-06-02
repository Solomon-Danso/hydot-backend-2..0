<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;
use App\Http\Middleware\ApiAuthenticator;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\Websites;
use App\Http\Controllers\Finance;



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

    Route::post('CreateHero', [Websites::class, 'CreateHero']);
    Route::post('ViewHero', [Websites::class, 'ViewHero']);

    Route::post('CreateWhatWeDo', [Websites::class, 'CreateWhatWeDo']);
    Route::post('ViewWhatWeDo', [Websites::class, 'ViewWhatWeDo']);

    Route::post('CreateOurDifferences', [Websites::class, 'CreateOurDifferences']);
    Route::post('ViewOurDifferences', [Websites::class, 'ViewOurDifferences']);

    Route::post('CreateOurProcess', [Websites::class, 'CreateOurProcess']);
    Route::post('ViewOurProcess', [Websites::class, 'ViewOurProcess']);

    Route::post('CreateOurPortfolioHeader', [Websites::class, 'CreateOurPortfolioHeader']);
    Route::post('ViewOurPortfolioHeader', [Websites::class, 'ViewOurPortfolioHeader']);

    Route::post('CreateOurPortfolioProjects', [Websites::class, 'CreateOurPortfolioProjects']);
    Route::post('UpdateOurPortfolioProjects', [Websites::class, 'UpdateOurPortfolioProjects']);
    Route::post('ViewOurPortfolioProjects', [Websites::class, 'ViewOurPortfolioProjects']);
    Route::post('DeleteOurPortfolioProjects', [Websites::class, 'DeleteOurPortfolioProjects']);

    Route::post('CreateOurClientsHeader', [Websites::class, 'CreateOurClientsHeader']);
    Route::post('ViewOurClientsHeader', [Websites::class, 'ViewOurClientsHeader']);

    Route::post('CreateOurClientsProjects', [Websites::class, 'CreateOurClientsProjects']);
    Route::post('UpdateOurClientsProjects', [Websites::class, 'UpdateOurClientsProjects']);
    Route::post('ViewOurClientsProjects', [Websites::class, 'ViewOurClientsProjects']);
    Route::post('DeleteOurClientsProjects', [Websites::class, 'DeleteOurClientsProjects']);

    Route::post('CreateTestimonials', [Websites::class, 'CreateTestimonials']);
    Route::post('UpdateTestimonials', [Websites::class, 'UpdateTestimonials']);
    Route::post('ViewTestimonials', [Websites::class, 'ViewTestimonials']);
    Route::post('DeleteTestimonials', [Websites::class, 'DeleteTestimonials']);


    Route::post('CreateSales', [Websites::class, 'CreateSales']);
    Route::post('ApproveSales', [Websites::class, 'ApproveSales']);
    Route::post('ViewSales', [Websites::class, 'ViewSales']);
   




});

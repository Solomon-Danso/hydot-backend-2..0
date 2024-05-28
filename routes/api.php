<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;
use App\Http\Middleware\ApiAuthenticator;

// Route for setting up the admin, accessible without authentication
Route::post('SetUpCreateAdmin', [AdminUserController::class, 'SetUpCreateAdmin']);


// Routes that require authentication
Route::middleware([ApiAuthenticator::class])->group(function () {
    Route::post('CreateAdmin', [AdminUserController::class, 'CreateAdmin']);
    Route::get('Test', [AdminUserController::class, 'Test']);


});

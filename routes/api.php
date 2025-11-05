<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;

Route::post('register', [AuthController::class, 'registerWithEmail']);
Route::post('login', [AuthController::class, 'emailLogin']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('profile', [AuthController::class, 'profile']);
    Route::get('search-users', [UserController::class, 'searchUsers']);
});
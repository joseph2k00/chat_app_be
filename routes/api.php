<?php

use App\Http\Controllers\AuthController;

Route::post('email-register', [AuthController::class, 'registerWithEmail']);
Route::post('email-login', [AuthController::class, 'emailLogin']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});

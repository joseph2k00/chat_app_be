<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ConversationController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'registerWithEmail']);
Route::post('login', [AuthController::class, 'emailLogin']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('profile', [AuthController::class, 'profile']);
    Route::get('search-users', [UserController::class, 'searchUsers']);

    Route::get('conversations', [ConversationController::class, 'getConversations']);
    Route::post('create-conversation', [ConversationController::class, 'createConversation']);
    Route::get('conversation/{conversation_id}', [ConversationController::class, 'getConversationDetails']);
    Route::post('conversation/send-message', [ConversationController::class, 'sendMessage']);
});
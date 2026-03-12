<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Create JWT token for a given user
     * 
     * @param \App\Models\User $user
     * @return mixed
     */
    public function createJwtToken(User $user): mixed {
        return JWTAuth::fromUser($user);
    }

    /**
     * Login user with email and password
     * 
     * @param array $credentials Associative array containing email and password
     * @return bool|array Returns false if login failed, else, returns user data and JWT token
     */
    public function loginUser(array $credentials): bool|array {
        if (!$token = JWTAuth::attempt($credentials)) {
            return false;
        }

        return [
            'token' => $token,
            'user' => Auth::user(),
        ];
    }

    /**
     * Logout current user
     * 
     * @param void
     * @return void
     */
    public function logoutCurrentUser(): void {
        Auth::logout();
    }
}
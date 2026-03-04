<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterWithEmailRequest;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * Register new user with Email
     * 
     * @param \App\Http\Requests\RegisterWithEmailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerWithEmail(RegisterWithEmailRequest $request)
    {
        $user = $this->userService->createUser($request->all());
        $token = $this->userService->createJwtToken($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Login user with email
     * 
     * @param \App\Http\Requests\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function emailLogin(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'token' => $token,
            'user' => Auth::user(),
        ], 200);
    }

    /**
     * Get user Profile
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(
            $this->userService->getUserProfile(Auth::user()->id)
        );
    }

    /**
     * Logout user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {    
        Auth::logout();
        
        return response()->json(
            [
                'message' => 'Successfully logged out'
            ]
        );
    }
}

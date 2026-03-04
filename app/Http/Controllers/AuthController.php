<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterWithEmailRequest;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $userService,
        $authService;

    public function __construct(
        UserService $userService,
        AuthService $authService
    ) {
        $this->userService = $userService;
        $this->authService = $authService;
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
        $token = $this->authService->createJwtToken($user);

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
        $response = $this->authService->loginUser(
            $request->only('email', 'password')
        );

        if (!$response) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        return response()->json($response, 200);
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
        $this->authService->logoutCurrentUser();
        
        return response()->json(
            [
                'message' => 'Successfully logged out'
            ]
        );
    }
}

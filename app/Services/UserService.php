<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
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
     * Create User
     * 
     * @param array $data
     * @return \App\Models\User
     */
    public function createUser(array $data): User {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Fetchs user profile data
     * 
     * @param int $id User ID
     * @return \App\Models\User
     */
    public function getUserProfile(int $id): User {
        return User::findOrFail($id);
    }

    /**
     * Get users list from search query
     * 
     * @param string $query
     * @return \App\Models\User
     */
    public function getUsersList(string $query): User {
        // $users = User::where(function ($q) use ($query) {
        //         $q->where('name', 'LIKE', "%{$query}%")
        //         ->orWhere('email', 'LIKE', "%{$query}%");
        //     })
        //     ->where('id', '!=', Auth::user()->id);
        return User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();
    }
}
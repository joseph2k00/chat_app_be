<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService
{
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
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersList(string $query): Collection
    {
        return User::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->where('id', '!=', Auth::id())
            ->limit(10)
            ->get();
    }
}
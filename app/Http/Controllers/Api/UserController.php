<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function searchUsers(Request $request)
    {
        $query = $request->input('query');

        // $users = User::where(function ($q) use ($query) {
        //         $q->where('name', 'LIKE', "%{$query}%")
        //         ->orWhere('email', 'LIKE', "%{$query}%");
        //     })
        //     ->where('id', '!=', Auth::user()->id);
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    public function checkUserConnection(Request $request, $user_id)
    {
        
    }
}

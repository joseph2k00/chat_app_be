<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConversationMembers;

class ConversationController extends Controller
{
    public function getConversations(Request $request)
    {
        $coversations = ConversationMembers::where('user_id', auth()->user()->id)
            ->with('conversation')
            ->get();
    }

    public function getConversation()
    {

    }
}

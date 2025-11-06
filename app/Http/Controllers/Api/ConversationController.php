<?php

namespace App\Http\Controllers\Api;

use App\Enum\ConversationType;
use App\Events\UserSentMessageEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConversationMembers;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationType as ModelsConversationType;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function getConversations()
    {
        $conversations = Conversation::whereHas('members', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })
        ->with(['members.user', 'latestMessage'])->get();

        return response()->json($conversations, 200);
    }

    public function createConversation(Request $request)
    {
        $request->validate([
            'other_user_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $currentUserConversations = ConversationMembers::where('user_id', Auth::user()->id)
            ->pluck('conversation_id')
            ->toArray();

        $existingConversation = ConversationMembers::whereIn('conversation_id', $currentUserConversations)
            ->where('user_id', $request->input('other_user_id'))
            ->first();

        if ($existingConversation) {
            return response()->json([
                'message' => 'Conversation already exists',
                'conversation_id' => $existingConversation->conversation_id,
            ], 200);
        }

        // Create new conversation logic
        $conversation = new Conversation();
        $conversation->type_id = ModelsConversationType::where('name', ConversationType::PRIVATE->value)->first()->id;
        $conversation->save();

        // IMPORTANT: PARALLEL TASK -- FUTURE IMPLEMENTATION NEEDED
        $member1 = new ConversationMembers();
        $member1->conversation_id = $conversation->id;
        $member1->user_id =Auth::user()->id;
        $member1->save();

        $member2 = new ConversationMembers();
        $member2->conversation_id = $conversation->id;
        $member2->user_id = $request->input('other_user_id');
        $member2->save();

        $message = new ConversationMessage();
        $message->conversation_id = $conversation->id;
        $message->sender_id = Auth::user()->id;
        $message->message = $request->input('message');
        $message->save();
        // END IMPORTANT

        return response()->json([
            'message' => 'Conversation created successfully',
            'conversation_id' => $conversation->id,
        ], 200);
    }

    public function getConversationDetails($conversation_id)
    {
        // Check if the authenticated user is a member of the conversation
        $isMember = ConversationMembers::where('conversation_id', $conversation_id)
            ->where('user_id', Auth::user()->id)
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $conversation = Conversation::with(['members.user', 'messages'])->find($conversation_id);
        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }
        return response()->json($conversation, 200);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'required|exists:conversations,id',
        ]);

        // Check if the authenticated user is a member of the conversation
        $isMember = ConversationMembers::where('conversation_id', $request->input('conversation_id'))
            ->where('user_id', Auth::user()->id)
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        $message = new ConversationMessage();
        $message->conversation_id = $request->input('conversation_id');
        $message->sender_id = Auth::user()->id;
        $message->message = $request->input('message');
        $message->save();

        
        return response()->json(['message' => 'Message sent successfully'], 200);
    }   
}

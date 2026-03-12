<?php

namespace App\Http\Controllers\Api;

use App\Events\NewMessageReceivedEvent;
use App\Events\UserSentMessageEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateConversationRequest;
use App\Http\Requests\GetConversationDetailsRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\ConversationMembers;
use App\Services\ConversationService;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    protected $conversationService;
    
    public function __construct(
        ConversationService $conversationService
    ) {
        $this->conversationService = $conversationService;
    }

    /**
     * Controller to get conversations of current user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversations()
    {
        $conversations = $this->conversationService->getConversationList();
        return response()->json($conversations, 200);
    }

    /**
     * Controller to create a new conversation between two users
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function createConversation(CreateConversationRequest $request)
    {
        $response = $this->conversationService->createNewConversation($request->toArray());

        NewMessageReceivedEvent::dispatch(
            $request->input('other_user_id'),
            $response["conversation"]->id,
            $response["message"]->message,
            Auth::user()->name,
            $response["message"]->conversation->conversation_title,
        );

        return response()->json([
            'message' => 'Conversation created successfully',
            'conversation_id' => $response["conversation"]->id,
        ], 200);
    }

    /**
     * Controller to get details of a conversation
     * 
     * @param int $conversation_id
     * 
     * @return \Illuminate\Http\JsonResponse 
     */
    public function getConversationDetails(GetConversationDetailsRequest $request)
    {
        $conversation = $this->conversationService->getConversationFromId($request->conversation_id); 
        return response()->json($conversation, 200);
    }

    /**
     * Controller for sending a message sent by a user
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(SendMessageRequest $request)
    {
        $message = $this->conversationService->addMessageToConversation(
            $request->input('conversation_id'),
            Auth::user()->id,
            $request->input('message')
        );

        $otherUserIds = ConversationMembers::where('conversation_id', $request->input('conversation_id'))
            ->where('user_id', '!=', Auth::user()->id)
            ->pluck('user_id')
            ->toArray();

        foreach ($otherUserIds as $userId) {
            NewMessageReceivedEvent::dispatch(
                $userId,
                $request->input('conversation_id'),
                $request->input('message'),
                Auth::user()->name,
                $message->conversation->conversation_title
            );
            UserSentMessageEvent::dispatch(
                $userId,
                $request->input('conversation_id'),
                $request->input('message'),
                Auth::user()->name,
                $message->conversation->conversation_title
            );
        }

        return response()->json(['message' => 'Message sent successfully'], 200);
    }   
}

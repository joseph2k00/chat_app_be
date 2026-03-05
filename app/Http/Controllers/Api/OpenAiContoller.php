<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ConversationService;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpenAiContoller extends Controller
{
    protected $openAIService,
        $conversationService;

    public function __construct(
        OpenAIService $openAIService,
        ConversationService $conversationService
    ) {
        $this->openAIService = $openAIService;
        $this->conversationService = $conversationService;
    }

    public function translateMessage(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:conversation_messages,id',
            'target_language' => 'required|string',
        ]);

        if (!$this->conversationService->userHasAccessToMessage(Auth::id(), $request->input('message_id'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission Denied',
            ], 403);
        }
        
        $response = $this->openAIService->translateMessage(
            $request->input('message_id'), 
            $request->input('target_language')
        );

        return response()->json($response['output']);
    }
}

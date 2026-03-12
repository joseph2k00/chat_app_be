<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslateMessageRequest;
use App\Services\ConversationService;
use App\Services\OpenAIService;

class OpenAiContoller extends Controller
{
    protected OpenAIService $openAIService;
    protected ConversationService $conversationService;

    public function __construct(
        OpenAIService $openAIService,
        ConversationService $conversationService
    ) {
        $this->openAIService = $openAIService;
        $this->conversationService = $conversationService;
    }

    /**
     * Controller to translate a given message
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function translateMessage(TranslateMessageRequest $request)
    {
        $response = $this->openAIService->translateMessage(
            $request->input('message_id'), 
            $request->input('target_language')
        );

        return response()->json($response['output']);
    }
}

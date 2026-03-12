<?php

namespace App\Http\Requests;

use App\Services\ConversationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversationService = app(ConversationService::class);

        return $conversationService->isUserMemberOfConversation(
            Auth::id(),
            $this->conversation_id
        );
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string'],
            'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
        ];
    }
}
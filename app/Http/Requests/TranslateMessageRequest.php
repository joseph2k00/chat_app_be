<?php

namespace App\Http\Requests;

use App\Services\ConversationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TranslateMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversationService = app(ConversationService::class);

        return $conversationService->userHasAccessToMessage(
            Auth::id(),
            $this->message_id
        );
    }

    public function rules(): array
    {
        return [
            'message_id' => ['required', 'integer', 'exists:conversation_messages,id'],
            'target_language' => ['required', 'string'],
        ];
    }
}
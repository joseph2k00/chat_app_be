<?php

namespace App\Http\Requests;

use App\Services\ConversationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetConversationDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversationService = app(ConversationService::class);

        $conversationId = $this->route('conversation_id');

        return $conversationService->isUserMemberOfConversation(
            Auth::id(),
            $conversationId
        );
    }

    public function rules(): array
    {
        return [
            'conversation_id' => [
                'required',
                'integer',
                'exists:conversations,id'
            ]
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'conversation_id' => $this->route('conversation_id'),
        ]);
    }
}

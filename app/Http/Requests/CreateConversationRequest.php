<?php

namespace App\Http\Requests;

use App\Rules\UsersNotAlreadyInConversation;
use App\Services\ConversationService;
use Illuminate\Foundation\Http\FormRequest;

class CreateConversationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'other_user_id' => ['required', 'exists:users,id', new UsersNotAlreadyInConversation(
                app(ConversationService::class),
                $this->user()->id
            )],
            'message' => ['required', 'string'],
        ];
    }
}

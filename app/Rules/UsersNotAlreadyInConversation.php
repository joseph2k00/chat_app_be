<?php

namespace App\Rules;

use App\Services\ConversationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log as FacadesLog;

class UsersNotAlreadyInConversation implements ValidationRule
{
    protected $conversationService;
    protected $currentUserId;
    
    public function __construct(
        ConversationService $conversationService,
        int $currentUserId
    ) {
        $this->conversationService = $conversationService;
        $this->currentUserId = $currentUserId;
    }   

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->currentUserId) {
            $fail('User not authenticated.');
            return;
        }

        $existingConversation = $this->conversationService->getExistingConversationBetweenTwoUsers(
            $this->currentUserId,
            (int) $value
        );
        
        if (isset($existingConversation)) {
            $fail('A conversation between this user already exists.');
        }
    }
}

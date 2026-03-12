<?php

namespace App\Services;

use App\Enum\ConversationType;
use App\Models\Conversation;
use App\Models\ConversationMembers;
use App\Models\ConversationMessage;
use App\Models\ConversationType as ModelsConversationType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ConversationService
{
    /**
     * Function to get list of conversations of the authenticated user
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConversationList(): Collection {
        return Conversation::whereHas('members', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })
        ->with(['members.user', 'latestMessage'])
        ->get();
    }

    /**
     * Get conversation details from conversation ID
     * 
     * @param int $convoId
     * 
     * @return \App\Models\Conversation
     */
    public function getConversationFromId(int $convoId): Conversation {
        return Conversation::with(['members.user', 'messages'])->find($convoId);
    }

    /**
     * Function to create new conversation, add members and message
     * 
     * @param array $data Associative array of data to create new conversation
     * 
     * @return array Associative array
     */
    public function createNewConversation(array $data): array
    {
        try {
            DB::beginTransaction();
            $conversation = $this->createConversation();
            $member1 = $this->addUserToConversation($conversation->id, Auth::id());
            $member2 = $this->addUserToConversation($conversation->id, $data['other_user_id']);
            $message = $this->addMessageToConversation(
                $conversation->id,
                Auth::id(),
                $data['message']
            );
            DB::commit();

            return [
                "conversation" => $conversation,
                "member_1" => $member1,
                "member_2" => $member2,
                "message" => $message,
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    /**
     * Function to create new private conversation
     * 
     * @return \App\Models\Conversation
     */
    public function createConversation(): Conversation {
        $conversation = new Conversation();
        $conversation->type_id = ModelsConversationType::where('name', ConversationType::PRIVATE->value)->first()->id;
        $conversation->save();

        return $conversation;
    }

    /**
     * Function to add an user to conversation
     * 
     * @param int $convoId Conversation ID
     * @param int $userId User ID
     * 
     * @return \App\Models\ConversationMembers
     */
    public function addUserToConversation(
        int $convoId,
        int $userId
    ): ConversationMembers {
        $member = new ConversationMembers();
        $member->conversation_id = $convoId;
        $member->user_id = $userId;
        $member->save();
        return $member;
    }

    /**
     * Function to add message sent by an user to the conversation
     * 
     * @param int $convoId Conversation ID
     * @param int $userId User ID
     * @param string $message Message
     * 
     * @return \App\Models\ConversationMessage
     */
    public function addMessageToConversation(
        int $convoId,
        int $userId,
        string $message
    ): ConversationMessage {
        return ConversationMessage::create([
            'conversation_id' => $convoId,
            'sender_id' => $userId,
            'message' => $message,
        ]);
    }

    /**
     * Fetch existing conversation between two users. Returns `null` is no conversation exists
     * 
     * @param int $userId
     * @param int $otherUserId
     * 
     * @return \App\Models\Conversation|null
     */
    public function getExistingConversationBetweenTwoUsers(
        int $userId,
        int $otherUserId
    ): Conversation|null {
        $currentUserConversations = ConversationMembers::where('user_id', $userId)
            ->pluck('conversation_id')
            ->toArray();

        $existingConversation = ConversationMembers::whereIn('conversation_id', $currentUserConversations)
            ->where('user_id', $otherUserId)
            ->first();

        return $existingConversation ? 
            Conversation::find($existingConversation->conversation_id):
            null;
    }

    /**
     * Check if a given user is the member of the conversation
     * 
     * @param int $userId User ID
     * @param int $convoId Conversation ID
     * 
     * @return bool
     */
    public function isUserMemberOfConversation(
        int $userId,
        int $convoId
    ): bool {
        return ConversationMembers::where('conversation_id', $convoId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Check if user has access
     * 
     * @param int $userId
     * @param int $msgId
     * 
     * @return bool
     */
    public function userHasAccessToMessage(
        int $userId,
        int $msgId
    ): bool {
        $message = ConversationMessage::find($msgId);
        return in_array(
            $userId, 
            $message->conversation->members->pluck('user_id')->toArray()
        );
    }
}

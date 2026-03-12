<?php

namespace App\Services;

use App\Enum\ConversationType;
use App\Events\NewMessageReceivedEvent;
use App\Events\UserSentMessageEvent;
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
        return DB::transaction(function () use ($data) {
            $currentUserId = Auth::id();

            $conversation = $this->createConversation();
            $member1 = $this->addUserToConversation($conversation->id, $currentUserId);
            $member2 = $this->addUserToConversation($conversation->id, $data['other_user_id']);
            $message = $this->addMessageToConversation(
                $conversation->id,
                $currentUserId,
                $data['message']
            );

            try {
                $this->sendUserSentMessageNotification(
                    $data['other_user_id'],
                    $conversation->id,
                    $message->message,
                    Auth::user()->name,
                    $message->conversation->conversation_title
                );
            } catch (Throwable $e) {
                Log::error("Failed to send push notification for message ID: {$message->id}");
            }

            return [
                "conversation" => $conversation,
                "member_1" => $member1,
                "member_2" => $member2,
                "message" => $message,
            ];
        });
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
        $converstionMessage = ConversationMessage::create([
            'conversation_id' => $convoId,
            'sender_id' => $userId,
            'message' => $message,
        ]);
        
        try {
            $this->sendPushNotificationToConversationMembers(
                $convoId,
                $message,
                Auth::user()->name,
                $converstionMessage->conversation->conversation_title
            );
        } catch (Throwable $e) {
            Log::error("Failed to send push notification for message ID: {$converstionMessage->id}");
        }

        return $converstionMessage;
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

    /**
     * Send push notification to all members of the conversation except the sender
     * 
     * @param int $convoId Conversation ID
     * @param string $message Message content
     * @param string $senderName Name of the sender
     * @param string $conversationTitle Title of the conversation
     */
    public function sendPushNotificationToConversationMembers(
        int $convoId,
        string $message,
        string $senderName,
        string $conversationTitle
    ): void {
        $otherUserIds = ConversationMembers::where('conversation_id', $convoId)
            ->where('user_id', '!=', Auth::user()->id)
            ->pluck('user_id')
            ->toArray();

        foreach ($otherUserIds as $userId) {
            $this->sendMessageReceivedNotification(
                $userId,
                $convoId,
                $message,
                $senderName,
                $conversationTitle
            );
            $this->sendUserSentMessageNotification(
                $userId,
                $convoId,
                $message,
                $senderName,
                $conversationTitle
            );
        }
    }

    /**
     * Send push notification to a user when they receive a new message in a conversation
     * 
     * @param int $recipientUserId Recipient User ID
     * @param int $convoId Conversation ID
     * @param string $message Message content
     * @param string $senderName Name of the sender
     * @param string $conversationTitle Title of the conversation
     * 
     * @return void
     */
    public function sendMessageReceivedNotification(
        int $recipientUserId,
        int $convoId,
        string $message,
        string $senderName,
        string $conversationTitle
    ): void {
        NewMessageReceivedEvent::dispatch(
            $recipientUserId,
            $convoId,
            $message,
            $senderName,
            $conversationTitle
        );
    }   

    /**
     * Send push notification to a user when they send a new message in a conversation
     *  
     * @param int $recipientUserId Recipient User ID
     * @param int $convoId Conversation ID
     * @param string $message Message content
     * @param string $senderName Name of the sender
     * @param string $conversationTitle Title of the conversation
     * 
     * @return void
     */
    public function sendUserSentMessageNotification(
        int $recipientUserId,
        int $convoId,
        string $message,
        string $senderName,
        string $conversationTitle
    ): void {
        UserSentMessageEvent::dispatch(
            $recipientUserId,
            $convoId,
            $message,
            $senderName,
            $conversationTitle
        );
    }
}

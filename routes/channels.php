<?php

use App\Models\ConversationMembers;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:api']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('message.received.{conversationId}', function (User $user, int $conversationId) {
    return ConversationMembers::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('new.conversation.received.{userId}', function (User $user, int $userId) {
    return  (int) $user->id === (int) $userId;
});
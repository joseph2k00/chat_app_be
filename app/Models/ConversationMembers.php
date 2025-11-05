<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationMembers extends Model
{
    protected $table = 'conversation_members';

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

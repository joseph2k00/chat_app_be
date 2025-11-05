<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    protected $table = 'conversation_messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'username',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getUsernameAttribute()
    {
        return $this->sender ? $this->sender->name : null;
    }
}

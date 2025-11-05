<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enum\ConversationType as ConversationTypeEnum;
use Illuminate\Support\Facades\Auth;

class Conversation extends Model
{
    protected $table = 'conversations';

    protected $hidden = [
        'created_at',
        'updated_at',
        'title'
    ];
    
    protected $appends = [
        'conversation_type',
        'conversation_title'
    ];

    public function members()
    {
        return $this->hasMany(ConversationMembers::class);
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(ConversationMessage::class)->latestOfMany();
    }

    public function type()
    {
        return $this->belongsTo(ConversationType::class, 'type_id');
    }

    public function getConversationTypeAttribute()
    {
        return $this->type ? $this->type->name : null;
    }

    public function getConversationTitleAttribute()
    {
        if ($this->type->name === ConversationTypeEnum::GROUP->name) {
            return $this->title;
        }

        $otherMember = $this->members()->where('user_id', '!=', Auth::user()->id)->first();

        return $otherMember && $otherMember->user ? $otherMember->user->name : null;
    }
}

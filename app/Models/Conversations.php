<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversations extends Model
{
    protected $table = 'conversations';

    public function members()
    {
        return $this->hasMany(ConversationMembers::class);
    }
}

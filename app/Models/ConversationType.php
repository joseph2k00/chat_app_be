<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationType extends Model
{
    protected $table = 'conversation_types';

    public function conversations()
    {
        return $this->hasMany(Conversations::class, 'type_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class AiConversation extends Model
{
    protected $fillable = [
        'user_id',
        'assistant',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function messages()
    {
        return $this->hasMany(AiMessage::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    protected $table = 'chat_histories';

    protected $fillable = [
        'user_id',
        'session_id',
        'role',
        'content',
        'movie_cards',
    ];

    protected $casts = [
        'movie_cards' => 'array',
    ];
}
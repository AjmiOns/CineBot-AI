<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tmdb_id',
        'movie_title',
        'liked',
        'rating',
        'watched_at',
        'search_count',
    ];

    protected $casts = [
        'liked'      => 'boolean',
        'rating'     => 'integer',
        'watched_at' => 'datetime',
    ];
}

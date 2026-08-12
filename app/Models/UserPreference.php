<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'preference_key',
        'preference_value',
        'score',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    /**
     * Incrémente (ou crée) le score d'une préférence apprise pour un utilisateur.
     * Ex: UserPreference::bump($userId, 'genre', 'Science-Fiction', +2);
     *
     * Le score ne descend jamais en dessous de 0 : une préférence "oubliée"
     * disparaît progressivement du classement plutôt que de devenir négative.
     */
    public static function bump(?int $userId, string $key, ?string $value, int $delta = 1): void
    {
        if (!$userId || !$value) {
            return;
        }

        $row = static::firstOrNew([
            'user_id'           => $userId,
            'preference_key'    => $key,
            'preference_value'  => $value,
        ]);

        $row->score = max(0, ($row->score ?? 0) + $delta);
        $row->save();
    }

    /**
     * Retourne le top des valeurs apprises pour une clé donnée
     * (ex: top genres, top acteurs) triées par score décroissant.
     */
    public static function topFor(int $userId, string $key, int $limit = 8)
    {
        return static::where('user_id', $userId)
            ->where('preference_key', $key)
            ->where('score', '>', 0)
            ->orderByDesc('score')
            ->limit($limit)
            ->get(['preference_value', 'score']);
    }
}

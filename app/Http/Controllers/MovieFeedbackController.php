<?php

namespace App\Http\Controllers;

use App\Models\MovieInteraction;
use App\Models\UserPreference;
use App\Services\TmdbClient;
use Illuminate\Http\Request;

class MovieFeedbackController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // POST /api/movies/feedback
    // Body: { tmdb_id, title, poster_path?, action: like|dislike|watched|rate, rating? }
    // ─────────────────────────────────────────────────────────────────────
    public function feedback(Request $request)
    {
        $data = $request->validate([
            'tmdb_id'     => 'required|integer',
            'title'       => 'required|string|max:255',
            'poster_path' => 'nullable|string',
            'action'      => 'required|in:like,dislike,watched,rate',
            'rating'      => 'nullable|integer|min:1|max:5',
        ]);

        if ($data['action'] === 'rate' && empty($data['rating'])) {
            return response()->json(['error' => 'A rating from 1 to 5 is required.'], 422);
        }

        $userId = auth()->id();

        /** @var MovieInteraction $interaction */
        $interaction = MovieInteraction::firstOrNew([
            'user_id' => $userId,
            'tmdb_id' => $data['tmdb_id'],
        ]);
        $interaction->movie_title = $data['title'];

        switch ($data['action']) {
            case 'like':
                $interaction->liked = true;
                $interaction->watched_at ??= now();
                break;
            case 'dislike':
                $interaction->liked = false;
                $interaction->watched_at ??= now();
                break;
            case 'watched':
                $interaction->watched_at = now();
                break;
            case 'rate':
                $interaction->rating = $data['rating'];
                $interaction->watched_at ??= now();
                break;
        }

        $interaction->save();

        // ── Apprentissage du profil (genres / acteurs / réalisateur / langue) ──
        $meta = TmdbClient::details($data['tmdb_id']);

        if ($meta) {
            $delta = match ($data['action']) {
                'like'    => 2,
                'dislike' => -2,
                'watched' => 1,
                'rate'    => ($data['rating'] ?? 3) - 3, // note 1★→-2 ... 5★→+2
            };

            foreach ($meta['genres'] as $genre) {
                UserPreference::bump($userId, 'genre', $genre, $delta);
            }
            foreach ($meta['actors'] as $actor) {
                UserPreference::bump($userId, 'actor', $actor, $delta);
            }
            if ($meta['director']) {
                UserPreference::bump($userId, 'director', $meta['director'], $delta);
            }
            if ($meta['language']) {
                // La langue reflète une habitude de visionnage, pas un jugement :
                // toujours +1, qu'on ait aimé ou non le film.
                UserPreference::bump($userId, 'language', $meta['language'], 1);
            }
        }

        return response()->json([
            'success'     => true,
            'interaction' => $interaction->only(['tmdb_id', 'movie_title', 'liked', 'rating', 'watched_at']),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/user/favorites — films likés par l'utilisateur connecté
    // ─────────────────────────────────────────────────────────────────────
    public function favorites()
    {
        $movies = MovieInteraction::where('user_id', auth()->id())
            ->where('liked', true)
            ->orderByDesc('updated_at')
            ->get(['tmdb_id', 'movie_title', 'rating', 'watched_at']);

        return response()->json($movies);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/user/watched — historique des films vus / notés
    // ─────────────────────────────────────────────────────────────────────
    public function watched()
    {
        $movies = MovieInteraction::where('user_id', auth()->id())
            ->whereNotNull('watched_at')
            ->orderByDesc('watched_at')
            ->get(['tmdb_id', 'movie_title', 'liked', 'rating', 'watched_at']);

        return response()->json($movies);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/user/preferences — profil appris (genres/acteurs/réalisateurs/langues)
    // ─────────────────────────────────────────────────────────────────────
    public function preferences()
    {
        $userId = auth()->id();

        $grouped = UserPreference::where('user_id', $userId)
            ->where('score', '>', 0)
            ->orderByDesc('score')
            ->get()
            ->groupBy('preference_key')
            ->map(fn ($rows) => $rows->take(8)->map(fn ($r) => [
                'value' => $r->preference_value,
                'score' => $r->score,
            ])->values());

        return response()->json([
            'genres'    => $grouped->get('genre', collect()),
            'actors'    => $grouped->get('actor', collect()),
            'directors' => $grouped->get('director', collect()),
            'languages' => $grouped->get('language', collect()),
        ]);
    }
}

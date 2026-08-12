<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ChatHistory;
use App\Models\UserPreference;
use Illuminate\Support\Str;

class CinebotController extends Controller
{
    private function aiApiUrl(): string
    {
        return env('AI_API_URL', 'http://127.0.0.1:8001');
    }

    /**
     * Récupère le profil de goûts appris de l'utilisateur (genres/acteurs/
     * réalisateurs préférés, calculés à partir de ses like/dislike/note réels)
     * pour le transmettre au moteur de ranking hybride de FastAPI.
     * Retourne des tableaux vides pour un invité ou un nouvel utilisateur.
     */
    private function tastePayload(): array
    {
        $userId = auth()->id();
        if (!$userId) {
            return ['genres' => [], 'actors' => [], 'directors' => []];
        }

        return [
            'genres'    => UserPreference::topFor($userId, 'genre', 3)->pluck('preference_value')->all(),
            'actors'    => UserPreference::topFor($userId, 'actor', 3)->pluck('preference_value')->all(),
            'directors' => UserPreference::topFor($userId, 'director', 2)->pluck('preference_value')->all(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/chat
    // Le frontend gère désormais lui-même le session_id (une conversation =
    // un session_id), pour permettre plusieurs discussions distinctes comme
    // dans Claude/ChatGPT. Si aucun session_id n'est fourni (première
    // discussion), on en génère un et on le renvoie au frontend.
    // ─────────────────────────────────────────────────────────────────────────
    public function chat(Request $request)
    {
        $request->validate([
            'message'    => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $userId    = auth()->id();
        $sessionId = $request->input('session_id') ?: (string) Str::uuid();
        $taste     = $this->tastePayload();

        try {
            $response = Http::timeout(60)->post($this->aiApiUrl() . '/chat', [
                'message'             => $request->message,
                'user_id'             => (string) ($userId ?? 'guest'),
                'session_id'          => $sessionId,
                'preferred_genres'    => $taste['genres'],
                'preferred_actors'    => $taste['actors'],
                'preferred_directors' => $taste['directors'],
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::channel('cinebot')->error('FastAPI unreachable', ['error' => $e->getMessage(), 'session_id' => $sessionId]);
            return response()->json([
                'reply'              => '⚠️ The AI service is unreachable. Make sure the Python server is running: `uvicorn main:app --port 8001`',
                'recommended_movies' => [],
                'session_id'         => $sessionId,
            ], 503);
        } catch (\Exception $e) {
            Log::channel('cinebot')->error('FastAPI connection failed', ['error' => $e->getMessage(), 'session_id' => $sessionId]);
            return response()->json([
                'reply'              => '⚠️ Could not connect to the AI service.',
                'recommended_movies' => [],
                'session_id'         => $sessionId,
            ], 503);
        }

        if ($response->failed()) {
            Log::channel('cinebot')->error('FastAPI returned error', [
                'status'     => $response->status(),
                'body'       => $response->body(),
                'session_id' => $sessionId,
            ]);
            return response()->json([
                'reply'              => '⚠️ AI service error (' . $response->status() . '). Please try again.',
                'recommended_movies' => [],
                'session_id'         => $sessionId,
            ], 502);
        }

        $data = $response->json();

        if (!is_array($data)) {
            Log::channel('cinebot')->error('FastAPI returned non-JSON', ['body' => $response->body(), 'session_id' => $sessionId]);
            return response()->json([
                'reply'              => '⚠️ Unexpected response from AI service.',
                'recommended_movies' => [],
                'session_id'         => $sessionId,
            ], 502);
        }

        $reply             = $data['reply']              ?? 'No response received.';
        $recommendedMovies = $data['recommended_movies'] ?? [];

        // Persist to DB (non-blocking)
        try {
            ChatHistory::create([
                'user_id'     => $userId,
                'session_id'  => $sessionId,
                'role'        => 'user',
                'content'     => $request->message,
                'movie_cards' => null,
            ]);
            ChatHistory::create([
                'user_id'     => $userId,
                'session_id'  => $sessionId,
                'role'        => 'assistant',
                'content'     => $reply,
                'movie_cards' => json_encode($recommendedMovies),
            ]);
        } catch (\Exception $e) {
            Log::channel('cinebot')->warning('ChatHistory save failed', ['error' => $e->getMessage(), 'session_id' => $sessionId]);
        }

        return response()->json([
            'reply'              => $reply,
            'recommended_movies' => $recommendedMovies,
            'session_id'         => $sessionId,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/recommendations/{userId}
    // Mis en cache par utilisateur (clé incluant son profil de goûts, pour ne
    // jamais servir un cache périmé après un nouveau like/dislike) — évite un
    // aller-retour TMDB à chaque ouverture du chat, sans sacrifier la fraîcheur
    // de la personnalisation. Les échecs ne sont jamais mis en cache : une
    // panne transitoire du service IA/TMDB ne doit pas priver l'utilisateur
    // de recommandations pendant 15 minutes après le rétablissement.
    // ─────────────────────────────────────────────────────────────────────────
    public function recommendations()
    {
        $userId = auth()->id() ?? 'guest';
        $taste  = $this->tastePayload();

        $cacheKey = 'recommendations:' . $userId . ':' . md5(json_encode($taste));

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($this->aiApiUrl() . "/recommendations/{$userId}", [
                    'genres'    => implode(',', $taste['genres']),
                    'actors'    => implode(',', $taste['actors']),
                    'directors' => implode(',', $taste['directors']),
                ]);

            if ($response->failed()) {
                return response()->json(['movies' => []], 502);
            }

            $data = $response->json();
            Cache::put($cacheKey, $data, now()->addMinutes(15));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::channel('cinebot')->warning('Recommendations failed: ' . $e->getMessage());
            return response()->json(['movies' => []], 503);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/chat/history — historique complet à plat (toutes sessions),
    // conservé pour compatibilité / export éventuel.
    // ─────────────────────────────────────────────────────────────────────────
    public function history()
    {
        $messages = ChatHistory::where('user_id', auth()->id())
            ->orderBy('created_at')
            ->get(['role', 'content', 'movie_cards', 'created_at']);

        return response()->json($messages);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/chat/sessions — liste des discussions (comme la barre latérale
    // de Claude/ChatGPT) : une ligne par session_id, triée par activité récente.
    // ─────────────────────────────────────────────────────────────────────────
    public function sessions()
    {
        $userId = auth()->id();

        $sessions = ChatHistory::where('user_id', $userId)
            ->selectRaw('session_id, MIN(created_at) as started_at, MAX(created_at) as last_at')
            ->groupBy('session_id')
            ->orderByDesc('last_at')
            ->get();

        $result = $sessions->map(function ($s) use ($userId) {
            $firstUserMessage = ChatHistory::where('user_id', $userId)
                ->where('session_id', $s->session_id)
                ->where('role', 'user')
                ->orderBy('created_at')
                ->value('content');

            return [
                'session_id' => $s->session_id,
                'title'      => Str::limit($firstUserMessage ?? 'Nouvelle discussion', 40),
                'started_at' => $s->started_at,
                'last_at'    => $s->last_at,
            ];
        });

        return response()->json($result->values());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/chat/sessions/{sessionId} — messages d'une discussion précise
    // ─────────────────────────────────────────────────────────────────────────
    public function sessionMessages(string $sessionId)
    {
        $messages = ChatHistory::where('user_id', auth()->id())
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get(['role', 'content', 'movie_cards', 'created_at']);

        if ($messages->isEmpty()) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        return response()->json($messages);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /api/chat/sessions/{sessionId} — supprime une discussion
    // ─────────────────────────────────────────────────────────────────────────
    public function deleteSession(string $sessionId)
    {
        $deleted = ChatHistory::where('user_id', auth()->id())
            ->where('session_id', $sessionId)
            ->delete();

        if ($deleted === 0) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/trending
    // Identique pour tous les utilisateurs → un seul cache global, 60 minutes.
    // Le contenu tendance TMDB ne varie pas assez vite pour justifier un appel
    // réseau à chaque chargement de la sidebar.
    // ─────────────────────────────────────────────────────────────────────────
    public function trending()
    {
        if (Cache::has('trending_movies')) {
            return response()->json(Cache::get('trending_movies'));
        }

        try {
            $response = Http::timeout(15)->get($this->aiApiUrl() . '/trending');
            if ($response->failed()) {
                return response()->json(['movies' => []], 502);
            }

            $data = $response->json();
            Cache::put('trending_movies', $data, now()->addMinutes(60));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::channel('cinebot')->warning('Trending fetch failed: ' . $e->getMessage());
            return response()->json(['movies' => []], 503);
        }
    }
}

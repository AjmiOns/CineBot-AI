<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TmdbClient
{
    /**
     * Récupère les métadonnées d'un film (genres, acteurs principaux, réalisateur,
     * langue originale) pour alimenter le profilage utilisateur.
     * Retourne null si TMDB_API_KEY n'est pas configurée ou si l'appel échoue —
     * le feedback (like/dislike/note) est alors quand même enregistré, seul
     * l'apprentissage des préférences est simplement ignoré.
     */
    public static function details(int $tmdbId): ?array
    {
        $key = config('services.tmdb.key');

        if (!$key) {
            return null;
        }

        try {
            $response = Http::timeout(8)->get("https://api.themoviedb.org/3/movie/{$tmdbId}", [
                'api_key'             => $key,
                'append_to_response'  => 'credits',
                'language'            => 'fr-FR',
            ]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();

            $director = collect($data['credits']['crew'] ?? [])
                ->firstWhere('job', 'Director');

            return [
                'genres'   => collect($data['genres'] ?? [])->pluck('name')->filter()->values()->all(),
                'actors'   => collect($data['credits']['cast'] ?? [])->take(3)->pluck('name')->filter()->values()->all(),
                'director' => $director['name'] ?? null,
                'language' => $data['original_language'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::channel('cinebot')->warning('TMDB enrichment failed', [
                'tmdb_id' => $tmdbId,
                'error'   => $e->getMessage(),
            ]);
            return null;
        }
    }
}

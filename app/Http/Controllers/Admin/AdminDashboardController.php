<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Models\MovieInteraction;
use App\Models\User;
use App\Models\UserPreference;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/admin/stats — toutes les métriques du dashboard en un seul appel
    // ─────────────────────────────────────────────────────────────────────────
    public function stats()
    {
        return response()->json($this->buildStats());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/export — export CSV des mêmes métriques, pour analyse hors
    // ligne (Excel/LibreOffice) ou preuve à l'appui pendant la soutenance.
    // ─────────────────────────────────────────────────────────────────────────
    public function exportCsv(): StreamedResponse
    {
        $stats    = $this->buildStats();
        $filename = 'cinebot-stats-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($stats) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 pour qu'Excel affiche correctement les accents
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['CineBot AI — Statistics Export', now()->format('m/d/Y H:i')]);
            fputcsv($out, []);

            fputcsv($out, ['USERS']);
            fputcsv($out, ['Total registered', $stats['users']['total']]);
            fputcsv($out, ['New (30 days)', $stats['users']['new_30d']]);
            fputcsv($out, ['Active (7 days)', $stats['users']['active_7d']]);
            fputcsv($out, ['Active (30 days)', $stats['users']['active_30d']]);
            fputcsv($out, []);

            fputcsv($out, ['CHATBOT USAGE']);
            fputcsv($out, ['Total messages', $stats['usage']['total_messages']]);
            fputcsv($out, ['Total conversations', $stats['usage']['total_conversations']]);
            fputcsv($out, ['Average messages / user', $stats['usage']['avg_messages_per_user']]);
            fputcsv($out, []);

            fputcsv($out, ['DAILY USAGE (LAST 14 DAYS)']);
            fputcsv($out, ['Date', 'Messages', 'Active users']);
            foreach ($stats['usage']['daily'] as $day) {
                fputcsv($out, [$day->day, $day->messages, $day->active_users]);
            }
            fputcsv($out, []);

            fputcsv($out, ['FEEDBACK STATISTICS']);
            fputcsv($out, ['Likes (👍)', $stats['feedback']['likes']]);
            fputcsv($out, ['Dislikes (👎)', $stats['feedback']['dislikes']]);
            fputcsv($out, ['Ratings given (⭐)', $stats['feedback']['ratings']]);
            fputcsv($out, ['Average rating', $stats['feedback']['avg_rating']]);
            fputcsv($out, ['Movies marked "watched"', $stats['feedback']['watched']]);
            fputcsv($out, []);

            fputcsv($out, ['MOST LIKED GENRES']);
            fputcsv($out, ['Genre', 'Cumulative score']);
            foreach ($stats['top_genres'] as $genre) {
                fputcsv($out, [$genre->preference_value, $genre->total_score]);
            }
            fputcsv($out, []);

            fputcsv($out, ['MOST LIKED MOVIES']);
            fputcsv($out, ['Title', 'Likes', 'Average rating']);
            foreach ($stats['top_liked_movies'] as $movie) {
                fputcsv($out, [$movie['movie_title'], $movie['likes'], $movie['avg_rating']]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Logique partagée entre la vue JSON (dashboard) et l'export CSV, pour ne
    // jamais laisser les deux formats diverger silencieusement.
    // ─────────────────────────────────────────────────────────────────────────
    private function buildStats(): array
    {
        $now     = now();
        $since7  = $now->copy()->subDays(7);
        $since14 = $now->copy()->subDays(14);
        $since30 = $now->copy()->subDays(30);

        // ── Genres les plus appréciés ────────────────────────────────────────
        // Agrégés depuis les préférences réellement apprises (like/dislike/note
        // enrichis via TMDB), tous utilisateurs confondus — un signal plus
        // fiable qu'un simple comptage de mots-clés recherchés.
        $topGenres = UserPreference::query()
            ->where('preference_key', 'genre')
            ->select('preference_value')
            ->selectRaw('SUM(score) as total_score')
            ->groupBy('preference_value')
            ->orderByDesc('total_score')
            ->limit(10)
            ->get();

        // ── Films les plus likés ─────────────────────────────────────────────
        $topLikedMovies = MovieInteraction::query()
            ->where('liked', true)
            ->select('movie_title')
            ->selectRaw('COUNT(*) as likes')
            ->selectRaw('AVG(rating) as avg_rating')
            ->groupBy('movie_title')
            ->orderByDesc('likes')
            ->limit(10)
            ->get()
            ->map(fn ($m) => [
                'movie_title' => $m->movie_title,
                'likes'       => (int) $m->likes,
                'avg_rating'  => $m->avg_rating ? round((float) $m->avg_rating, 1) : null,
            ]);

        // ── Utilisateurs ─────────────────────────────────────────────────────
        $totalUsers     = User::count();
        $newUsers30d    = User::where('created_at', '>=', $since30)->count();
        $activeUsers7d  = ChatHistory::where('created_at', '>=', $since7)->distinct('user_id')->count('user_id');
        $activeUsers30d = ChatHistory::where('created_at', '>=', $since30)->distinct('user_id')->count('user_id');

        // ── Usage du chatbot (14 derniers jours) ─────────────────────────────
        $dailyUsage = ChatHistory::where('created_at', '>=', $since14)
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('COUNT(*) as messages')
            ->selectRaw('COUNT(DISTINCT user_id) as active_users')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $totalMessages      = ChatHistory::count();
        $totalConversations = ChatHistory::distinct('session_id')->count('session_id');
        $avgMessagesPerUser = $totalUsers > 0 ? round($totalMessages / $totalUsers, 1) : 0;

        // ── Statistiques de feedback ─────────────────────────────────────────
        $totalLikes    = MovieInteraction::where('liked', true)->count();
        $totalDislikes = MovieInteraction::where('liked', false)->count();
        $totalRatings  = MovieInteraction::whereNotNull('rating')->count();
        $avgRating     = MovieInteraction::whereNotNull('rating')->avg('rating');
        $totalWatched  = MovieInteraction::whereNotNull('watched_at')->count();

        return [
            'top_genres'       => $topGenres,
            'top_liked_movies' => $topLikedMovies,
            'users' => [
                'total'      => $totalUsers,
                'new_30d'    => $newUsers30d,
                'active_7d'  => $activeUsers7d,
                'active_30d' => $activeUsers30d,
            ],
            'usage' => [
                'total_messages'        => $totalMessages,
                'total_conversations'   => $totalConversations,
                'avg_messages_per_user' => $avgMessagesPerUser,
                'daily'                 => $dailyUsage,
            ],
            'feedback' => [
                'likes'      => $totalLikes,
                'dislikes'   => $totalDislikes,
                'ratings'    => $totalRatings,
                'watched'    => $totalWatched,
                'avg_rating' => $avgRating ? round((float) $avgRating, 2) : null,
            ],
        ];
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL refuse de supprimer l'index composite (user_id, tmdb_id) tant
        // que la contrainte de clé étrangère sur user_id s'appuie dessus.
        // On retire donc d'abord la FK, puis l'index, avant de restructurer
        // la table — et on recrée la FK à la fin.
        Schema::table('movie_interactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('movie_interactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'tmdb_id']);
        });

        Schema::table('movie_interactions', function (Blueprint $table) {
            $table->dropColumn('action');
        });

        Schema::table('movie_interactions', function (Blueprint $table) {
            // null = pas d'avis donné, true = 👍 liked, false = 👎 disliked
            $table->boolean('liked')->nullable()->after('movie_title');

            // Note 1 à 5 étoiles (⭐), optionnelle
            $table->unsignedTinyInteger('rating')->nullable()->after('liked');

            // Date à laquelle le film a été marqué comme "vu"
            $table->timestamp('watched_at')->nullable()->after('rating');

            // Nombre de fois où ce film a été cherché/mentionné (pour pondérer le ranking)
            $table->unsignedInteger('search_count')->default(0)->after('watched_at');

            $table->unique(['user_id', 'tmdb_id']);

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_interactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('movie_interactions', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'tmdb_id']);
            $table->dropColumn(['liked', 'rating', 'watched_at', 'search_count']);
        });

        Schema::table('movie_interactions', function (Blueprint $table) {
            $table->enum('action', ['liked', 'disliked', 'viewed', 'searched'])->default('viewed');
            $table->index(['user_id', 'tmdb_id']);
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }
};

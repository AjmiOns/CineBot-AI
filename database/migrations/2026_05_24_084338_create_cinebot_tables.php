<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =========================
        // CHAT HISTORIES
        // =========================
        Schema::create('chat_histories', function (Blueprint $table) {
            $table->id();

            // FIX: nullable() so guests (non-logged-in users) don't crash the insert
            // FIX: nullOnDelete() instead of cascade so deleting a user
            //      keeps their chat history (change to cascadeOnDelete() if you prefer purging)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->string('session_id')->index();

            $table->enum('role', ['user', 'assistant']);

            $table->text('content');

            $table->json('movie_cards')->nullable();

            $table->timestamps();
        });

        // =========================
        // USER PREFERENCES
        // =========================
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->string('preference_key');
            $table->string('preference_value');

            $table->integer('score')->default(1);

            $table->timestamps();

            // FIX: unique constraint so upsert works cleanly instead of creating duplicates
            $table->unique(['user_id', 'preference_key', 'preference_value']);
        });

        // =========================
        // MOVIE INTERACTIONS
        // =========================
        Schema::create('movie_interactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->integer('tmdb_id');
            $table->string('movie_title');

            $table->enum('action', ['liked', 'disliked', 'viewed', 'searched']);

            $table->timestamps();

            $table->index(['user_id', 'tmdb_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_interactions');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('chat_histories');
    }
};
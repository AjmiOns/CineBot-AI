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
        Schema::table('users', function (Blueprint $table) {
            // Nécessaire pour le futur dashboard admin (statistiques, gestion des rôles)
            $table->boolean('is_admin')->default(false)->after('password');

            // Champs utiles pour la personnalisation des recommandations (préférences globales)
            $table->string('preferred_language', 5)->default('fr')->after('is_admin');
            $table->timestamp('last_login_at')->nullable()->after('preferred_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'preferred_language', 'last_login_at']);
        });
    }
};

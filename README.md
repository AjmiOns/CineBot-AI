# 🎬 CineBot AI

**Assistant cinématographique intelligent** combinant une architecture RAG hybride (recherche dense + sparse), un LLM (Groq / Llama 3.1) et des données TMDB en temps réel, pour offrir des recommandations de films personnalisées, expliquées et vérifiables.

Projet de stage d'été — 1ère année Cycle Ingénieur, TEK-UP University.

---

## Sommaire

- [Aperçu](#aperçu)
- [Fonctionnalités](#fonctionnalités)
- [Architecture](#architecture)
- [Stack technique](#stack-technique)
- [Structure du projet](#structure-du-projet)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Démarrage rapide avec Docker](#démarrage-rapide-avec-docker)
- [Variables d'environnement](#variables-denvironnement)
- [Lancer le projet](#lancer-le-projet)
- [Schéma de base de données](#schéma-de-base-de-données)
- [Référence API](#référence-api)
- [Documentation interactive (Swagger)](#documentation-interactive-swagger)
- [Cache & performance](#cache--performance)
- [Logs & observabilité](#logs--observabilité)
- [Tests automatisés](#tests-automatisés)
- [Sécurité & gestion de compte](#sécurité--gestion-de-compte)
- [Devenir administrateur](#devenir-administrateur)
- [Feuille de route](#feuille-de-route)
- [Contribuer](#contribuer)
- [Licence](#licence)

---

## Aperçu

CineBot AI est un chatbot cinéma full-stack qui ne se contente pas d'interroger un LLM : chaque réponse est **ancrée** dans des données réelles (TMDB) via un pipeline de retrieval hybride, puis **re-classée** par un moteur de ranking qui tient compte des goûts appris de chaque utilisateur, avant que Groq ne rédige l'explication finale.

```
"Recommande-moi un bon film de science-fiction"
        │
        ▼
┌───────────────────────────────────────────────────────────┐
│  1. Retrieval hybride : FAISS (dense) + BM25 (sparse)      │
│     fusionnés par Reciprocal Rank Fusion                    │
│  2. Enrichissement TMDB (rating, popularité, genres, cast)  │
│  3. Ranking hybride : pertinence + note + popularité         │
│     + préférences apprises (like/dislike/note réels)        │
│  4. Groq (Llama 3.1) rédige l'explication personnalisée      │
└───────────────────────────────────────────────────────────┘
        │
        ▼
   Réponse + cartes films classées, avec 👍 👎 ⭐ pour continuer
   à affiner le profil de l'utilisateur
```

## Fonctionnalités

- **Chat conversationnel** avec résolution d'anaphores ("parle-moi du premier", "même réalisateur que lui")
- **Multi-discussions** façon Claude/ChatGPT : historique dans une barre latérale, bouton "Nouvelle discussion"
- **Authentification complète** : inscription, connexion, déconnexion, **récupération de mot de passe**, **suppression de compte** (sessions Laravel)
- **Profilage utilisateur réel** : genres, acteurs, réalisateurs et langues préférés, appris à partir des interactions (enrichi via l'API TMDB, pas de simple mot-clé)
- **Feedback Learning** : 👍 / 👎 / ⭐ (1 à 5) sur chaque recommandation, qui met à jour le profil en direct
- **Moteur de ranking hybride** combinant pertinence RAG, note TMDB, popularité et préférences apprises — avec un badge "🎯 XX% match" affiché sur chaque recommandation pour rendre le score visible, pas juste fonctionnel en coulisses
- **Dashboard admin** : utilisateurs actifs, genres les plus appréciés, films les plus likés, usage du chatbot, statistiques de feedback, **export CSV en un clic**
- **Sécurité applicative** : rate limiting sur les endpoints sensibles, confirmation par mot de passe pour les actions destructives, gestion RGPD des données personnelles
- **Thème clair / sombre** synchronisé sur toutes les pages
- **Filtrage de contenu adulte** à plusieurs niveaux (TMDB, documents RAG, prompt système)

## Architecture

Le projet est composé de **deux services indépendants** qui communiquent en HTTP :

```
┌───────────────┐      HTTP/JSON       ┌────────────────────┐      HTTP      ┌──────────┐
│   Frontend     │ ───────────────────▶ │   Backend Laravel   │ ─────────────▶ │ Groq API  │
│  Blade + JS    │ ◀─────────────────── │   (PHP / MySQL)      │                └──────────┘
└───────────────┘                      └──────────┬──────────┘
                                                   │ HTTP/JSON
                                                   ▼
                                        ┌─────────────────────┐      HTTP      ┌──────────┐
                                        │  Service IA Python    │ ─────────────▶ │ TMDB API  │
                                        │  FastAPI + FAISS      │                └──────────┘
                                        └─────────────────────┘
```

- **Laravel** gère l'authentification, la persistance (historique, favoris, préférences), et fait office de proxy sécurisé vers le service Python (aucune clé API n'est jamais exposée au navigateur).
- **FastAPI** gère le pipeline RAG (FAISS + BM25), le ranking hybride, et l'appel à Groq.
- Les deux services communiquent uniquement via `AI_API_URL` — ils peuvent être déployés séparément.

## Stack technique

| Couche | Technologie |
|---|---|
| Frontend | Blade (PHP templating), JavaScript vanilla, CSS custom |
| Backend applicatif | Laravel 12 (PHP 8.2+), MySQL |
| Service IA | FastAPI (Python), Uvicorn |
| Recherche vectorielle | FAISS + `sentence-transformers/all-MiniLM-L6-v2` |
| Recherche lexicale | BM25 (`rank_bm25`) |
| LLM | Groq API — `llama-3.1-8b-instant` |
| Données films | TMDB API |
| Environnement local | XAMPP (Apache/MySQL) |
| Conteneurisation | Docker Compose (Laravel + MySQL + service IA) |

## Structure du projet

```
cinebot-ai/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/AuthController.php        # inscription / connexion / déconnexion
│   │   │   ├── Admin/AdminDashboardController.php
│   │   │   ├── CinebotController.php          # proxy vers le service IA, historique
│   │   │   └── MovieFeedbackController.php    # like/dislike/note, favoris, préférences
│   │   └── Middleware/EnsureIsAdmin.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── ChatHistory.php
│   │   ├── UserPreference.php                 # profil appris (bump/topFor)
│   │   └── MovieInteraction.php
│   └── Services/TmdbClient.php                # enrichissement TMDB côté Laravel
├── database/migrations/
├── resources/views/
│   ├── auth/{login,register,forgot-password,reset-password}.blade.php
│   ├── admin/dashboard.blade.php
│   ├── chatbot.blade.php                      # interface principale
│   └── profile.blade.php                      # favoris, préférences, suppression de compte
├── routes/{web.php,api.php}
├── docker/entrypoint.sh                       # attend MySQL, migre, démarre Laravel
├── Dockerfile                                  # image Laravel (PHP 8.2 + artisan serve)
├── docker-compose.yml                          # orchestre Laravel + MySQL + service IA
├── ai-service/                                # service Python indépendant
│   ├── main.py            # endpoints FastAPI (/chat, /recommendations, /trending)
│   ├── rag_engine.py      # ingestion TMDB, index FAISS + BM25, retrieval hybride
│   ├── ranking.py          # moteur de ranking hybride (relevance + rating + pop + prefs)
│   ├── groq_service.py     # prompt engineering + appel Groq
│   ├── tmdb_service.py     # recherche TMDB temps réel
│   ├── memory.py           # mémoire conversationnelle par session
│   ├── requirements.txt    # dépendances Python figées
│   ├── Dockerfile          # image service IA (Python 3.11 + FAISS)
│   └── faiss_movie_index/  # index vectoriel persistant (généré, non versionné)
└── .env / .env.example
```

## Prérequis

- PHP ≥ 8.2, Composer
- MySQL ≥ 8.0 (ou MariaDB équivalent)
- Python ≥ 3.10, pip
- Une clé [Groq API](https://console.groq.com) (gratuite)
- Une clé [TMDB API](https://www.themoviedb.org/settings/api) (gratuite)

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/AjmiOns/CineBot-AI.git
cd CineBot-AI
```

### 2. Backend Laravel

```bash
composer install
copy .env.example .env        # cp .env.example .env sous Linux/Mac
php artisan key:generate
```

Configurer `DB_*` dans `.env` puis :

```bash
php artisan migrate
```

### 3. Service IA (Python)

```bash
cd ai-service
python -m venv venv
venv\Scripts\activate          # source venv/bin/activate sous Linux/Mac
pip install -r requirements.txt
```

## Démarrage rapide avec Docker

Alternative à l'installation manuelle ci-dessus : les 3 services (Laravel, MySQL, service IA) démarrent en une seule commande, avec les bonnes versions et sans dépendre de la configuration de la machine locale (XAMPP, versions PHP/Python, etc.).

**Prérequis** :
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et lancé (sur Windows, nécessite WSL 2 — `wsl --install` puis redémarrage si ce n'est pas déjà activé)
- `.env` (racine) et `ai-service/.env` déjà remplis avec tes clés (voir [Variables d'environnement](#variables-denvironnement)) — Docker Compose ne les génère pas à ta place

```bash
docker compose up --build
```

Le tout premier lancement est long (5-15 min selon la connexion) : téléchargement des images de base, installation des dépendances PHP/Python, et téléchargement du modèle d'embedding HuggingFace. Les lancements suivants sont bien plus rapides grâce au cache Docker.

| Service | URL |
|---|---|
| Application Laravel | `http://localhost:8000` |
| Service IA (Swagger) | `http://localhost:8001/docs` |
| MySQL (côté hôte, si besoin d'un accès direct) | `localhost:3307` *(3307 pour ne pas entrer en conflit avec un MySQL local déjà sur 3306, ex. XAMPP)* |

Ce que fait `docker compose up` automatiquement :
- Construit l'image Laravel (PHP 8.2 + extensions) et l'image du service IA (Python 3.11 + FAISS/sentence-transformers)
- Attend que MySQL soit prêt (`healthcheck`) avant de démarrer Laravel
- Applique les migrations (`php artisan migrate --force`) au démarrage du conteneur Laravel
- Persiste la base MySQL et l'index FAISS dans des volumes Docker nommés, pour ne pas tout reconstruire à chaque redémarrage

⚠️ **Base de données isolée** — le conteneur MySQL démarre avec une base **vide et totalement séparée** de celle utilisée en local (XAMPP). Les comptes utilisateurs créés dans un environnement n'existent pas dans l'autre : il faut recréer un compte via `/register` (ou réimporter un dump SQL) après le premier `docker compose up`.

Pour arrêter :
```bash
docker compose down
```

Pour repartir de zéro (supprime aussi les volumes — base de données et index FAISS) :
```bash
docker compose down -v
```

## Variables d'environnement

**`.env` (Laravel, à la racine)**

| Variable | Description |
|---|---|
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Connexion MySQL |
| `AI_API_URL` | URL du service Python (ex. `http://127.0.0.1:8001`) |
| `TMDB_API_KEY` | Utilisée pour l'enrichissement du profil (genres/acteurs/réalisateurs) |

**`ai-service/.env`**

| Variable | Description |
|---|---|
| `GROQ_API_KEY` | Clé de l'API Groq |
| `TMDB_API_KEY` | Clé TMDB (recherche temps réel + construction de l'index RAG) |

## Lancer le projet

Deux processus doivent tourner en parallèle, dans deux terminaux séparés :

```bash
# Terminal 1 — backend Laravel
php artisan serve

# Terminal 2 — service IA
cd ai-service
uvicorn main:app --port 8001 --reload
```

Au premier démarrage, `uvicorn` construit automatiquement l'index FAISS s'il est absent (peut prendre quelques minutes selon le nombre de films configuré dans `build_index()`).

Accéder ensuite à `http://localhost:8000`.

## Schéma de base de données

| Table | Rôle |
|---|---|
| `users` | Comptes utilisateurs (+ `is_admin`, `preferred_language`) |
| `chat_histories` | Messages, groupés par `session_id` (une conversation) |
| `user_preferences` | Profil appris : `preference_key` (genre/actor/director/language) × `preference_value` × `score` |
| `movie_interactions` | Une ligne par (utilisateur, film) : `liked`, `rating`, `watched_at` |

## Référence API

Toutes les routes `/api/*` nécessitent une session authentifiée (sauf `/api/chat` et `/api/recommendations`, accessibles en invité avec des fonctionnalités réduites).

| Méthode | Route | Description |
|---|---|---|
| `POST` | `/api/chat` | Envoie un message, reçoit une réponse + recommandations classées *(limité à 20 req/min)* |
| `GET` | `/api/chat/sessions` | Liste des discussions de l'utilisateur |
| `GET` | `/api/chat/sessions/{id}` | Messages d'une discussion précise |
| `DELETE` | `/api/chat/sessions/{id}` | Supprime une discussion |
| `POST` | `/api/movies/feedback` | Enregistre un like / dislike / note sur un film |
| `GET` | `/api/user/favorites` | Films likés |
| `GET` | `/api/user/preferences` | Profil appris (genres/acteurs/réalisateurs) |
| `GET` | `/api/admin/stats` | Statistiques globales (admin uniquement) |
| `GET` | `/admin/export` | Export CSV des statistiques (admin uniquement) |

Routes web associées à l'authentification (hors `/api`) :

| Méthode | Route | Description |
|---|---|---|
| `GET/POST` | `/forgot-password` | Demande de réinitialisation de mot de passe *(limité à 5 req/min)* |
| `GET/POST` | `/reset-password/{token}` | Application du nouveau mot de passe |
| `DELETE` | `/profile` | Suppression du compte et de toutes ses données |

## Documentation interactive (Swagger)

Le service IA expose sa documentation OpenAPI générée automatiquement par FastAPI — aucune maintenance manuelle requise :

- **Swagger UI** (interactif, testable directement) : `http://127.0.0.1:8001/docs`
- **ReDoc** (lecture) : `http://127.0.0.1:8001/redoc`
- **Schéma OpenAPI brut** : `http://127.0.0.1:8001/openapi.json`

Utile pour explorer `/chat`, `/recommendations/{user_id}` et `/trending` sans passer par le frontend Laravel.

## Cache & performance

Deux endpoints sujets à des appels TMDB répétés sont mis en cache côté Laravel (`Cache::remember`, driver configuré via `CACHE_STORE`) :

| Endpoint | Portée du cache | Durée | Justification |
|---|---|---|---|
| `GET /api/trending` | Globale (tous les utilisateurs) | 60 min | Contenu identique pour tout le monde, ne varie pas vite |
| `GET /api/recommendations/{id}` | Par utilisateur + profil de goûts (clé incluant un hash des préférences) | 15 min | Personnalisé, mais doit rester frais après un nouveau like/dislike |

Les échecs (service IA ou TMDB indisponible) ne sont **jamais** mis en cache, pour ne pas priver un utilisateur de recommandations pendant toute la durée du TTL après une panne transitoire déjà résolue.

## Logs & observabilité

Les échecs de communication avec le service IA (FastAPI), Groq et TMDB sont écrits dans un canal dédié, séparé des logs applicatifs génériques Laravel :

```
storage/logs/cinebot-{date}.log
```

Configuré dans `config/logging.php` (driver `daily`, rotation sur 14 jours). Pour suivre ces logs en direct pendant une démo :

```bash
tail -f storage/logs/cinebot-$(date +%Y-%m-%d).log
```

## Tests automatisés

```bash
php artisan test
```

Suite de tests Feature (PHPUnit, isolée sur SQLite en mémoire — voir `phpunit.xml`, aucune donnée de la base MySQL de développement n'est touchée) :

| Fichier | Couvre |
|---|---|
| `tests/Feature/Auth/AuthenticationTest.php` | Inscription, connexion, déconnexion, protection des routes, mot de passe oublié, suppression de compte |
| `tests/Feature/MovieFeedbackTest.php` | Like / dislike / note, validation des entrées, upsert (pas de doublons), accès invité refusé, favoris |

Les appels réseau vers TMDB sont interceptés (`Http::fake()`) — la suite s'exécute entièrement hors-ligne et de façon déterministe.

## Sécurité & gestion de compte

Le projet applique plusieurs mesures de durcissement au-delà d'une authentification basique :

- **Récupération de mot de passe** — flux natif Laravel (`Password::sendResetLink` / `Password::reset`), token à usage unique expirant après 60 minutes. Le message renvoyé est volontairement générique ("si un compte existe...") pour ne jamais confirmer qu'une adresse email est enregistrée (protection contre l'énumération de comptes).
- **Rate limiting** :
  - `/api/chat` : 20 requêtes/minute par utilisateur (ou par IP pour les invités) — protège la facture Groq/TMDB et le service IA contre les abus ou boucles frontend.
  - `/forgot-password` : 5 requêtes/minute — anti-spam sur l'envoi d'emails.
- **Suppression de compte (droit à l'oubli)** — accessible depuis `/profile`, section "Zone dangereuse". Confirmation par mot de passe obligatoire avant suppression. La suppression est exécutée dans une transaction SQL et efface : l'historique de discussions, les préférences apprises, les interactions films (favoris/notes), puis le compte lui-même.
- **Secrets non versionnés** — `.env`, `ai-service/.env`, ainsi que l'index FAISS généré (`ai-service/faiss_movie_index/`) sont exclus du dépôt via `.gitignore`. Les clés Groq et TMDB ne transitent jamais côté navigateur : tous les appels externes passent par le backend.
- **CSRF** — toutes les routes `/api/*` partagent la session web (voir `bootstrap/app.php`) et valident le token CSRF, y compris pour les appels `fetch()` du frontend.

## Devenir administrateur

```bash
php artisan tinker
```
```php
User::where('email', 'votre@email.com')->update(['is_admin' => true]);
```

Avec Docker, exécute Tinker **à l'intérieur du conteneur** :
```bash
docker compose exec laravel php artisan tinker
```

## Feuille de route

- [ ] Pagination de l'historique des discussions
- [ ] File d'attente asynchrone pour la reconstruction de l'index FAISS
- [ ] Personnalisation du template d'email de réinitialisation (actuellement le template Laravel par défaut)
- [ ] Vérification d'email à l'inscription

## Contribuer

1. Créer une branche depuis `main` : `feature/nom-fonctionnalite` ou `fix/nom-bug`
2. Commits au format [Conventional Commits](https://www.conventionalcommits.org/) : `feat:`, `fix:`, `refactor:`, `docs:`
3. Vérifier que les migrations et le service Python démarrent sans erreur avant de pousser
4. Ouvrir une Pull Request vers `main` avec une description claire du changement et, si pertinent, une capture d'écran
5. Une revue est requise avant fusion

## Licence

Projet réalisé dans le cadre d'un stage d'été — 1ère année Cycle Ingénieur, TEK-UP University.
**Tous droits réservés** — voir le fichier [`LICENSE`](./LICENSE). Toute réutilisation du code sans autorisation écrite de l'auteure est interdite.

---

<p align="center">
  <strong>Ons Ajmi</strong> — étudiante en 1ère année Cycle Ingénieur, TEK-UP University<br>
  GitHub : <a href="https://github.com/AjmiOns">AjmiOns (Ons Ajmi)</a> · LinkedIn : <a href="https://www.linkedin.com/in/ons-ajmi-0ab2982a2/">Ons Ajmi</a>
</p>
# main.py
"""
CineBot AI — FastAPI backend with RAG
======================================

Startup sequence
----------------
1. Load .env (walks up from script dir to find it)
2. Load FAISS + BM25 index from disk; build it if missing
3. Serve endpoints

Endpoints
---------
POST /chat                  → RAG chat
GET  /recommendations/{uid} → sidebar movie cards
GET  /trending              → trending movies
GET  /                      → health check
POST /admin/rebuild-index   → rebuild RAG index (admin)
"""

from __future__ import annotations

import os
import sys
from pathlib import Path

# ── Load .env first (before any other import) ────────────────────────────────
from dotenv import load_dotenv

for _p in [Path(__file__).resolve().parent,
           Path(__file__).resolve().parent.parent,
           Path(__file__).resolve().parent.parent.parent]:
    _env = _p / ".env"
    if _env.exists():
        load_dotenv(dotenv_path=_env, override=True)
        print(f"[dotenv] loaded from {_env}", file=sys.stderr)
        break
else:
    print("[dotenv] WARNING: no .env file found", file=sys.stderr)

_groq_key  = os.getenv("GROQ_API_KEY", "")
_tmdb_key  = os.getenv("TMDB_API_KEY", "")
print(f"[startup] GROQ_API_KEY = {'SET ✅' if _groq_key else 'MISSING ❌'}", file=sys.stderr)
print(f"[startup] TMDB_API_KEY = {'SET ✅' if _tmdb_key else 'using default'}", file=sys.stderr)

# ── Standard imports ─────────────────────────────────────────────────────────
from typing import Optional, List
from fastapi import FastAPI, BackgroundTasks, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

from memory       import ConversationMemory
from groq_service import chat as groq_chat
from tmdb_service import search_movies, get_popular, get_trending
from ranking      import rank_movies
import rag_engine as rag

# ──────────────────────────────────────────────────────────────────────────────
# App setup
# ──────────────────────────────────────────────────────────────────────────────
app = FastAPI(
    title="CineBot AI — Service IA",
    version="2.0",
    description=(
        "Pipeline RAG hybride (FAISS + BM25 + RRF), ranking hybride "
        "(pertinence + note TMDB + popularité + préférences apprises) et "
        "génération de réponses via Groq (Llama 3.1). "
        "Consommé exclusivement par le backend Laravel — jamais exposé "
        "directement au navigateur en production."
    ),
    contact={"name": "Ons Ajmi", "url": "https://github.com/AjmiOns"},
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# In-process session stores
_memory_store:  dict[str, ConversationMemory] = {}


# ──────────────────────────────────────────────────────────────────────────────
# Startup: load / build RAG index
# ──────────────────────────────────────────────────────────────────────────────
@app.on_event("startup")
async def startup_event():
    """
    Try to load the FAISS index from disk.
    If not present, build it in the background so the server starts fast.
    """
    try:
        ok = rag.load_index()
        if not ok:
            print("[startup] Index not found — building in background…", file=sys.stderr)
            # Build synchronously on first start (could be async but simpler this way)
            rag.build_index(num_movies=500)
    except Exception as e:
        print(f"[startup] RAG init error: {e}", file=sys.stderr)


# ──────────────────────────────────────────────────────────────────────────────
# Request / Response models
# ──────────────────────────────────────────────────────────────────────────────
class ChatRequest(BaseModel):
    message:    str
    user_id:    Optional[str] = "guest"
    session_id: Optional[str] = "default"
    # Préférences apprises côté Laravel (UserPreference), envoyées à chaque
    # requête pour personnaliser le ranking ET l'explication de Groq.
    preferred_genres:    Optional[List[str]] = []
    preferred_actors:    Optional[List[str]] = []
    preferred_directors: Optional[List[str]] = []


class RebuildRequest(BaseModel):
    num_movies: Optional[int] = 500
    secret:     Optional[str] = ""        # optional admin secret


# ──────────────────────────────────────────────────────────────────────────────
# Helper
# ──────────────────────────────────────────────────────────────────────────────
def _get_memory(session_id: str) -> ConversationMemory:
    if session_id not in _memory_store:
        _memory_store[session_id] = ConversationMemory(max_turns=12)
    return _memory_store[session_id]


# ──────────────────────────────────────────────────────────────────────────────
# POST /chat  — main RAG endpoint
# ──────────────────────────────────────────────────────────────────────────────
@app.post("/chat")
def chat_endpoint(body: ChatRequest):
    query      = body.message.strip()
    session_id = body.session_id or "default"
    user_id    = body.user_id   or "guest"

    if not query:
        return {"reply": "Please type a message.", "recommended_movies": []}

    memory = _get_memory(session_id)

    # ── 1. RAG retrieval (FAISS + BM25, fused with RRF) ─────────────────────
    # On récupère un pool un peu plus large que nécessaire (8 au lieu de 5)
    # pour laisser au ranking hybride de la marge pour re-classer.
    rag_docs     = rag.retrieve(query, k=8)
    rag_context  = rag.format_context(rag_docs)
    movie_cards  = rag.docs_to_movie_cards(rag_docs)

    # ── 2. Real-time TMDB search (supplements RAG) ──────────────────────────
    try:
        tmdb_results = search_movies(query)[:8]
    except Exception:
        tmdb_results = []

    # Merge: RAG cards first, then any extra TMDB results not already included
    rag_ids = {c["id"] for c in movie_cards}
    for m in tmdb_results:
        if m.get("id") not in rag_ids:
            movie_cards.append(m)

    # ── 3. Ranking hybride ───────────────────────────────────────────────────
    # Combine : pertinence RAG + note TMDB + popularité + préférences apprises
    # (genres/acteurs/réalisateurs envoyés par Laravel depuis UserPreference).
    movie_cards = rank_movies(
        movie_cards,
        pref_genres    = body.preferred_genres,
        pref_actors    = body.preferred_actors,
        pref_directors = body.preferred_directors,
    )
    movie_cards = movie_cards[:10]

    # ── 4. Groq RAG chat (avec profil de goûts pour personnaliser "pourquoi") ──
    taste_parts = []
    if body.preferred_genres:
        taste_parts.append(f"Favorite genres: {', '.join(body.preferred_genres)}")
    if body.preferred_actors:
        taste_parts.append(f"Favorite actors: {', '.join(body.preferred_actors)}")
    if body.preferred_directors:
        taste_parts.append(f"Favorite directors: {', '.join(body.preferred_directors)}")
    user_taste = " | ".join(taste_parts)

    history = memory.get_messages()
    reply   = groq_chat(
        user_message = query,
        rag_context  = rag_context,
        history      = history,
        api_key      = os.getenv("GROQ_API_KEY", ""),
        user_taste   = user_taste,
    )

    # ── 5. Save turn to memory ───────────────────────────────────────────────
    memory.add("user",      query)
    memory.add("assistant", reply)

    return {
        "reply":              reply,
        "recommended_movies": movie_cards,
        "session_id":         session_id,
    }


# ──────────────────────────────────────────────────────────────────────────────
# GET /recommendations/{user_id}
# ──────────────────────────────────────────────────────────────────────────────
@app.get("/recommendations/{user_id}")
def recommendations(user_id: str, genres: str = "", actors: str = "", directors: str = ""):
    """
    Sidebar / "for you" recommendations.
    Si Laravel transmet les préférences apprises de l'utilisateur (query
    params `genres`, `actors`, `directors` en listes séparées par des
    virgules), le pool trending/popular est re-classé par le moteur hybride
    au lieu d'être renvoyé brut — c'est ce qui rend cet endpoint réellement
    personnalisé plutôt qu'identique pour tout le monde.
    """
    movies = get_trending() or get_popular()

    pref_genres    = [g for g in genres.split(",") if g]
    pref_actors    = [a for a in actors.split(",") if a]
    pref_directors = [d for d in directors.split(",") if d]

    if pref_genres or pref_actors or pref_directors:
        movies = rank_movies(movies, pref_genres, pref_actors, pref_directors)

    return {"movies": movies[:12]}


# ──────────────────────────────────────────────────────────────────────────────
# GET /trending
# ──────────────────────────────────────────────────────────────────────────────
@app.get("/trending")
def trending():
    return {"movies": get_trending()[:12]}


# ──────────────────────────────────────────────────────────────────────────────
# POST /admin/rebuild-index
# ──────────────────────────────────────────────────────────────────────────────
@app.post("/admin/rebuild-index")
def rebuild_index(body: RebuildRequest, background_tasks: BackgroundTasks):
    admin_secret = os.getenv("ADMIN_SECRET", "")
    if admin_secret and body.secret != admin_secret:
        raise HTTPException(status_code=403, detail="Invalid admin secret.")
    background_tasks.add_task(rag.build_index, body.num_movies)
    return {"status": f"Rebuilding index with {body.num_movies} movies in background."}


# ──────────────────────────────────────────────────────────────────────────────
# GET /  — health check
# ──────────────────────────────────────────────────────────────────────────────
@app.get("/")
def health():
    key = os.getenv("GROQ_API_KEY", "")
    return {
        "status":    "CineBot AI v2 running ✅",
        "groq":      "✅ set" if key else "❌ missing — add GROQ_API_KEY to .env",
        "rag_index": f"✅ {len(rag._documents)} movies" if rag._documents else "⚠️ building…",
    }
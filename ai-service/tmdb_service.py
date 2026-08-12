# tmdb_service.py
"""
TMDB API helpers used by the FastAPI endpoint.
These are for REAL-TIME queries (search bar, popular sidebar)
and are separate from the RAG index (which is pre-built offline).
"""
from __future__ import annotations

import os
import requests
from typing import List

API_KEY         = os.getenv("TMDB_API_KEY", "af253619184b16a30ab789dd6b116ee2")
BASE_URL        = "https://api.themoviedb.org/3"
IMAGE_BASE_W300 = "https://image.tmdb.org/t/p/w300"

# TMDB numeric genre IDs (correct values)
GENRE_IDS: dict[str, int] = {
    "action":    28,
    "comedy":    35,
    "horror":    27,
    "romance":   10749,
    "sci-fi":    878,
    "scifi":     878,
    "thriller":  53,
    "drama":     18,
    "animation": 16,
    "anime":     16,
    "adventure": 12,
    "fantasy":   14,
    "mystery":   9648,
    "crime":     80,
    "family":    10751,
    "history":   36,
    "war":       10752,
    "western":   37,
    "music":     10402,
    "biography": 99,  # documentary
    "documentary": 99,
}


def _fix_poster(movie: dict) -> dict:
    """Normalise poster_path to a full URL and add year/rating fields."""
    path = movie.get("poster_path") or ""
    movie["poster_path"] = (IMAGE_BASE_W300 + path) if path else ""
    movie["year"]        = str(movie.get("release_date", ""))[:4]
    movie["rating"]      = round(float(movie.get("vote_average") or 0), 1)
    return movie


def _get(path: str, **params) -> dict:
    params.setdefault("api_key", API_KEY)
    params.setdefault("language", "fr-FR")
    r = requests.get(f"{BASE_URL}{path}", params=params, timeout=10)
    r.raise_for_status()
    return r.json()


# ──────────────────────────────────────────────────────────────────────────────
# Public API
# ──────────────────────────────────────────────────────────────────────────────

def search_movies(query: str, page: int = 1) -> List[dict]:
    """
    Smart search:
    - If query matches a known genre keyword → /discover (genre filter)
    - Otherwise → /search/movie (text search)
    """
    q_lower = query.strip().lower()
    genre_id = GENRE_IDS.get(q_lower)

    try:
        if genre_id:
            data = _get(
                "/discover/movie",
                with_genres=genre_id,
                sort_by="popularity.desc",
                page=page,
            )
        else:
            data = _get("/search/movie", query=query, page=page)

        return [_fix_poster(m) for m in data.get("results", [])]
    except Exception as e:
        print(f"[TMDB] search_movies error: {e}")
        return []


def get_popular(page: int = 1) -> List[dict]:
    """Return currently popular movies."""
    try:
        data = _get("/movie/popular", page=page)
        return [_fix_poster(m) for m in data.get("results", [])]
    except Exception as e:
        print(f"[TMDB] get_popular error: {e}")
        return []


def get_trending(time_window: str = "week") -> List[dict]:
    """Return trending movies (day or week)."""
    try:
        data = _get(f"/trending/movie/{time_window}")
        return [_fix_poster(m) for m in data.get("results", [])]
    except Exception as e:
        print(f"[TMDB] get_trending error: {e}")
        return []
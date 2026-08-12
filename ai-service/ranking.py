# ranking.py
"""
Moteur de ranking hybride — CineBot AI
=======================================

Combine plusieurs signaux en un seul score composite, plutôt que de
laisser le LLM décider seul de l'ordre des recommandations :

  1. Pertinence RAG      → position dans les résultats FAISS + BM25 (RRF)
  2. Note TMDB           → vote_average (qualité perçue globale)
  3. Popularité          → vote_count / popularity TMDB (log-scaled)
  4. Préférences apprises → genres / acteurs / réalisateurs préférés de
                            l'utilisateur (calculés côté Laravel à partir
                            de ses like/dislike/note réels, puis transmis
                            à chaque requête /chat et /recommendations)

Pipeline complet :
  User Query → Intent Detection (FastAPI route) → FAISS + BM25 (rag_engine)
             → TMDB metadata (déjà dans les documents + recherche temps réel)
             → rank_movies() [CE FICHIER]
             → Groq explique les recommandations (groq_service, avec le
               profil utilisateur injecté dans le prompt)
"""
from __future__ import annotations

import math
from typing import List, Dict, Optional

from tmdb_service import GENRE_IDS

# TMDB envoie des genre_ids (int) sur les résultats de recherche temps réel,
# alors que les documents RAG stockent déjà des noms de genres en texte.
# On construit le mapping inverse pour pouvoir comparer les deux à plat.
GENRE_ID_TO_NAME: Dict[int, str] = {v: k for k, v in GENRE_IDS.items()}

# Poids du score composite — modifiables facilement pour ajuster l'équilibre
# entre "ce qui correspond à la requête" et "ce que l'utilisateur aime".
WEIGHT_RELEVANCE     = 0.35
WEIGHT_RATING        = 0.20
WEIGHT_POPULARITY    = 0.15
WEIGHT_PREFERENCE    = 0.30

# Bonus par correspondance de préférence (plafonnés pour ne jamais écraser
# complètement la pertinence de la requête elle-même)
BONUS_GENRE    = 0.15
BONUS_DIRECTOR = 0.20
BONUS_ACTOR    = 0.10
BONUS_CAP      = 0.6


# Score composite maximal théorique (relevance=1 + rating=1 + popularity=1,
# pondérés, + bonus préférence plafonné) — sert à convertir match_score en
# pourcentage lisible côté frontend (badge "97% match").
MAX_POSSIBLE_SCORE = (
    WEIGHT_RELEVANCE
    + WEIGHT_RATING
    + WEIGHT_POPULARITY
    + (WEIGHT_PREFERENCE * BONUS_CAP)
)


def _card_genres(card: dict) -> str:
    """Retourne les genres d'une carte film sous forme de texte en minuscules."""
    if card.get("genres"):
        return str(card["genres"]).lower()
    ids = card.get("genre_ids") or []
    names = [GENRE_ID_TO_NAME.get(i, "") for i in ids]
    return " ".join(n for n in names if n)


def score_movie(
    card: dict,
    rank_position: int,
    total_candidates: int,
    pref_genres: List[str],
    pref_actors: List[str],
    pref_directors: List[str],
) -> float:
    """
    Calcule le score composite d'un film candidat.
    Score approximatif dans [0, ~1.5] — plus haut = meilleur.
    """
    # 1) Pertinence de la requête (position dans le classement RAG/TMDB d'origine)
    relevance = 1.0 - (rank_position / max(total_candidates, 1))

    # 2) Qualité perçue (note TMDB, 0-10 → 0-1)
    rating = float(card.get("rating") or card.get("vote_average") or 0) / 10.0

    # 3) Popularité (nombre de votes ou score popularity TMDB, log-scalé pour
    #    éviter que les blockbusters n'écrasent tout le reste)
    raw_pop = float(card.get("vote_count") or card.get("popularity") or 0)
    popularity = min(1.0, math.log1p(raw_pop) / math.log1p(20000))

    # 4) Correspondance avec le profil appris de l'utilisateur
    genres   = _card_genres(card)
    director = str(card.get("director") or "").lower()
    actors   = str(card.get("actors") or "").lower()

    pref_bonus = 0.0
    for g in pref_genres:
        if g and g.lower() in genres:
            pref_bonus += BONUS_GENRE
    if director and any(d.lower() == director for d in pref_directors if d):
        pref_bonus += BONUS_DIRECTOR
    for a in pref_actors:
        if a and a.lower() in actors:
            pref_bonus += BONUS_ACTOR
    pref_bonus = min(pref_bonus, BONUS_CAP)

    return (
        WEIGHT_RELEVANCE  * relevance
        + WEIGHT_RATING     * rating
        + WEIGHT_POPULARITY * popularity
        + WEIGHT_PREFERENCE * pref_bonus
    )


def rank_movies(
    cards: List[dict],
    pref_genres: Optional[List[str]] = None,
    pref_actors: Optional[List[str]] = None,
    pref_directors: Optional[List[str]] = None,
) -> List[dict]:
    """
    Ré-ordonne une liste de cartes films selon le score hybride.
    Ajoute un champ `match_score` (0-1.5) à chaque carte pour transparence —
    le frontend peut l'ignorer ou l'afficher (ex: badge "97% match").
    """
    pref_genres    = pref_genres or []
    pref_actors    = pref_actors or []
    pref_directors = pref_directors or []

    total = len(cards)
    if total == 0:
        return []

    scored = [
        (card, score_movie(card, i, total, pref_genres, pref_actors, pref_directors))
        for i, card in enumerate(cards)
    ]
    scored.sort(key=lambda x: x[1], reverse=True)

    ranked = []
    for card, s in scored:
        card = dict(card)
        card["match_score"]   = round(s, 3)
        card["match_percent"] = round(min(1.0, s / MAX_POSSIBLE_SCORE) * 100)
        ranked.append(card)
    return ranked

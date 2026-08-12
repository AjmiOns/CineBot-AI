# rag_engine.py
"""
CineBot RAG Engine
==================
Handles document ingestion, vector storage (FAISS), and hybrid retrieval
(dense semantic + BM25 sparse) for grounded movie Q&A.

"""

from __future__ import annotations

import json
import os
import pickle
import re
from pathlib import Path
from typing import List, Tuple

import numpy as np
import requests
from langchain_core.documents import Document

# ─────────────────────────────────────────────────────────────
# CONFIGURATION
# ─────────────────────────────────────────────────────────────
TMDB_API_KEY        = os.getenv("TMDB_API_KEY", "af253619184b16a30ab789dd6b116ee2")
TMDB_BASE           = "https://api.themoviedb.org/3"
TMDB_IMAGE_BASE     = "https://image.tmdb.org/t/p/w300"

EMBEDDING_MODEL     = "sentence-transformers/all-MiniLM-L6-v2"
FAISS_DIR           = Path("./faiss_movie_index")
DOCS_CACHE          = FAISS_DIR / "docs_cache.pkl"
BM25_CACHE          = FAISS_DIR / "bm25.pkl"

TOP_K_DENSE         = 8
TOP_K_SPARSE        = 8
TOP_K_FINAL         = 5

# ── Adult content filter ──────────────────────────────────────────────────────
_ADULT_RE = re.compile(
    r"\b(porn|pornograph|xxx|erotic|erotica|sex film|sex movie|adult film|"
    r"adult content|nude|nudist|nudism|hentai|softcore|hardcore|playboy|"
    r"penthouse|strip(per|tease)|camgirl|onlyfans|nsfw)\b",
    re.IGNORECASE,
)


def _is_adult_movie(movie: dict) -> bool:
    """Return True if this TMDB movie dict should be excluded."""
    if movie.get("adult", False):
        return True
    text = f"{movie.get('title', '')} {movie.get('overview', '')}"
    return bool(_ADULT_RE.search(text))


def _is_adult_doc(doc: Document) -> bool:
    """Return True if this LangChain Document should be excluded."""
    return bool(_ADULT_RE.search(doc.page_content))


# ──────────────────────────────────────────────────────────────────────────────
# Lazy imports (heavy ML libs – only loaded when actually used)
# ──────────────────────────────────────────────────────────────────────────────
_embeddings  = None
_vectorstore = None
_bm25        = None
_documents: List[Document] = []

# Charge le modèle d'embeddings uniquement si nécessaire.
def _get_embeddings():
    global _embeddings
    if _embeddings is None:
        from langchain_huggingface import HuggingFaceEmbeddings
        _embeddings = HuggingFaceEmbeddings(model_name=EMBEDDING_MODEL)
    return _embeddings


# ──────────────────────────────────────────────────────────────────────────────
# TMDB helpers
# ──────────────────────────────────────────────────────────────────────────────
def _tmdb_get(path: str, **params) -> dict:
    #  Requête sécurisée vers TMDB API.
    params["api_key"]        = TMDB_API_KEY
    params["language"]       = "en-US"
    params["include_adult"]  = "false"   # TMDB-level filter
    r = requests.get(f"{TMDB_BASE}{path}", params=params, timeout=10)
    r.raise_for_status()
    return r.json()

# Récupère les films populaires et top rated depuis TMDB.
def fetch_movies(pages: int = 15) -> List[dict]:
    """Fetch popular + top-rated movies from TMDB, adult content excluded."""
    movies: dict[int, dict] = {}

    for page in range(1, pages + 1):
        for endpoint in ("/movie/popular", "/movie/top_rated"):
            try:
                data = _tmdb_get(endpoint, page=page)
                for m in data.get("results", []):
                    if not _is_adult_movie(m):      # ← filter here
                        movies[m["id"]] = m
            except Exception as e:
                print(f"[RAG] TMDB fetch error ({endpoint} p{page}): {e}")

    return list(movies.values())

#  Ajoute cast + crew + keywords à un film.
def enrich_movie(movie: dict) -> dict:
    """Add cast, crew, keywords to a movie dict."""
    try:
        details = _tmdb_get(
            f"/movie/{movie['id']}",
            append_to_response="credits,keywords"
        )
        movie.update(details)
    except Exception as e:
        print(f"[RAG] Could not enrich {movie.get('title')}: {e}")
    return movie

# ─────────────────────────────────────────────────────────────
#  CONVERSION DOCUMENT RAG
# ─────────────────────────────────────────────────────────────
def movie_to_document(movie: dict) -> Document:
    # Convertit un film TMDB en document exploitable par RAG.
    cast   = movie.get("credits", {}).get("cast", [])[:6]
    crew   = movie.get("credits", {}).get("crew", [])
    genres = [g["name"] for g in movie.get("genres", [])]
    kws    = [k["name"] for k in movie.get("keywords", {}).get("keywords", [])[:12]]

    director = next((p["name"] for p in crew if p.get("job") == "Director"), "Unknown")
    actors   = ", ".join(p["name"] for p in cast)

    content = (
        f"Title: {movie.get('title', '')}\n"
        f"Year: {str(movie.get('release_date', ''))[:4]}\n"
        f"Genres: {', '.join(genres)}\n"
        f"Director: {director}\n"
        f"Stars: {actors}\n"
        f"Rating: {movie.get('vote_average', 0):.1f}/10  "
        f"({movie.get('vote_count', 0)} votes)\n"
        f"Overview: {movie.get('overview', '')}\n"
        f"Keywords: {', '.join(kws)}\n"
        f"Budget: ${movie.get('budget', 0):,}  "
        f"Runtime: {movie.get('runtime', 0)} min"
    )

    poster = movie.get("poster_path", "")
    return Document(
        page_content=content,
        metadata={
            "movie_id":    movie.get("id"),
            "title":       movie.get("title", ""),
            "year":        str(movie.get("release_date", ""))[:4],
            "poster_path": (TMDB_IMAGE_BASE + poster) if poster else "",
            "genres":      ", ".join(genres),
            "rating":      float(movie.get("vote_average", 0)),
            "vote_count":  int(movie.get("vote_count", 0)),
            "director":    director,
            "actors":      actors,
        }
    )


# ─────────────────────────────────────────────────────────────
# BUILD INDEX (FAISS + BM25)
# ─────────────────────────────────────────────────────────────

# Construit la base RAG complète :FAISS (embeddings) BM25 (keyword search)
def build_index(num_movies: int = 500) -> None:
    """Fetch movies from TMDB, build FAISS + BM25 indexes, persist to disk."""
    from langchain_community.vectorstores import FAISS
    from rank_bm25 import BM25Okapi

    global _vectorstore, _bm25, _documents

    FAISS_DIR.mkdir(exist_ok=True)

    print(f"[RAG] Fetching up to {num_movies} movies from TMDB…")
    raw = fetch_movies(pages=max(1, num_movies // 20))[:num_movies]

    print(f"[RAG] Enriching {len(raw)} movies with cast/keywords…")
    documents: List[Document] = []
    skipped = 0
    for i, m in enumerate(raw):
        enriched = enrich_movie(m)

        # Skip adult content after enrichment (keywords may reveal it)
        if _is_adult_movie(enriched):
            skipped += 1
            continue

        doc = movie_to_document(enriched)

        # Second pass: check the document text itself
        if _is_adult_doc(doc):
            skipped += 1
            continue

        documents.append(doc)
        if (i + 1) % 50 == 0:
            print(f"[RAG]   {i + 1}/{len(raw)} done  (skipped {skipped} adult)")

    print(f"[RAG] Adult content filtered: {skipped} movies removed.")
    print("[RAG] Building FAISS vector store…")
    embeddings  = _get_embeddings()
    vectorstore = FAISS.from_documents(documents, embeddings)
    vectorstore.save_local(str(FAISS_DIR))

    print("[RAG] Building BM25 sparse index…")
    tokenized = [doc.page_content.lower().split() for doc in documents]
    bm25      = BM25Okapi(tokenized)

    with open(DOCS_CACHE, "wb") as f:
        pickle.dump(documents, f)
    with open(BM25_CACHE, "wb") as f:
        pickle.dump(bm25, f)

    _vectorstore = vectorstore
    _bm25        = bm25
    _documents   = documents
    print(f"[RAG] ✅ Index built: {len(documents)} safe movies.")


def load_index() -> bool:
    """Load FAISS + BM25 from disk. Returns True if successful."""
    from langchain_community.vectorstores import FAISS

    global _vectorstore, _bm25, _documents

    if not (FAISS_DIR / "index.faiss").exists():
        return False

    try:
        embeddings   = _get_embeddings()
        _vectorstore = FAISS.load_local(
            str(FAISS_DIR),
            embeddings,
            allow_dangerous_deserialization=True
        )
        with open(DOCS_CACHE, "rb") as f:
            _documents = pickle.load(f)
        with open(BM25_CACHE, "rb") as f:
            _bm25 = pickle.load(f)
        print(f"[RAG] ✅ Index loaded: {len(_documents)} movies.")
        return True
    except Exception as e:
        print(f"[RAG] Load error: {e}")
        return False


# ─────────────────────────────────────────────────────────────
# RETRIEVAL HYBRIDE
# ─────────────────────────────────────────────────────────────
def _reciprocal_rank_fusion(
        #Fusion des résultats FAISS + BM25.
    dense:  List[Tuple[Document, float]],
    sparse: List[Tuple[Document, float]],
    k: int = 60,
) -> List[Document]:
    """Merge dense + sparse results with Reciprocal Rank Fusion."""
    scores: dict[str, float] = {}
    doc_map: dict[str, Document] = {}

    for rank, (doc, _) in enumerate(dense):
        key = doc.page_content[:80]
        scores[key]  = scores.get(key, 0.0) + 1.0 / (k + rank + 1)
        doc_map[key] = doc

    for rank, (doc, _) in enumerate(sparse):
        key = doc.page_content[:80]
        scores[key]  = scores.get(key, 0.0) + 1.0 / (k + rank + 1)
        doc_map[key] = doc

    ranked = sorted(scores.items(), key=lambda x: x[1], reverse=True)
    return [doc_map[key] for key, _ in ranked if key in doc_map]


def retrieve(query: str, k: int = TOP_K_FINAL) -> List[Document]:
    """
    Hybrid retrieval: FAISS semantic + BM25 keyword, fused with RRF.
    Adult documents are stripped from results even if they slip through.
    """
    if _vectorstore is None or _bm25 is None:
        return []

    # Dense (semantic) retrieval
    dense_results = _vectorstore.similarity_search_with_score(query, k=TOP_K_DENSE)

    # Sparse (BM25) retrieval
    tokens      = query.lower().split()
    bm25_scores = _bm25.get_scores(tokens)
    top_idx     = np.argsort(bm25_scores)[::-1][:TOP_K_SPARSE]
    sparse_results = [(_documents[i], float(bm25_scores[i])) for i in top_idx]

    fused = _reciprocal_rank_fusion(dense_results, sparse_results)

    # Final safety pass — strip any adult doc that somehow made it through
    safe = [doc for doc in fused if not _is_adult_doc(doc)]

    return safe[:k]

# ─────────────────────────────────────────────────────────────
# FORMAT OUTPUT
# ─────────────────────────────────────────────────────────────

def format_context(docs: List[Document]) -> str:
    """Format retrieved documents into a prompt-ready context block."""
    if not docs:
        return "No specific movie data retrieved."
    parts = []
    for i, doc in enumerate(docs, 1):
        title = doc.metadata.get("title", "?")
        year  = doc.metadata.get("year", "?")
        parts.append(f"[Movie {i}: {title} ({year})]\n{doc.page_content}")
    return "\n\n---\n\n".join(parts)

#Convertit les documents RAG en cartes UI type Netflix.
def docs_to_movie_cards(docs: List[Document]) -> List[dict]:
    """Convert retrieved docs into TMDB-style movie card dicts for the UI."""
    cards = []
    seen  = set()
    for doc in docs:
        mid = doc.metadata.get("movie_id")
        if mid in seen:
            continue
        seen.add(mid)
        cards.append({
            "id":           mid,
            "title":        doc.metadata.get("title", ""),
            "year":         doc.metadata.get("year", ""),
            "poster_path":  doc.metadata.get("poster_path", ""),
            "rating":       doc.metadata.get("rating", 0),
            "vote_average": doc.metadata.get("rating", 0),
            "vote_count":   doc.metadata.get("vote_count", 0),
            "release_date": f"{doc.metadata.get('year', '')}-01-01",
            "genres":       doc.metadata.get("genres", ""),
            "director":     doc.metadata.get("director", ""),
            "actors":       doc.metadata.get("actors", ""),
        })
    return cards
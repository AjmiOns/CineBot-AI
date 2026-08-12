# movie_data.py
import requests
import os
from langchain_core.documents import Document


# ─────────────────────────────────────────────────────────────
# TMDB API CONFIGURATION
# ─────────────────────────────────────────────────────────────
TMDB_BASE = "https://api.themoviedb.org/3"
TMDB_KEY = os.getenv("TMDB_API_KEY", "af253619184b16a30ab789dd6b116ee2")
TMDB_IMAGE_BASE = "https://image.tmdb.org/t/p/w500"

# ─────────────────────────────────────────────────────────────
# 1. RÉCUPÉRATION DES FILMS POPULAIRES
# ─────────────────────────────────────────────────────────────
#Récupère les films populaires depuis TMDB API.
def fetch_popular_movies(pages=10) -> list[dict]:
    movies = []
    for page in range(1, pages + 1):
        r = requests.get(
            f"{TMDB_BASE}/movie/popular",
            params={"api_key": TMDB_KEY, "page": page}
        )

         # Si la requête réussit → ajouter les films
        if r.status_code == 200:
            movies.extend(r.json().get("results", []))
    return movies



# ─────────────────────────────────────────────────────────────
# 2. DÉTAILS COMPLETS D'UN FILM
# ─────────────────────────────────────────────────────────────
def fetch_movie_details(movie_id: int) -> dict:
    #Récupère les détails complets d'un film
    detail = requests.get(
        f"{TMDB_BASE}/movie/{movie_id}",
        params={"api_key": TMDB_KEY, "append_to_response": "credits,keywords"}
    )
      # Lève une erreur si la requête échoue
    detail.raise_for_status()
    return detail.json()

# ─────────────────────────────────────────────────────────────
# 3. CONVERSION FILM → DOCUMENT RAG
# ─────────────────────────────────────────────────────────────
def movie_to_document(movie: dict) -> Document:
     # Acteurs principaux (top 5)
    cast = movie.get("credits", {}).get("cast", [])[:5]
     # Crew technique
    crew = movie.get("credits", {}).get("crew", [])
    # Extraction du réalisateur
    director = next((p["name"] for p in crew if p["job"] == "Director"), "Unknown")
    actors = ", ".join([p["name"] for p in cast])

    genres = ", ".join([g["name"] for g in movie.get("genres", [])])
    keywords = ", ".join([
        k["name"] for k in movie.get("keywords", {}).get("keywords", [])[:10]
    ])

 # Texte final utilisé pour embeddings (RAG)
    content = f"""
Title: {movie.get('title')}
Year: {movie.get('release_date', '')[:4]}
Genres: {genres}
Director: {director}
Stars: {actors}
Rating: {movie.get('vote_average')}/10
Overview: {movie.get('overview', '')}
Keywords: {keywords}
""".strip()


 # Poster du film
    poster = movie.get("poster_path", "")
    poster_url = (TMDB_IMAGE_BASE + poster) if poster else ""


# Document LangChain
    return Document(
        page_content=content,
        metadata={
            "movie_id": movie.get("id"),
            "title": movie.get("title"),
            "year": movie.get("release_date", "")[:4],
            "poster_path": poster_url,
            "genres": genres,
            "rating": movie.get("vote_average"),
            "director": director,
            "actors": actors,
        }
    )

# ─────────────────────────────────────────────────────────────
#  4. CONSTRUCTION VECTOR STORE (RAG DATABASE)
# ─────────────────────────────────────────────────────────────
def build_movie_vectorstore(num_movies=500):
    from langchain_community.vectorstores import Chroma
    from langchain_huggingface import HuggingFaceEmbeddings

# Modèle d'embeddings (MiniLM léger et rapide)
    embedding_model = HuggingFaceEmbeddings(
        model_name="sentence-transformers/all-MiniLM-L6-v2"
    )

    print("Fetching movies from TMDB...")
    movies = fetch_popular_movies(pages=max(1, num_movies // 20))

    documents = []
     # Conversion chaque film → Document RAG
    for movie in movies[:num_movies]:
        try:
            details = fetch_movie_details(movie["id"])
            doc = movie_to_document(details)
            documents.append(doc)
        except Exception as e:
            print(f"Skipping movie {movie.get('title')}: {e}")

    print(f"Building vector store with {len(documents)} movies...")
     # Création de la base vectorielle ChromaDB
    vectorstore = Chroma.from_documents(
        documents=documents,
        embedding=embedding_model,
        persist_directory="./movie_chroma_db"
    )
    # Sauvegarde sur disque
    vectorstore.persist()
    print("Done! Vector store saved to ./movie_chroma_db")
    return vectorstore, documents
# recommender.py

# ─────────────────────────────────────────────────────────────
# Movie Recommender (Hybrid + Personalized)
# ─────────────────────────────────────────────────────────────

class MovieRecommender:
    def __init__(self, hybrid_retriever, preference_engine):
        # Retriever hybride (RAG : dense + sparse)
        self.retriever = hybrid_retriever
        # Moteur de préférences utilisateur
        self.prefs = preference_engine
    
    # ─────────────────────────────────────────────────────────────
    # RECOMMANDATION PRINCIPALE
    # ─────────────────────────────────────────────────────────────
    def recommend(self, query: str, user_id: str, n=5) -> list:
        # Add preference bias to query
        bias = self.prefs.get_recommendation_bias(user_id)
        # On enrichit la requête avec les préférences utilisateur
        enhanced_query = f"{query} {bias}".strip()
        
        # Retrieval hybride (RAG)
        candidates = self.retriever.retrieve(enhanced_query)
        
        # Scoring des candidats
        profile = self.prefs.get_profile(user_id)
        scored = []
        
         # On limite à 20 pour performance
        for doc in candidates[:20]:
            score = self._score_movie(doc, profile)
            scored.append((doc, score))
        
        # Tri par score décroissant
        scored.sort(key=lambda x: x[1], reverse=True)
         # Retour des N meilleurs films
        return [doc for doc, _ in scored[:n]]
    

    # ─────────────────────────────────────────────────────────────
    # FONCTION DE SCORING
    # ─────────────────────────────────────────────────────────────
    def _score_movie(self, doc, profile) -> float:
        # Calcule un score de pertinence pour un film.
        score = 0.0
        content = doc.page_content.lower()
        meta = doc.metadata
        
        # Bonus genres
        for genre, count in profile["genre_counts"].items():
            if genre.lower() in content:
                score += count * 0.5
        
        # Bonus acteurs
        for actor, count in profile["actor_mentions"].items():
            if actor.lower() in content:
                score += count * 0.3
        
        # Bonus rating TMDB
        rating = float(meta.get("rating", 5))
        score += rating / 10 # normalisation 0–10 → 0–1
        
        # Pénalité films rejetés
        title = meta.get("title", "").lower()
        if title in [m.lower() for m in profile["disliked_movies"]]:
            score -= 5.0
        
        return score
    
    # ─────────────────────────────────────────────────────────────
    # RECHERCHE DE FILMS SIMILAIRES
    # ─────────────────────────────────────────────────────────────

    #Trouve des films similaires à un titre donné.
    #Utilise simplement le retriever RAG avec une requête enrichie.
    
    def get_similar_movies(self, movie_title: str, n=5) -> list:
        """Find movies similar to a given title."""
        return self.retriever.retrieve(f"movies similar to {movie_title}", )[:n]
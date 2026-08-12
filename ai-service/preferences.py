# preferences.py
from collections import Counter
import json

# ─────────────────────────────────────────────────────────────
# Mapping mots-clés → genres
# ─────────────────────────────────────────────────────────────
# Permet de détecter automatiquement le genre préféré
# à partir du texte de l'utilisateur

GENRE_KEYWORDS = {
    "action": ["action", "fight", "explosion", "thriller", "chase"],
    "romance": ["love", "romance", "romantic", "relationship"],
    "horror": ["horror", "scary", "fear", "monster", "ghost", "terror"],
    "sci-fi": ["sci-fi", "science fiction", "space", "future", "robot", "alien"],
    "anime": ["anime", "animated", "animation", "japanese"],
    "comedy": ["comedy", "funny", "humor", "laugh"],
    "drama": ["drama", "emotional", "sad", "moving", "touching"],
    "documentary": ["documentary", "real", "true story", "based on"],
}

# ─────────────────────────────────────────────────────────────
# Mapping mots-clés → humeur utilisateur
# ─────────────────────────────────────────────────────────────
MOOD_MAP = {
    "happy": ["feel good", "funny", "comedy", "uplifting", "cheerful"],
    "sad": ["emotional", "cry", "touching", "sad", "melancholy"],
    "excited": ["action", "thriller", "adventure", "intense"],
    "relaxed": ["slow", "calm", "peaceful", "cozy"],
}

#Système de profil utilisateur basé sur les interactions textuelles.
class UserPreferenceEngine:
    def __init__(self):
        # En production : remplacé par base de données (MySQL)
        self.profiles = {}
    
    # ─────────────────────────────────────────────────────────────
    #  RÉCUPÉRATION DU PROFIL UTILISATEUR
    # ─────────────────────────────────────────────────────────────
    def get_profile(self, user_id: str) -> dict:
        if user_id not in self.profiles:
            self.profiles[user_id] = {
                "genre_counts": Counter(),
                "actor_mentions": Counter(),
                "director_mentions": Counter(),
                "mood_counts": Counter(),
                "liked_movies": [],
                "disliked_movies": [],
                "recent_queries": [],
            }
        return self.profiles[user_id]
    

    # ─────────────────────────────────────────────────────────────
    # ANALYSE DU MESSAGE UTILISATEUR
    # ─────────────────────────────────────────────────────────────
    def analyze_message(self, user_id: str, message: str):
        """Extract preferences from a user message."""
        profile = self.get_profile(user_id)
        msg_lower = message.lower()
        
        # Detect genres
        for genre, keywords in GENRE_KEYWORDS.items():
            if any(kw in msg_lower for kw in keywords):
                profile["genre_counts"][genre] += 1
        
        # Detect mood
        for mood, keywords in MOOD_MAP.items():
            if any(kw in msg_lower for kw in keywords):
                profile["mood_counts"][mood] += 1
        
        # Store recent query
        profile["recent_queries"].append(message)
          # Limite mémoire (20 derniers messages)
        if len(profile["recent_queries"]) > 20:
            profile["recent_queries"].pop(0)
    

    # ─────────────────────────────────────────────────────────────
    #  RÉSUMÉ DU PROFIL UTILISATEUR
    # ─────────────────────────────────────────────────────────────
    def get_profile_summary(self, user_id: str) -> str:
        if not user_id or user_id not in self.profiles:
            return "New user — no preferences yet."
        
        profile = self.profiles[user_id]
         # Top genres (les plus mentionnés)
        top_genres = profile["genre_counts"].most_common(3)
        # Mood dominant
        top_mood = profile["mood_counts"].most_common(1)
        
        summary_parts = []
        if top_genres:
            genres_str = ", ".join([f"{g} ({c} mentions)" for g, c in top_genres])
            summary_parts.append(f"Favorite genres: {genres_str}")
        if top_mood:
            summary_parts.append(f"Current mood preference: {top_mood[0][0]}")
        if profile["liked_movies"]:
            summary_parts.append(f"Liked: {', '.join(profile['liked_movies'][-3:])}")
        
        return " | ".join(summary_parts) if summary_parts else "Building preference profile..."
    

    # ─────────────────────────────────────────────────────────────
    # 🔍 BIAIS POUR RÉCUPÉRATION RAG
    # ─────────────────────────────────────────────────────────────
    def get_recommendation_bias(self, user_id: str) -> str:
        """Return a query bias string for retrieval."""
        profile = self.get_profile(user_id)
        top_genres = [g for g, _ in profile["genre_counts"].most_common(2)]
        return " ".join(top_genres) if top_genres else ""

# Instance globale utilisée par l'application
preference_engine = UserPreferenceEngine()
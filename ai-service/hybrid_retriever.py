# hybrid_retriever.py
from rank_bm25 import BM25Okapi
from langchain_community.vectorstores import Chroma
from langchain_huggingface import HuggingFaceEmbeddings
import numpy as np

class HybridRetriever:
    
    def __init__(self, vectorstore, documents, top_k=5):
        self.vectorstore = vectorstore # base vectorielle
        self.top_k = top_k # nombre de résultats à récupérer
        
        # On transforme chaque document en liste de tokens (mots)
        # Exemple: "Movie about space travel" → ["movie", "about", "space", "travel"]
        # Build BM25 index from your existing documents
        tokenized = [doc.page_content.lower().split() for doc in documents]
         # Création de l'index BM25 (ranking basé sur fréquence des mots)
        self.bm25 = BM25Okapi(tokenized)
         # Stockage des documents originaux
        self.documents = documents

    def retrieve(self, query: str) -> list:
        # Effectue une recherche hybride :
        # 1. Dense retrieval (your existing ChromaDB search)
          # Utilise les embeddings (ChromaDB)
        # → comprend le sens de la phrase (ex: "space movie" ≈ "Interstellar")
        dense_results = self.vectorstore.similarity_search_with_score(query, k=self.top_k)
        
        # 2. Sparse retrieval (BM25 keyword search)
        # Recherche basée sur les mots exacts
        tokenized_query = query.lower().split()
         # Score BM25 pour chaque document
        bm25_scores = self.bm25.get_scores(tokenized_query)
        # On récupère les meilleurs indices selon BM25
        top_bm25_indices = np.argsort(bm25_scores)[::-1][:self.top_k]
         # Construction des résultats BM25
        sparse_results = [(self.documents[i], bm25_scores[i]) 
                          for i in top_bm25_indices]
        
        # 3. Reciprocal Rank Fusion
        return self._reciprocal_rank_fusion(dense_results, sparse_results)

    def _reciprocal_rank_fusion(self, dense, sparse, k=60):
        scores = {}
        
        #  Contribution des résultats DENSE (embeddings)
        for rank, (doc, _) in enumerate(dense):
            key = doc.page_content[:100]  # use content as key
            scores[key] = scores.get(key, 0) + 1 / (k + rank + 1)
        
        #  Contribution des résultats SPARSE (BM25) 
        for rank, (doc, _) in enumerate(sparse):
             # clé = début du document (approximation d'identité)
            key = doc.page_content[:100]
            scores[key] = scores.get(key, 0) + 1 / (k + rank + 1)
        
        #  Reconstruction des documents finaux
        # mapping key → document original
        # Return documents sorted by combined score
        all_docs = {doc.page_content[:100]: doc 
                    for doc, _ in dense + sparse}
         # tri des documents par score final (du meilleur au pire)
        ranked = sorted(scores.items(), key=lambda x: x[1], reverse=True)
          # on retourne les documents dans l’ordre final
        return [all_docs[key] for key in [r[0] for r in ranked] if key in all_docs]
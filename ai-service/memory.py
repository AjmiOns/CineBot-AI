# memory.py
"""
Conversation memory with sliding window.
Stored per session_id so multiple users/sessions are independent.
"""
from __future__ import annotations
from collections import deque
from typing import List


class ConversationMemory:
    def __init__(self, max_turns: int = 12):
        # max_turns * 2 slots = user + assistant per turn
        self._history: deque[dict] = deque(maxlen=max_turns * 2)

    # ── write :  Ajoute un message à la mémoire.
    def add(self, role: str, content: str) -> None:
        self._history.append({"role": role, "content": content})

     # RÉCUPÉRER L'HISTORIQUE (FORMAT LLM)
    def get_messages(self) -> List[dict]:
        """Return history in OpenAI-compatible message format."""
        return list(self._history)

   # 🧠 CONTEXTE TEXTE (pour RAG / prompt engineering)
    def get_context_string(self) -> str:
        """
        Return a plain-text conversation summary for injection into
        the RAG system prompt (keeps references like 'that movie',
        'the second one', 'same director' resolvable).
        """
        if not self._history:
            return ""
        lines = []
        for msg in self._history:
            prefix = "User" if msg["role"] == "user" else "Assistant"
            lines.append(f"{prefix}: {msg['content']}")
        return "\n".join(lines)
    

# 🎬 DERNIER MESSAGE ASSISTANT
    def last_assistant_message(self) -> str:
        for msg in reversed(self._history):
            if msg["role"] == "assistant":
                return msg["content"]
        return ""
 #  RESET MÉMOIRE
    def clear(self) -> None:
         #Supprime toute la mémoire de la session.
        self._history.clear()

    def __len__(self) -> int:
        return len(self._history)
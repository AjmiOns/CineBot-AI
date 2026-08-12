# check_env.py
# Run this once to diagnose your environment:
#   python check_env.py
import os
import sys
from pathlib import Path

print("=== CineBot Environment Check ===\n")

# 1. Where are we?
cwd = Path.cwd()
print(f"Working directory : {cwd}")
print(f"This script lives : {Path(__file__).resolve().parent}\n")

# 2. Search for .env files
found = []
for p in [Path.cwd(), Path(__file__).resolve().parent,
          Path(__file__).resolve().parent.parent]:
    env = p / ".env"
    if env.exists():
        found.append(env)

if found:
    for f in found:
        print(f"✅ .env found at: {f}")
        with open(f) as fh:
            for line in fh:
                line = line.strip()
                if line.startswith("GROQ_API_KEY"):
                    val = line.split("=", 1)[1].strip() if "=" in line else ""
                    masked = val[:8] + "..." if len(val) > 8 else "(empty)"
                    print(f"   GROQ_API_KEY = {masked}")
                if line.startswith("TMDB_API_KEY"):
                    val = line.split("=", 1)[1].strip() if "=" in line else ""
                    masked = val[:8] + "..." if len(val) > 8 else "(empty)"
                    print(f"   TMDB_API_KEY = {masked}")
else:
    print("❌ No .env file found in any expected location.")
    print("   Create one next to main.py with:")
    print("   GROQ_API_KEY=gsk_your_key_here")
    print("   TMDB_API_KEY=your_tmdb_key_here")

# 3. Check python-dotenv
print()
try:
    from dotenv import load_dotenv
    if found:
        load_dotenv(dotenv_path=found[0], override=True)
    key = os.getenv("GROQ_API_KEY", "")
    print(f"python-dotenv    : ✅ installed")
    print(f"GROQ_API_KEY     : {'✅ loaded (' + key[:8] + '...)' if key else '❌ still empty after load_dotenv'}")
except ImportError:
    print("python-dotenv    : ❌ NOT installed")
    print("   Fix: pip install python-dotenv")

print("\n=== Done ===")
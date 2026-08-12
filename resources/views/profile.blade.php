<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>My Profile — CineBot AI</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root,
[data-theme="dark"] {
  --bg:           #080a10;
  --surface:      #0e1018;
  --surface-2:    #13151f;
  --surface-3:    #1a1d2a;
  --border:       #1f2235;
  --border-glow:  #2a2e48;
  --text:         #e8eaf2;
  --muted:        #5a5f7a;
  --accent:       #e50914;
  --accent-dim:   rgba(229,9,20,.12);
  --accent-glow:  rgba(229,9,20,.25);
  --gold:         #f5b800;
  --gold-dim:     rgba(245,184,0,.15);
  --radius:       14px;
  --radius-sm:    8px;
  --ease:         cubic-bezier(.4,0,.2,1);
  --shadow-card:  0 8px 32px rgba(0,0,0,.6);
}
[data-theme="light"] {
  --bg:           #f5f5f7;
  --surface:      #ffffff;
  --surface-2:    #f0f0f5;
  --surface-3:    #e5e5ec;
  --border:       #d8d8e4;
  --border-glow:  #c0c0d0;
  --text:         #111118;
  --muted:        #7a7a90;
  --accent:       #e50914;
  --accent-dim:   rgba(229,9,20,.08);
  --accent-glow:  rgba(229,9,20,.18);
  --gold:         #c98a00;
  --gold-dim:     rgba(201,138,0,.12);
  --shadow-card:  0 4px 20px rgba(0,0,0,.12);
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  transition:background .3s var(--ease), color .3s var(--ease);
}
[data-theme="dark"] body{
  background-image:
    radial-gradient(ellipse 80% 60% at 50% -10%, rgba(229,9,20,.08) 0%, transparent 60%),
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='.025'/%3E%3C/svg%3E");
}
a{color:var(--accent);text-decoration:none}
::-webkit-scrollbar{width:4px}
::-webkit-scrollbar-thumb{background:var(--border-glow);border-radius:99px}

/* ── Navbar (identique à la page chatbot) ── */
.navbar{
  height:58px;
  background:rgba(8,10,16,.9);
  backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  padding:0 20px;position:sticky;top:0;z-index:10;
}
[data-theme="light"] .navbar{background:rgba(255,255,255,.92)}
.brand{
  display:flex;align-items:center;gap:10px;
  font-family:'Bebas Neue',sans-serif;font-size:1.6rem;letter-spacing:.06em;
}
.brand-icon{
  width:32px;height:32px;background:var(--accent);border-radius:6px;
  display:grid;place-items:center;font-size:1rem;box-shadow:0 0 18px var(--accent-glow);
}
.brand-sub{
  font-family:'DM Sans',sans-serif;font-size:.65rem;font-weight:300;
  color:var(--muted);letter-spacing:.1em;text-transform:uppercase;
  display:block;margin-top:-4px;
}
.nav-right{display:flex;gap:8px;align-items:center}
.nav-badge{
  font-size:.75rem;color:var(--muted);background:var(--surface-2);
  border:1px solid var(--border);border-radius:99px;padding:4px 12px;
}
.btn-icon{
  width:34px;height:34px;border-radius:var(--radius-sm);
  border:1px solid var(--border);background:var(--surface-2);
  color:var(--muted);display:grid;place-items:center;
  cursor:pointer;font-size:.95rem;transition:.2s var(--ease);
}
.btn-icon:hover{color:var(--text);border-color:var(--border-glow)}

.theme-toggle{
  position:relative;width:60px;height:30px;border-radius:99px;
  background:var(--surface-3);border:1px solid var(--border);
  cursor:pointer;display:flex;align-items:center;padding:3px;
  transition:.25s var(--ease);
}
.theme-toggle:hover{border-color:var(--accent)}
.toggle-thumb{
  width:24px;height:24px;border-radius:50%;background:var(--accent);
  display:grid;place-items:center;font-size:.75rem;color:#fff;
  transition:transform .25s var(--ease), background .25s;transform:translateX(0);
}
[data-theme="light"] .toggle-thumb{transform:translateX(30px);background:#f5b800}
.toggle-icons{
  position:absolute;width:100%;display:flex;justify-content:space-between;
  padding:0 7px;pointer-events:none;font-size:.7rem;
}

/* ── Page ── */
.page{max-width:920px;margin:0 auto;padding:32px 20px 60px}
.page-header{margin-bottom:26px}
.page-header h1{font-family:'Bebas Neue',sans-serif;font-size:2rem;letter-spacing:.03em}
.page-header p{color:var(--muted);font-size:.88rem;margin-top:2px}

.tabs{display:flex;gap:6px;border-bottom:1px solid var(--border);margin-bottom:24px}
.tab{
  padding:10px 18px;font-size:.85rem;font-weight:600;color:var(--muted);
  cursor:pointer;border-bottom:2px solid transparent;transition:.2s var(--ease);
  display:flex;align-items:center;gap:6px;
}
.tab:hover{color:var(--text)}
.tab.active{color:var(--accent);border-bottom-color:var(--accent)}

.tab-panel{display:none}
.tab-panel.active{display:block}

.empty-state{
  text-align:center;padding:50px 20px;color:var(--muted);font-size:.9rem;
}
.empty-state i{font-size:2rem;display:block;margin-bottom:10px;opacity:.5}

/* ── Zone dangereuse ── */
.danger-zone{
  margin-top:36px;
  border:1px solid rgba(229,9,20,.3);
  border-radius:var(--radius);
  padding:20px 22px;
  background:rgba(229,9,20,.04);
}
.danger-zone-header{
  display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;
}
.danger-zone-header h3{
  font-size:.9rem;color:var(--accent);display:flex;align-items:center;gap:8px;margin-bottom:4px;
}
.danger-zone-header p{font-size:.78rem;color:var(--muted);max-width:480px;line-height:1.5}
.btn-danger-outline{
  padding:9px 16px;border-radius:var(--radius-sm);border:1px solid var(--accent);
  background:transparent;color:var(--accent);font-family:inherit;font-size:.82rem;font-weight:600;
  cursor:pointer;transition:.2s var(--ease);white-space:nowrap;
}
.btn-danger-outline:hover{background:var(--accent);color:#fff}

.danger-zone-confirm{
  display:none;margin-top:18px;padding-top:18px;border-top:1px solid rgba(229,9,20,.2);
}
.danger-zone-confirm.show{display:block}
.danger-zone-confirm label{display:block;font-size:.78rem;color:var(--muted);margin-bottom:8px}
.danger-zone-confirm .input-wrap{position:relative;max-width:320px;margin-bottom:14px}
.danger-zone-confirm .input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.9rem}
.danger-zone-confirm input{
  width:100%;max-width:320px;padding:10px 14px 10px 38px;
  background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);
  color:var(--text);font-family:inherit;font-size:.85rem;
}
.danger-zone-confirm input:focus{outline:none;border-color:var(--accent)}
.danger-zone-actions{display:flex;gap:10px}
.btn-icon-text{
  padding:9px 16px;border-radius:var(--radius-sm);border:1px solid var(--border);
  background:var(--surface-2);color:var(--muted);font-family:inherit;font-size:.82rem;
  cursor:pointer;transition:.2s var(--ease);
}
.btn-icon-text:hover{color:var(--text)}
.btn-danger-solid{
  padding:9px 16px;border-radius:var(--radius-sm);border:none;
  background:var(--accent);color:#fff;font-family:inherit;font-size:.82rem;font-weight:600;
  cursor:pointer;transition:.2s var(--ease);display:flex;align-items:center;gap:6px;
}
.btn-danger-solid:hover{background:#c4070f}
.btn-danger-solid:disabled{opacity:.6;cursor:not-allowed}

.alert{
  padding:10px 14px;border-radius:var(--radius-sm);font-size:.8rem;margin-bottom:14px;
  border:1px solid;display:flex;gap:8px;align-items:flex-start;
}
.alert-error{background:rgba(229,9,20,.08);border-color:rgba(229,9,20,.35);color:#ff6b6f}

.app-credit{
  text-align:center;margin:30px 0 10px;font-size:.72rem;color:var(--muted);
  display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;
}
.app-credit a{color:var(--muted);transition:color .15s var(--ease)}
.app-credit a:hover{color:var(--accent)}
.app-credit .dot{opacity:.4}

/* Historique */
.history-msg{
  display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);
}
.history-role{
  flex-shrink:0;width:26px;height:26px;border-radius:50%;
  display:grid;place-items:center;font-size:.7rem;font-weight:700;
}
.history-role.user{background:var(--accent);color:#fff}
.history-role.assistant{background:var(--surface-3);color:var(--gold)}
.history-content{font-size:.85rem;line-height:1.5;white-space:pre-wrap}
.history-time{font-size:.68rem;color:var(--muted);margin-top:3px}

/* Favoris */
.movies-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:14px}
.movie-tile{
  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);
  padding:10px;transition:.2s var(--ease);
}
.movie-tile:hover{border-color:var(--border-glow);transform:translateY(-2px)}
.movie-tile-title{font-size:.82rem;font-weight:600;margin-bottom:6px;line-height:1.3}
.movie-tile-meta{display:flex;justify-content:space-between;align-items:center;font-size:.72rem;color:var(--muted)}
.tile-star{color:var(--gold)}
.tile-liked{color:#4ade80}

/* Préférences */
.pref-groups{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px}
.pref-group{
  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
  padding:18px;
}
.pref-group h3{
  font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);
  margin-bottom:14px;display:flex;align-items:center;gap:6px;
}
.pref-item{margin-bottom:10px}
.pref-item-top{display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:4px}
.pref-bar-track{height:6px;border-radius:99px;background:var(--surface-3);overflow:hidden}
.pref-bar-fill{height:100%;background:var(--accent);border-radius:99px;transition:width .4s var(--ease)}
</style>
</head>
<body>

<nav class="navbar">
  <a href="{{ route('chatbot') }}" class="brand" style="color:var(--text)">
    <div class="brand-icon">🎬</div>
    <div>
      CineBot AI
      <span class="brand-sub">My Profile</span>
    </div>
  </a>
  <div class="nav-right">
    <a href="{{ route('chatbot') }}" class="btn-icon" title="Retour au chat">
      <i class="bi bi-chat-dots"></i>
    </a>
    <span class="nav-badge">👤 {{ auth()->user()->name }}</span>
    <form method="POST" action="{{ route('logout') }}" style="display:inline">
      @csrf
      <button type="submit" class="btn-icon" title="Log out">
        <i class="bi bi-box-arrow-right"></i>
      </button>
    </form>
    <div class="theme-toggle" id="themeToggle" title="Toggle light/dark theme">
      <div class="toggle-icons"><span>🌙</span><span>☀️</span></div>
      <div class="toggle-thumb" id="toggleThumb"><i class="bi bi-moon-stars-fill" id="toggleIcon"></i></div>
    </div>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <h1>My Profile</h1>
    <p>Favorites and learned preferences based on your conversations with CineBot. <a href="{{ route('chatbot') }}">Your chat history</a> is directly accessible in the chat sidebar.</p>
  </div>

  <div class="tabs">
    <div class="tab active" data-tab="favorites"><i class="bi bi-heart-fill"></i> Favorites</div>
    <div class="tab" data-tab="preferences"><i class="bi bi-graph-up"></i> Preferences</div>
  </div>

  <div class="tab-panel active" id="panel-favorites">
    <div id="favoritesContent"><div class="empty-state"><i class="bi bi-hourglass-split"></i>Loading…</div></div>
  </div>

  <div class="tab-panel" id="panel-preferences">
    <div id="preferencesContent"><div class="empty-state"><i class="bi bi-hourglass-split"></i>Loading…</div></div>
  </div>

  <!-- ── Zone dangereuse ── -->
  <div class="danger-zone">
    <div class="danger-zone-header">
      <div>
        <h3><i class="bi bi-exclamation-triangle-fill"></i> Danger zone</h3>
        <p>Deleting your account permanently erases your history, favorites, and learned preferences. This action cannot be undone.</p>
      </div>
      <button class="btn-danger-outline" id="showDeleteBtn">Delete my account</button>
    </div>

    <div class="danger-zone-confirm" id="deleteConfirmBox">
      <div class="alert alert-error" id="deleteError" style="display:none"></div>
      <label for="deletePassword">Confirm with your password to continue:</label>
      <div class="input-wrap">
        <i class="bi bi-lock"></i>
        <input type="password" id="deletePassword" placeholder="Your password">
      </div>
      <div class="danger-zone-actions">
        <button class="btn-icon-text" id="cancelDeleteBtn">Cancel</button>
        <button class="btn-danger-solid" id="confirmDeleteBtn">
          <i class="bi bi-trash3"></i> Delete permanently
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-credit">
  <span>CineBot AI © 2026 — Ons Ajmi</span>
  <span class="dot">·</span>
  <a href="https://github.com/AjmiOns" target="_blank" rel="noopener"><i class="bi bi-github"></i> GitHub</a>
  <span class="dot">·</span>
  <a href="https://www.linkedin.com/in/ons-ajmi-0ab2982a2/" target="_blank" rel="noopener"><i class="bi bi-linkedin"></i> LinkedIn</a>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? "";

// ── Thème ──
const html = document.documentElement;
const themeToggle = document.getElementById('themeToggle');
const toggleIcon = document.getElementById('toggleIcon');
function applyTheme(t){
  html.setAttribute('data-theme', t);
  localStorage.setItem('cinebot_theme', t);
  toggleIcon.className = t === 'dark' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
}
applyTheme(localStorage.getItem('cinebot_theme') || 'dark');
themeToggle.addEventListener('click', () => {
  applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
});

// ── Tabs ──
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
  });
});

function escapeHtml(s){
  const d = document.createElement('div');
  d.textContent = s ?? '';
  return d.innerHTML;
}
function fmtDate(iso){
  if (!iso) return '';
  const d = new Date(iso);
  return d.toLocaleDateString('en-US', { day:'2-digit', month:'short', year:'numeric' });
}

// ── Favoris ──
async function loadFavorites() {
  const box = document.getElementById('favoritesContent');
  try {
    const res = await fetch('{{ url("/api/user/favorites") }}', { headers: { Accept: 'application/json' } });
    const data = await res.json();
    if (!data?.length) {
      box.innerHTML = '<div class="empty-state"><i class="bi bi-heart"></i>No liked movies yet. Click 👍 under a recommendation to add it here.</div>';
      return;
    }
    box.innerHTML = `<div class="movies-grid">` + data.map(m => `
      <div class="movie-tile">
        <div class="movie-tile-title">${escapeHtml(m.movie_title)}</div>
        <div class="movie-tile-meta">
          <span class="tile-liked"><i class="bi bi-heart-fill"></i> Liked</span>
          ${m.rating ? `<span class="tile-star">★ ${m.rating}/5</span>` : ''}
        </div>
      </div>`).join('') + `</div>`;
  } catch (e) {
    box.innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Impossible de charger les favoris.</div>';
  }
}

// ── Préférences ──
function prefGroupHtml(title, icon, items, maxScore) {
  if (!items?.length) return '';
  const rows = items.map(it => `
    <div class="pref-item">
      <div class="pref-item-top"><span>${escapeHtml(it.value)}</span><span>${it.score}</span></div>
      <div class="pref-bar-track"><div class="pref-bar-fill" style="width:${Math.max(4, (it.score / maxScore) * 100)}%"></div></div>
    </div>`).join('');
  return `<div class="pref-group"><h3><i class="bi ${icon}"></i> ${title}</h3>${rows}</div>`;
}

async function loadPreferences() {
  const box = document.getElementById('preferencesContent');
  try {
    const res = await fetch('{{ url("/api/user/preferences") }}', { headers: { Accept: 'application/json' } });
    const data = await res.json();
    const groups = ['genres', 'actors', 'directors', 'languages'];
    const hasAny = groups.some(g => data?.[g]?.length);
    if (!hasAny) {
      box.innerHTML = '<div class="empty-state"><i class="bi bi-graph-up"></i>Not enough data yet. Like or rate movies to build your profile.</div>';
      return;
    }
    const allScores = groups.flatMap(g => (data[g] || []).map(it => it.score));
    const maxScore = Math.max(1, ...allScores);
    box.innerHTML = `<div class="pref-groups">
      ${prefGroupHtml('Favorite genres', 'bi-film', data.genres, maxScore)}
      ${prefGroupHtml('Favorite actors', 'bi-person-badge', data.actors, maxScore)}
      ${prefGroupHtml('Favorite directors', 'bi-camera-reels', data.directors, maxScore)}
      ${prefGroupHtml('Languages watched', 'bi-translate', data.languages, maxScore)}
    </div>`;
  } catch (e) {
    box.innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Unable to load preferences.</div>';
  }
}

// ── Suppression de compte ──
const showDeleteBtn    = document.getElementById('showDeleteBtn');
const deleteConfirmBox = document.getElementById('deleteConfirmBox');
const cancelDeleteBtn  = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const deletePassword   = document.getElementById('deletePassword');
const deleteError      = document.getElementById('deleteError');

showDeleteBtn.addEventListener('click', () => {
  deleteConfirmBox.classList.add('show');
  showDeleteBtn.style.display = 'none';
  deletePassword.focus();
});

cancelDeleteBtn.addEventListener('click', () => {
  deleteConfirmBox.classList.remove('show');
  showDeleteBtn.style.display = '';
  deletePassword.value = '';
  deleteError.style.display = 'none';
});

confirmDeleteBtn.addEventListener('click', async () => {
  const password = deletePassword.value;
  if (!password) {
    deleteError.textContent = 'Veuillez saisir votre mot de passe.';
    deleteError.style.display = 'flex';
    return;
  }

  confirmDeleteBtn.disabled = true;
  confirmDeleteBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Deleting…';

  try {
    const res = await fetch('{{ url("/profile") }}', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF,
      },
      body: JSON.stringify({ password }),
    });

    if (res.ok) {
      window.location.href = '{{ route("login") }}';
      return;
    }

    const data = await res.json().catch(() => ({}));
    deleteError.textContent = data?.errors?.password?.[0] || data?.message || 'Mot de passe incorrect.';
    deleteError.style.display = 'flex';
  } catch (e) {
    deleteError.textContent = 'Something went wrong. Please try again.';
    deleteError.style.display = 'flex';
  } finally {
    confirmDeleteBtn.disabled = false;
    confirmDeleteBtn.innerHTML = '<i class="bi bi-trash3"></i> Delete permanently';
  }
});

loadFavorites();
loadPreferences();
</script>

</body>
</html>

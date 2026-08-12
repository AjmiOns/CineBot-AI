<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>CineBot AI</title>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════════════
   TOKENS — dark (default)
═══════════════════════════════════════════════════════════════ */
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
  --user-bg:      #e50914;
  --bot-bg:       #13151f;
  --radius:       14px;
  --radius-sm:    8px;
  --ease:         cubic-bezier(.4,0,.2,1);
  --shadow-card:  0 8px 32px rgba(0,0,0,.6);
}

/* ═══════════════════════════════════════════════════════════════
   TOKENS — light
═══════════════════════════════════════════════════════════════ */
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
  --user-bg:      #e50914;
  --bot-bg:       #ffffff;
  --shadow-card:  0 4px 20px rgba(0,0,0,.12);
}

/* ═══════════════════════════════════════════════════════════════
   RESET + BASE
═══════════════════════════════════════════════════════════════ */
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--bg);
  color:var(--text);
  display:flex;
  flex-direction:column;
  transition:background .3s var(--ease), color .3s var(--ease);
}
[data-theme="dark"] body {
  background-image:
    radial-gradient(ellipse 80% 60% at 50% -10%, rgba(229,9,20,.08) 0%, transparent 60%),
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='.025'/%3E%3C/svg%3E");
}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}
::-webkit-scrollbar{width:4px}
::-webkit-scrollbar-thumb{background:var(--border-glow);border-radius:99px}

/* ═══════════════════════════════════════════════════════════════
   NAVBAR
═══════════════════════════════════════════════════════════════ */
.navbar{
  height:58px;
  background:rgba(8,10,16,.9);
  backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:0 20px;
  flex-shrink:0;
  position:relative;
  z-index:10;
  transition:background .3s;
}
[data-theme="light"] .navbar {
  background: rgba(255,255,255,.92);
}
.brand{
  display:flex;align-items:center;gap:10px;
  font-family:'Bebas Neue',sans-serif;
  font-size:1.6rem;letter-spacing:.06em;
  color:var(--text);
}
.brand-icon{
  width:32px;height:32px;
  background:var(--accent);
  border-radius:6px;
  display:grid;place-items:center;
  font-size:1rem;
  box-shadow:0 0 18px var(--accent-glow);
}
.brand-sub{
  font-family:'DM Sans',sans-serif;
  font-size:.65rem;font-weight:300;
  color:var(--muted);letter-spacing:.1em;
  text-transform:uppercase;
  display:block;margin-top:-4px;
}
.nav-right{display:flex;gap:6px;align-items:center}
.nav-divider{width:1px;height:22px;background:var(--border);margin:0 4px;flex-shrink:0}

.btn-icon{
  width:34px;height:34px;
  border-radius:var(--radius-sm);
  border:1px solid var(--border);
  background:var(--surface-2);
  color:var(--muted);
  display:grid;place-items:center;
  cursor:pointer;font-size:.95rem;
  transition:.2s var(--ease);
}
.btn-icon:hover{color:var(--text);border-color:var(--border-glow)}

/* ── Menu déroulant générique (avatar utilisateur / options) ── */
.nav-menu{position:relative}
.dropdown-panel{
  position:absolute;top:calc(100% + 10px);right:0;
  min-width:198px;
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:var(--shadow-card);
  padding:6px;
  opacity:0;visibility:hidden;transform:translateY(-6px);
  transition:opacity .16s var(--ease), transform .16s var(--ease), visibility .16s;
  z-index:30;
}
.nav-menu.open .dropdown-panel{
  opacity:1;visibility:visible;transform:translateY(0);
}
.dropdown-item{
  display:flex;align-items:center;gap:10px;
  width:100%;padding:9px 10px;
  border:none;background:none;
  border-radius:var(--radius-sm);
  color:var(--text);font-family:inherit;font-size:.83rem;
  text-align:left;cursor:pointer;
  transition:background .15s var(--ease);
}
.dropdown-item i{width:16px;text-align:center;color:var(--muted);font-size:.9rem}
.dropdown-item:hover{background:var(--surface-2)}
.dropdown-item-danger:hover{background:var(--accent-dim);color:var(--accent)}
.dropdown-item-danger:hover i{color:var(--accent)}
.dropdown-divider{height:1px;background:var(--border);margin:5px 4px}

/* ── Puce utilisateur (avatar + nom) ── */
.user-chip{
  display:flex;align-items:center;gap:8px;
  padding:4px 10px 4px 4px;
  background:var(--surface-2);
  border:1px solid var(--border);
  border-radius:99px;
  cursor:pointer;font-family:inherit;
  transition:.2s var(--ease);
}
.user-chip:hover{border-color:var(--border-glow)}
.nav-menu.open .user-chip{border-color:var(--accent)}
.user-avatar{
  width:26px;height:26px;border-radius:50%;
  display:grid;place-items:center;flex-shrink:0;
  font-size:.72rem;font-weight:700;color:#fff;
}
.user-chip-name{
  font-size:.78rem;color:var(--text);font-weight:500;
  max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.user-chip-caret{font-size:.65rem;color:var(--muted);transition:transform .2s var(--ease)}
.nav-menu.open .user-chip-caret{transform:rotate(180deg)}

/* ── Theme toggle pill ── */
.theme-toggle{
  position:relative;
  width:52px;height:28px;
  border-radius:99px;
  background:var(--surface-3);
  border:1px solid var(--border);
  cursor:pointer;
  transition:.25s var(--ease);
  flex-shrink:0;
  display:flex;align-items:center;
  padding:3px;
}
.theme-toggle:hover{border-color:var(--accent)}
.toggle-thumb{
  width:20px;height:20px;border-radius:50%;
  background:var(--accent);
  display:grid;place-items:center;
  font-size:.65rem;color:#fff;
  transition:transform .25s var(--ease), background .25s;
  transform:translateX(0);
}
[data-theme="light"] .toggle-thumb{
  transform:translateX(26px);
  background:#f5b800;
}
.toggle-icons{
  position:absolute;
  width:100%;display:flex;justify-content:space-between;
  padding:0 6px;pointer-events:none;font-size:.62rem;
}

/* ═══════════════════════════════════════════════════════════════
   LAYOUT
═══════════════════════════════════════════════════════════════ */
.layout{
  flex:1;
  display:flex;
  overflow:hidden;
  min-height:0;
}

/* ── Sidebar ── */
.sidebar{
  width:280px;flex-shrink:0;
  background:var(--surface);
  border-right:1px solid var(--border);
  display:flex;flex-direction:column;
  overflow:hidden;
  transition:background .3s, border-color .3s;
}
.sidebar-header{
  padding:14px 16px;
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:8px;
  font-size:.8rem;font-weight:600;
  letter-spacing:.05em;text-transform:uppercase;
  color:var(--muted);
}
.pulse-dot{
  width:7px;height:7px;border-radius:50%;
  background:var(--accent);
  box-shadow:0 0 10px var(--accent-glow);
  animation:pulse 2s infinite;
}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

.sidebar-scroll{
  overflow-y:auto;padding:10px;
  flex:1;
}
.rec-item{
  display:flex;gap:10px;padding:9px;
  border-radius:10px;
  background:var(--surface-2);
  border:1px solid var(--border);
  margin-bottom:7px;cursor:pointer;
  transition:.2s var(--ease);
}
.rec-item:hover{
  border-color:var(--border-glow);
  transform:translateX(3px);
  background:var(--surface-3);
}
.rec-poster{
  width:44px;height:64px;object-fit:cover;
  border-radius:6px;flex-shrink:0;
  background:var(--surface-3);
}
.rec-info{flex:1;min-width:0}
.rec-title{
  font-size:.78rem;font-weight:600;
  color:var(--text);line-height:1.3;
  margin-bottom:5px;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.rec-meta{font-size:.69rem;color:var(--muted);display:flex;gap:8px;align-items:center}
.star-rating{color:var(--gold);font-weight:600}
.match-badge{
  background:linear-gradient(135deg, var(--accent), var(--gold));
  color:#fff;font-weight:700;padding:1.5px 7px;border-radius:99px;
  font-size:.62rem;letter-spacing:.02em;white-space:nowrap;
}

/* ── Feedback (like/dislike/note) sur une recommandation de la sidebar ── */
.rec-feedback{
  display:flex;align-items:center;gap:8px;margin-top:6px;
}
.rec-feedback .fb-stars{gap:1px}
.rec-feedback .fb-stars span{font-size:.62rem}
.rec-saved-toast{font-size:.58rem;color:var(--gold)}

/* ── Historique des discussions (sidebar droite, style Claude/ChatGPT) ── */
.history-sidebar{
  width:260px;flex-shrink:0;
  background:var(--surface);
  border-left:1px solid var(--border);
  display:flex;flex-direction:column;
  overflow:hidden;
  transition:background .3s, border-color .3s;
}
.history-header{
  padding:14px 16px;
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  font-size:.75rem;font-weight:600;letter-spacing:.05em;
  color:var(--muted);
}
.new-chat-btn{
  width:26px;height:26px;border-radius:7px;
  border:1px solid var(--border);background:var(--surface-2);
  color:var(--accent);display:grid;place-items:center;
  cursor:pointer;font-size:.8rem;transition:.2s var(--ease);
}
.new-chat-btn:hover{background:var(--accent);color:#fff;border-color:var(--accent)}

.history-list{overflow-y:auto;flex:1;padding:8px}
.history-empty{
  text-align:center;color:var(--muted);font-size:.75rem;
  padding:30px 12px;
}
.history-item{
  position:relative;
  padding:10px 32px 10px 12px;
  border-radius:9px;margin-bottom:4px;
  cursor:pointer;transition:.15s var(--ease);
  border:1px solid transparent;
}
.history-item:hover{background:var(--surface-2)}
.history-item.active{
  background:var(--accent-dim);
  border-color:var(--accent-glow);
}
.history-item-title{
  font-size:.78rem;font-weight:500;color:var(--text);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.history-item.active .history-item-title{color:var(--accent)}
.history-item-date{font-size:.65rem;color:var(--muted);margin-top:2px}
.history-item-delete{
  position:absolute;right:6px;top:50%;transform:translateY(-50%);
  width:22px;height:22px;border-radius:6px;border:none;
  background:transparent;color:var(--muted);
  display:none;place-items:center;cursor:pointer;
  transition:.15s var(--ease);
}
.history-item:hover .history-item-delete{display:grid}
.history-item-delete:hover{background:var(--accent-dim);color:var(--accent)}

@media (max-width: 1100px){
  .history-sidebar{display:none}
}

/* skeleton */
.skel{display:flex;gap:10px;padding:9px;margin-bottom:7px}
.skel-poster{width:44px;height:64px;border-radius:6px;background:var(--surface-3);animation:shimmer 1.4s infinite}
.skel-lines{flex:1;display:flex;flex-direction:column;gap:6px;padding-top:4px}
.skel-line{height:9px;border-radius:4px;background:var(--surface-3);animation:shimmer 1.4s infinite}
.skel-line.s{width:55%}
@keyframes shimmer{0%,100%{opacity:.5}50%{opacity:1}}

/* ── Chat column ── */
.chat-col{
  flex:1;display:flex;flex-direction:column;
  min-width:0;overflow:hidden;
  background:var(--bg);
  transition:background .3s;
}
.chat-messages{
  flex:1;overflow-y:auto;
  padding:20px 24px;
  display:flex;flex-direction:column;
  gap:4px;
}

/* ── Welcome ── */
.welcome{
  margin:auto;
  max-width:520px;
  text-align:center;
  padding:40px 20px;
  animation:fadeUp .5s var(--ease);
}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.welcome-icon{
  width:70px;height:70px;margin:0 auto 18px;
  background:linear-gradient(135deg,var(--accent),#ff4444);
  border-radius:18px;
  display:grid;place-items:center;
  font-size:2rem;
  box-shadow:0 0 40px var(--accent-glow);
}
.welcome h2{
  font-family:'Bebas Neue',sans-serif;
  font-size:1.9rem;letter-spacing:.08em;
  margin-bottom:10px;
}
.welcome p{color:var(--muted);font-size:.9rem;line-height:1.7;margin-bottom:22px}
.chips{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
.chip{
  padding:7px 14px;border-radius:99px;
  background:var(--surface-2);
  border:1px solid var(--border);
  font-size:.8rem;font-weight:500;
  cursor:pointer;transition:.2s var(--ease);white-space:nowrap;
}
.chip:hover{background:var(--accent-dim);border-color:var(--accent);color:var(--accent)}

/* ── Messages ── */
.msg-row{
  display:flex;align-items:flex-end;gap:9px;
  animation:msgIn .28s var(--ease) both;
}
.msg-row.user-row{flex-direction:row-reverse}
@keyframes msgIn{from{opacity:0;transform:translateY(8px) scale(.97)}to{opacity:1;transform:none}}

.avatar{
  width:30px;height:30px;border-radius:50%;
  flex-shrink:0;display:grid;place-items:center;
  font-size:.9rem;background:var(--accent);
}
.msg-group{display:flex;flex-direction:column;gap:3px;max-width:76%}
.user-row .msg-group{align-items:flex-end}

.bubble{
  padding:11px 15px;border-radius:var(--radius);
  font-size:.9rem;line-height:1.7;
}
.user-bubble{
  background:var(--user-bg);
  color:#fff;border-bottom-right-radius:4px;
}
.bot-bubble{
  background:var(--bot-bg);
  border:1px solid var(--border);
  color:var(--text);border-bottom-left-radius:4px;
  transition:background .3s, border-color .3s, color .3s;
}
.bot-bubble.wide{max-width:100%}

/* Markdown inside bot bubble */
.bot-bubble p{margin:0 0 8px}
.bot-bubble p:last-child{margin:0}
.bot-bubble ol,.bot-bubble ul{margin:6px 0 10px 0;padding-left:20px}
.bot-bubble li{margin-bottom:5px}
.bot-bubble strong{color:var(--text);font-weight:600}
.bot-bubble h1,.bot-bubble h2,.bot-bubble h3{
  font-family:'Bebas Neue',sans-serif;
  letter-spacing:.06em;margin:12px 0 6px;color:var(--accent)
}
.bot-bubble code{
  background:var(--surface-3);padding:1px 5px;
  border-radius:4px;font-size:.82em
}
.bot-bubble a{color:var(--accent)}

.msg-time{font-size:.65rem;color:var(--muted);padding:0 4px}
.user-row .msg-time{text-align:right}

/* ── Loading ── */
.loading-row{display:flex;gap:9px;align-items:flex-end}
.loading-bubble{
  display:flex;align-items:center;gap:8px;
  padding:11px 16px;
  background:var(--bot-bg);border:1px solid var(--border);
  border-radius:var(--radius);border-bottom-left-radius:4px;
}
.loading-bubble span{font-size:.82rem;color:var(--muted)}
.dots{display:flex;gap:4px}
.dots i{
  width:6px;height:6px;border-radius:50%;
  background:var(--accent);
  animation:bounce 1.1s infinite ease-in-out;
}
.dots i:nth-child(2){animation-delay:.18s}
.dots i:nth-child(3){animation-delay:.36s}
@keyframes bounce{0%,80%,100%{transform:scale(.55);opacity:.4}40%{transform:scale(1);opacity:1}}

/* ── Error ── */
.error-row{display:flex;gap:9px;align-items:flex-end}
.error-bubble{
  display:flex;align-items:center;gap:8px;
  padding:10px 14px;border-radius:var(--radius);
  background:rgba(229,9,20,.1);border:1px solid rgba(229,9,20,.3);
  color:var(--accent);font-size:.85rem;
}

/* ── Movie cards grid ── */
.movies-grid{
  display:flex;flex-wrap:wrap;gap:10px;
  padding:4px 0;
}
.movie-card{
  width:120px;border-radius:var(--radius-sm);
  background:var(--surface-2);border:1px solid var(--border);
  box-shadow:var(--shadow-card);
  cursor:pointer;overflow:hidden;
  transition:.25s var(--ease);
  flex-shrink:0;
}
.movie-card:hover{
  transform:translateY(-4px) scale(1.03);
  border-color:var(--border-glow);
  box-shadow:0 12px 30px rgba(0,0,0,.7);
}
.movie-poster{
  width:100%;height:175px;object-fit:cover;display:block;
  background:var(--surface-3);
}
.movie-info{padding:8px}
.movie-title{
  font-size:.73rem;font-weight:600;
  color:var(--text);line-height:1.3;margin-bottom:4px;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.movie-meta{
  font-size:.68rem;color:var(--muted);
  display:flex;justify-content:space-between;align-items:center;
}
.movie-star{color:var(--gold);font-weight:600}

/* ── Feedback (like/dislike/note) sur chaque carte film ── */
.movie-feedback{
  display:flex;flex-direction:column;align-items:center;gap:4px;
  padding:6px 6px 8px;border-top:1px solid var(--border);
}
.fb-row{display:flex;align-items:center;justify-content:center;gap:10px}
.fb-btn{
  background:none;border:none;cursor:pointer;font-size:.9rem;
  padding:2px;line-height:1;opacity:.55;
  transition:transform .15s var(--ease), opacity .15s var(--ease), filter .15s var(--ease);
}
.fb-btn:hover{opacity:1;transform:scale(1.2)}
.fb-btn.active-like{opacity:1;filter:drop-shadow(0 0 4px #4ade80)}
.fb-btn.active-dislike{opacity:1;filter:drop-shadow(0 0 4px #ff6b6f)}
.fb-stars{display:flex;gap:1px;cursor:pointer}
.fb-stars span{
  font-size:.72rem;color:var(--border-glow);
  transition:color .15s var(--ease);
}
.fb-stars span.filled{color:var(--gold)}
.fb-saved-toast{
  font-size:.6rem;color:var(--gold);opacity:0;height:0;
  transition:opacity .3s var(--ease);
}
.fb-saved-toast.show{opacity:1}

/* ── RAG debug badge ── */
.rag-badge{
  display:inline-flex;align-items:center;gap:4px;
  font-size:.65rem;padding:2px 8px;border-radius:99px;
  background:var(--accent-dim);border:1px solid var(--accent-glow);
  color:var(--accent);margin-top:5px;width:fit-content;
}

/* ── Input area ── */
.input-area{
  padding:14px 20px;
  border-top:1px solid var(--border);
  background:rgba(8,10,16,.8);
  backdrop-filter:blur(10px);
  flex-shrink:0;
  transition:background .3s;
}
[data-theme="light"] .input-area {
  background:rgba(245,245,247,.9);
}
.input-wrap{
  display:flex;align-items:center;gap:10px;
  background:var(--surface-2);
  border:1px solid var(--border);
  border-radius:var(--radius);
  padding:8px 8px 8px 14px;
  transition:.2s var(--ease);
}
.input-wrap:focus-within{
  border-color:var(--accent);
  box-shadow:0 0 0 3px var(--accent-glow);
}
.chat-input{
  flex:1;border:none;outline:none;
  background:transparent;color:var(--text);
  font-family:'DM Sans',sans-serif;font-size:.9rem;
  resize:none;line-height:1.5;max-height:120px;overflow-y:auto;
}
.chat-input::placeholder{color:var(--muted)}
.send-btn{
  width:38px;height:38px;border-radius:var(--radius-sm);
  background:var(--accent);border:none;color:#fff;
  cursor:pointer;display:grid;place-items:center;
  font-size:1rem;transition:.2s var(--ease);flex-shrink:0;
}
.send-btn:hover:not(:disabled){background:#c4070f;transform:scale(1.05)}
.send-btn:active:not(:disabled){transform:scale(.95)}
.send-btn:disabled{opacity:.35;cursor:not-allowed}

.input-hint{
  font-size:.7rem;color:var(--muted);margin-top:6px;text-align:center;
}

/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════════════ */
@media(max-width:720px){
  .sidebar{display:none}
  .chat-messages{padding:14px}
  .brand-sub{display:none}
}
</style>
</head>
<body>

<!-- ══════════════ NAVBAR ══════════════ -->
<nav class="navbar">
  <div class="brand">
    <div class="brand-icon">🎬</div>
    <div>
      CineBot AI
      <span class="brand-sub">RAG · Groq · TMDB</span>
    </div>
  </div>
  <div class="nav-right">

    {{-- ── DARK / LIGHT TOGGLE ── --}}
    <div class="theme-toggle" id="themeToggle" title="Toggle light/dark theme">
      <div class="toggle-icons">
        <span>🌙</span>
        <span>☀️</span>
      </div>
      <div class="toggle-thumb" id="toggleThumb">
        <i class="bi bi-moon-stars-fill" id="toggleIcon"></i>
      </div>
    </div>

    {{-- ── OUTILS (RAG debug / effacer la conversation) ── --}}
    <div class="nav-menu" id="toolsMenu">
      <button class="btn-icon" id="toolsMenuBtn" title="Tools">
        <i class="bi bi-three-dots"></i>
      </button>
      <div class="dropdown-panel">
        <button class="dropdown-item" id="ragDebugBtn">
          <i class="bi bi-hdd-network"></i> Test RAG connection
        </button>
        <button class="dropdown-item" id="clearBtn">
          <i class="bi bi-trash3"></i> Clear conversation
        </button>
      </div>
    </div>

    @auth
      <div class="nav-divider"></div>

      @php
        $avatarPalette = ['#e50914','#f5b800','#4ade80','#38bdf8','#a78bfa','#fb7185'];
        $avatarColor   = $avatarPalette[ord(mb_substr(auth()->user()->name, 0, 1)) % count($avatarPalette) ];
      @endphp

      {{-- ── COMPTE (avatar + menu) ── --}}
      <div class="nav-menu" id="userMenu">
        <button class="user-chip" id="userMenuBtn">
          <span class="user-avatar" style="background:{{ $avatarColor }}">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
          <span class="user-chip-name">{{ auth()->user()->name }}</span>
          <i class="bi bi-chevron-down user-chip-caret"></i>
        </button>
        <div class="dropdown-panel">
          @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
              <i class="bi bi-speedometer2"></i> Admin Dashboard
            </a>
          @endif
          <a href="{{ route('profile') }}" class="dropdown-item">
            <i class="bi bi-person-lines-fill"></i> My Profile
          </a>
          <div class="dropdown-divider"></div>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item dropdown-item-danger">
              <i class="bi bi-box-arrow-right"></i> Log out
            </button>
          </form>
        </div>
      </div>
    @endauth
  </div>
</nav>

<!-- ══════════════ LAYOUT ══════════════ -->
<div class="layout">

  <!-- ── Sidebar ── -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="pulse-dot"></div>
      RECOMMENDATIONS
    </div>
    <div class="sidebar-scroll" id="sidebarContent">
      <div class="skel"><div class="skel-poster"></div><div class="skel-lines"><div class="skel-line"></div><div class="skel-line s"></div></div></div>
      <div class="skel"><div class="skel-poster"></div><div class="skel-lines"><div class="skel-line"></div><div class="skel-line s"></div></div></div>
      <div class="skel"><div class="skel-poster"></div><div class="skel-lines"><div class="skel-line"></div><div class="skel-line s"></div></div></div>
      <div class="skel"><div class="skel-poster"></div><div class="skel-lines"><div class="skel-line"></div><div class="skel-line s"></div></div></div>
    </div>
  </aside>

  <!-- ── Chat column ── -->
  <div class="chat-col">
    <div class="chat-messages" id="chatBox">
      <div class="welcome" id="welcomeState">
        <div class="welcome-icon">🍿</div>
        <h2>What are you watching tonight?</h2>
        <p>Ask me anything about movies — plots, cast, recommendations, trailers, genres, directors. Our AI searches a real movie database for you.</p>
        <div class="chips">
          <span class="chip" onclick="sendSuggestion('Recommend me great sci-fi films')">🚀 Best sci-fi</span>
          <span class="chip" onclick="sendSuggestion('Tell me about The Shawshank Redemption')">🎩 Shawshank Redemption</span>
          <span class="chip" onclick="sendSuggestion('Recommend horror movies like Get Out')">👻 Like Get Out</span>
          <span class="chip" onclick="sendSuggestion('Best Christopher Nolan films ranked')">🎬 Nolan films</span>
          <span class="chip" onclick="sendSuggestion('Sad anime movies that will make me cry')">😢 Sad anime</span>
          <span class="chip" onclick="sendSuggestion('Action movies from the 2000s')">💥 2000s action</span>
          <span class="chip" onclick="sendSuggestion('Explain what makes a good cinematography')">🎥 Cinematography</span>
          <span class="chip" onclick="sendSuggestion('Movies with twist endings')">🌀 Twist endings</span>
        </div>
      </div>
    </div>

    <div class="input-area">
      <div class="input-wrap">
        <textarea
          id="chatInput"
          class="chat-input"
          rows="1"
          placeholder="Ask about any movie, actor, genre, director…"
        ></textarea>
        <button class="send-btn" id="sendBtn" onclick="sendMessage()" title="Send (Enter)">
          <i class="bi bi-send-fill"></i>
        </button>
      </div>
      <div class="input-hint">
        Enter to send · Shift+Enter for new line </br>
        <span style="opacity:.5">·</span>
        CineBot is AI and can make mistakes. Always verify informations from official sources.
      </div>
    </div>
  </div>

  <!-- ── Historique des discussions (comme Claude/ChatGPT) ── -->
  <aside class="history-sidebar">
    <div class="history-header">
      <span>CHATS</span>
      <button class="new-chat-btn" id="newChatBtn" title="New chat">
        <i class="bi bi-plus-lg"></i>
      </button>
    </div>
    <div class="history-list" id="historyList">
      <div class="history-empty">No conversations yet.</div>
    </div>
  </aside>
</div>


<script>
// ══════════════════════════════════════════════════════
// CONFIG
// ══════════════════════════════════════════════════════
const API_CHAT = "{{ url('/api/chat') }}";
const API_RECS = "{{ url('/api/recommendations') }}";
const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? "";

marked.setOptions({ breaks: true, gfm: true });

// ══════════════════════════════════════════════════════
// STATE
// ══════════════════════════════════════════════════════
let isLoading = false;

/**
 * FIX: Track the ordered list of movies the bot has recommended
 * in this session so we can resolve anaphora like "the first one",
 * "that movie", "tell me more about it", etc.
 *
 * Each entry: { title, position }  (1-indexed, matching the numbered list)
 */
let sessionRecommendations = [];  // [{title, year, id}, ...]
let lastBotMoviesMentioned  = []; // flat list of movie titles from last reply
let currentSessionId = null;      // discussion active (null = pas encore de message envoyé)

// ══════════════════════════════════════════════════════
// THEME TOGGLE
// ══════════════════════════════════════════════════════
const html        = document.documentElement;
const themeToggle = document.getElementById('themeToggle');
const toggleIcon  = document.getElementById('toggleIcon');

function applyTheme(theme) {
  html.setAttribute('data-theme', theme);
  localStorage.setItem('cinebot_theme', theme);
  toggleIcon.className = theme === 'dark'
    ? 'bi bi-moon-stars-fill'
    : 'bi bi-sun-fill';
}

// Restore saved preference
applyTheme(localStorage.getItem('cinebot_theme') || 'dark');

themeToggle.addEventListener('click', () => {
  const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  applyTheme(next);
});

// ══════════════════════════════════════════════════════
// MENUS DÉROULANTS (compte utilisateur / outils)
// ══════════════════════════════════════════════════════
document.querySelectorAll('.nav-menu > button').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const menu   = btn.closest('.nav-menu');
    const isOpen = menu.classList.contains('open');
    document.querySelectorAll('.nav-menu.open').forEach(m => m.classList.remove('open'));
    if (!isOpen) menu.classList.add('open');
  });
});
document.addEventListener('click', () => {
  document.querySelectorAll('.nav-menu.open').forEach(m => m.classList.remove('open'));
});

// ══════════════════════════════════════════════════════
// HELPERS
// ══════════════════════════════════════════════════════
function ts() {
  return new Date().toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'});
}
function scrollBottom() {
  const box = document.getElementById('chatBox');
  requestAnimationFrame(() => box.scrollTop = box.scrollHeight);
}
function hideWelcome() {
  document.getElementById('welcomeState')?.remove();
}
function posterSrc(path) {
  if (!path) return 'https://placehold.co/120x175/13151f/5a5f7a?text=No+Poster';
  if (path.startsWith('http')) return path;
  return `https://image.tmdb.org/t/p/w300${path}`;
}
function escapeHtml(s) {
  return String(s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Auto-resize textarea
const chatInput = document.getElementById('chatInput');
chatInput.addEventListener('input', () => {
  chatInput.style.height = 'auto';
  chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
});

// ══════════════════════════════════════════════════════
// ANAPHORA RESOLUTION
// ══════════════════════════════════════════════════════
/**
 * Detect if the user is referring to a previously recommended movie
 * by ordinal ("the first one", "2nd one", "that movie", etc.) and
 * expand the query so the backend has enough context to answer.
 *
 * Returns the (possibly enriched) query string.
 */
function resolveAnaphora(text) {
  const lower = text.toLowerCase();

  // Ordinal patterns: "the first one", "1st", "#1", "the second film", etc.
  const ordinals = [
    { words: ['first', '1st', '#1', 'number 1', 'number one'],       idx: 0 },
    { words: ['second', '2nd', '#2', 'number 2', 'number two'],      idx: 1 },
    { words: ['third', '3rd', '#3', 'number 3', 'number three'],     idx: 2 },
    { words: ['fourth', '4th', '#4', 'number 4', 'number four'],     idx: 3 },
    { words: ['fifth', '5th', '#5', 'number 5', 'number five'],      idx: 4 },
  ];

  for (const { words, idx } of ordinals) {
    if (words.some(w => lower.includes(w))) {
      const movie = sessionRecommendations[idx];
      if (movie) {
        // Replace the ambiguous phrase with the explicit movie title
        console.log(`[Anaphora] Resolved ordinal ${idx+1} → "${movie.title}"`);
        return `${text} (referring to "${movie.title}" ${movie.year || ''})`;
      }
    }
  }

  // Generic pronoun / demonstrative resolution
  // Covers: "this anime", "cast of this", "who stars in it", "its plot", etc.
  const pronounPatterns = [
    'tell me about it', 'tell me more about it', 'more about it',
    'that movie', 'the movie', 'this film', 'that film',
    'this anime', 'that anime', 'this movie', 'that movie',
    'this film', 'that film', 'this one', 'that one',
    'this show', 'that show', 'this series', 'that series',
    'plot of it', 'story of it', 'cast of it', 'about it',
    'tell me more', 'more details', 'expand on', 'elaborate',
    'who stars', 'who directed', 'who made', 'when was it',
    'how long is', 'runtime of', 'where to watch', 'is it on',
    'cast of this', 'cast of that', 'director of this', 'director of that',
    'soundtrack of', 'trailer of', 'sequel of', 'prequel of',
    'similar to this', 'like this', 'like that',
  ];
  const lastMovie = lastBotMoviesMentioned[0]; // most recently deeply discussed

  if (lastMovie && pronounPatterns.some(p => lower.includes(p))) {
    console.log(`[Anaphora] Resolved pronoun → "${lastMovie}"`);
    return `${text} (referring to "${lastMovie}")`;
  }

  // Bare short query with "it" (e.g. "summarize it", "rate it")
  if (lastMovie && /\bit\b/.test(lower) && lower.split(' ').length <= 6) {
    console.log(`[Anaphora] Resolved bare "it" → "${lastMovie}"`);
    return `${text} (referring to "${lastMovie}")`;
  }

  return text; // no change needed
}

/**
 * Parse bot reply markdown to extract movie titles it mentioned.
 * Looks for **Bold Title** patterns and numbered list entries.
 *
 * Returns titles ordered so the MOST RECENTLY / DEEPLY discussed movie
 * comes FIRST — that is the one pronouns like "this anime" refer to.
 *
 * Heuristic: when the reply is a single-movie deep-dive (no numbered list),
 * the first bold title IS the topic. When it's a numbered recommendation list,
 * we keep the list order but put the subject of the longest paragraph first
 * (the bot usually writes most about movie #1).
 */
function extractMoviesFromReply(markdown) {
  const titles = [];
  const boldRe = /\*\*([^*]+)\*\*/g;
  let m;
  while ((m = boldRe.exec(markdown)) !== null) {
    const t = m[1].trim();
    // Skip section headers (all caps, very short, no spaces typical of headers)
    if (t.length > 2 && t.length < 80 && !titles.includes(t)) {
      titles.push(t);
    }
  }

  // If only one title found, that's the topic — nothing to reorder
  if (titles.length <= 1) return titles;

  // If the reply is a numbered list (contains "1."), the first bold title
  // in the list is position #1. Keep that order — ordinal resolution uses it.
  // But for pronoun resolution ("this anime"), use the LAST title the bot
  // gave a detailed paragraph about. Detect this by finding the bold title
  // that appears closest to the longest prose paragraph.
  const paragraphs = markdown.split(/\n\n+/);
  let bestTitle = titles[0];
  let bestLen = 0;
  for (const para of paragraphs) {
    if (para.length > bestLen) {
      // Find which title is mentioned in this (longest) paragraph
      for (const t of titles) {
        if (para.includes(t)) {
          bestTitle = t;
          bestLen = para.length;
          break;
        }
      }
    }
  }

  // Put the most-discussed title first for pronoun resolution,
  // keep the rest in original order for ordinal resolution
  const reordered = [bestTitle, ...titles.filter(t => t !== bestTitle)];
  return reordered;
}

/**
 * Parse a numbered recommendation list from the bot's markdown reply.
 * Returns titles IN LIST ORDER (1, 2, 3...) — used to build sessionRecommendations.
 *
 * Matches patterns like:
 *   "1. **Interstellar** (2014) - ..."
 *   "1. Interstellar (2014)"
 *   "1) **Star Wars**"
 */
function extractNumberedListFromReply(markdown) {
  const titles = [];
  const lines = markdown.split('\n');

  // Regex: line starting with a number (1. or 1)) followed by optional bold title
  const listRe = /^\s*\d+[.)\s]+\*{0,2}([^*(\n*]+?)\*{0,2}(?:\s*\(|\s*-|\s*—|\s*$)/;

  for (const line of lines) {
    const m = listRe.exec(line);
    if (m) {
      const title = m[1].trim();
      if (title.length > 1 && title.length < 80) {
        titles.push(title);
      }
    }
  }

  return titles;
}

// ══════════════════════════════════════════════════════
// MESSAGE RENDERING
// ══════════════════════════════════════════════════════
function addUserMessage(text) {
  hideWelcome();
  const box = document.getElementById('chatBox');
  const row = document.createElement('div');
  row.className = 'msg-row user-row';
  row.innerHTML = `
    <div class="msg-group">
      <div class="bubble user-bubble">${escapeHtml(text)}</div>
      <div class="msg-time">${ts()}</div>
    </div>`;
  box.appendChild(row);
  scrollBottom();
}

function addBotMessage(markdown, wide = false, ragSources = 0) {
  const box = document.getElementById('chatBox');
  const row = document.createElement('div');
  row.className = 'msg-row';

  const ragBadge = ragSources > 0
    ? `<div class="rag-badge"><i class="bi bi-database-check"></i> RAG · ${ragSources} source${ragSources>1?'s':''} retrieved</div>`
    : '';

  row.innerHTML = `
    <div class="avatar">🎬</div>
    <div class="msg-group" style="align-items:flex-start;max-width:80%">
      <div class="bubble bot-bubble ${wide ? 'wide' : ''}">
        ${marked.parse(String(markdown))}
      </div>
      ${ragBadge}
      <div class="msg-time">${ts()}</div>
    </div>`;

  box.appendChild(row);
  scrollBottom();
}

function addMovieCards(movies) {
  if (!movies?.length) return;
  const box = document.getElementById('chatBox');
  const row = document.createElement('div');
  row.className = 'msg-row';
  row.innerHTML = `
    <div class="avatar">🎬</div>
    <div class="msg-group" style="max-width:90%">
      <div class="bubble bot-bubble wide">
        <div class="movies-grid" id="mgrid-${Date.now()}"></div>
      </div>
      <div class="msg-time">${ts()}</div>
    </div>`;
  box.appendChild(row);
  const grid = row.querySelector('.movies-grid');
  movies.slice(0, 8).forEach(m => {
    const card = document.createElement('div');
    card.className = 'movie-card';
    card.title = m.title || '';
    card.innerHTML = `
      <img class="movie-poster"
           src="${posterSrc(m.poster_path)}"
           alt="${escapeHtml(m.title || '')}"
           loading="lazy"
           onerror="this.src='https://placehold.co/120x175/13151f/5a5f7a?text=?'">
      <div class="movie-info">
        <div class="movie-title">${escapeHtml(m.title || 'Unknown')}</div>
        <div class="movie-meta">
          <span class="movie-star">★ ${(m.rating ?? m.vote_average ?? 0).toFixed(1)}</span>
          <span>${m.year || (m.release_date||'').slice(0,4) || ''}</span>
        </div>
      </div>`;
    card.addEventListener('click', () => sendSuggestion(`Tell me about "${m.title}"`));

    if (m.id) {
      const fb = document.createElement('div');
      fb.className = 'movie-feedback';
      fb.innerHTML = `
        <div class="fb-row">
          <button class="fb-btn fb-like" title="Like">👍</button>
          <button class="fb-btn fb-dislike" title="Dislike">👎</button>
        </div>
        <div class="fb-row fb-stars">
          ${[1,2,3,4,5].map(v => `<span data-v="${v}">★</span>`).join('')}
        </div>
        <div class="fb-saved-toast">Saved ✓</div>`;

      fb.querySelector('.fb-like').addEventListener('click', e => {
        e.stopPropagation();
        sendMovieFeedback(m.id, m.title, m.poster_path, 'like', null, fb);
      });
      fb.querySelector('.fb-dislike').addEventListener('click', e => {
        e.stopPropagation();
        sendMovieFeedback(m.id, m.title, m.poster_path, 'dislike', null, fb);
      });
      fb.querySelectorAll('.fb-stars span').forEach(star => {
        star.addEventListener('click', e => {
          e.stopPropagation();
          sendMovieFeedback(m.id, m.title, m.poster_path, 'rate', +star.dataset.v, fb);
        });
      });

      card.appendChild(fb);
    }

    grid.appendChild(card);
  });
  scrollBottom();
}

// ══════════════════════════════════════════════════════
// FEEDBACK — 👍 👎 ⭐ sur une carte film
// ══════════════════════════════════════════════════════
async function sendMovieFeedback(tmdbId, title, posterPath, action, rating, fbEl) {
  try {
    const res = await fetch('{{ url("/api/movies/feedback") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF,
      },
      body: JSON.stringify({
        tmdb_id: tmdbId,
        title: title,
        poster_path: posterPath,
        action: action,
        rating: rating,
      }),
    });

    if (res.status === 401) {
      alert('Connectez-vous pour donner votre avis sur ce film.');
      return;
    }
    if (!res.ok) throw new Error('feedback request failed');

    // Met à jour visuellement le bouton cliqué
    const likeBtn    = fbEl.querySelector('.fb-like');
    const dislikeBtn = fbEl.querySelector('.fb-dislike');
    const stars      = fbEl.querySelectorAll('.fb-stars span');

    if (action === 'like') {
      likeBtn.classList.add('active-like');
      dislikeBtn.classList.remove('active-dislike');
    } else if (action === 'dislike') {
      dislikeBtn.classList.add('active-dislike');
      likeBtn.classList.remove('active-like');
    } else if (action === 'rate') {
      stars.forEach(s => s.classList.toggle('filled', +s.dataset.v <= rating));
    }

    const toast = fbEl.querySelector('.fb-saved-toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 1500);

  } catch (e) {
    console.error('[Feedback]', e);
  }
}

function addError(msg) {
  const box = document.getElementById('chatBox');
  const row = document.createElement('div');
  row.className = 'error-row';
  row.innerHTML = `
    <div class="avatar">🎬</div>
    <div class="error-bubble">
      <i class="bi bi-exclamation-triangle-fill"></i> ${msg}
    </div>`;
  box.appendChild(row);
  scrollBottom();
}

let _loadingEl = null;
function showLoading() {
  const box = document.getElementById('chatBox');
  const row = document.createElement('div');
  row.className = 'loading-row'; row.id = 'loadingRow';
  row.innerHTML = `
    <div class="avatar">🎬</div>
    <div class="loading-bubble">
      <div class="dots"><i></i><i></i><i></i></div>
      <span>Searching movies…</span>
    </div>`;
  box.appendChild(row);
  scrollBottom();
  _loadingEl = row;
}
function hideLoading() {
  _loadingEl?.remove();
  _loadingEl = null;
}

// ══════════════════════════════════════════════════════
// SEND — with anaphora resolution
// ══════════════════════════════════════════════════════
async function sendMessage() {
  const input = document.getElementById('chatInput');
  const btn   = document.getElementById('sendBtn');
  const rawText = input.value.trim();
  if (!rawText || isLoading) return;

  // ── Resolve anaphora before sending ──────────────────────────────────────
  const text = resolveAnaphora(rawText);
  if (text !== rawText) {
    console.log(`[Anaphora] "${rawText}" → "${text}"`);
  }

  isLoading = true;
  btn.disabled = true;

  addUserMessage(rawText);   // show original text to user
  input.value = '';
  input.style.height = 'auto';
  showLoading();

  try {
    const res = await fetch(API_CHAT, {
      method:  'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'Accept':       'application/json',
      },
      body: JSON.stringify({ message: text, session_id: currentSessionId }),   // send resolved text
    });

    hideLoading();

    let data = null;
    try { data = await res.json(); } catch {}

    if (!res.ok) {
      const errMsg = data?.message ?? data?.reply ?? `Server error (${res.status})`;
      if (res.status === 419) addError('Session expired — please refresh the page.');
      else if (res.status === 401) addError('Please log in to use CineBot.');
      else if (res.status === 429) addError('⏳ Too many messages sent too quickly — please wait a moment before trying again.');
      else if (res.status === 503) addError('AI service unavailable. Make sure the Python server is running on port 8001.');
      else addError(errMsg);
      return;
    }

    const reply  = data?.reply ?? 'No response received.';
    const movies = data?.recommended_movies ?? [];

    // ── Cette discussion a maintenant un session_id (nouveau ou existant) ──
    if (data?.session_id) {
      currentSessionId = data.session_id;
    }

    // ── Update session context for future anaphora resolution ─────────────
    //
    // IMPORTANT: sessionRecommendations must reflect the NUMBERED LIST ORDER
    // in the bot's reply text, NOT the order of the movie cards array.
    // Cards come from RAG/TMDB retrieval and have a different order.
    // "the first one" means #1 in the reply list, so we parse the reply.
    const replyListTitles = extractNumberedListFromReply(reply);
    if (replyListTitles.length > 0) {
      // Build sessionRecommendations from the reply's numbered list order.
      // Cross-reference cards array for metadata (year, id, poster).
      sessionRecommendations = replyListTitles.map((title, i) => {
        const card = movies.find(m =>
          (m.title || '').toLowerCase() === title.toLowerCase()
        );
        return {
          title,
          year:     card ? (card.year || (card.release_date || '').slice(0, 4) || '') : '',
          id:       card ? card.id : null,
          position: i + 1,
        };
      });
      console.log('[Anaphora] sessionRecommendations from reply list:',
        sessionRecommendations.map(r => `${r.position}. ${r.title}`));
    } else if (movies.length > 0) {
      // Fallback: no numbered list in reply (e.g. single-movie answer) —
      // use cards order only as a last resort
      sessionRecommendations = movies.map((m, i) => ({
        title:    m.title || '',
        year:     m.year || (m.release_date || '').slice(0, 4) || '',
        id:       m.id,
        position: i + 1,
      }));
    }

    // Track movie titles from reply — [0] = most-discussed (for pronoun resolution)
    const mentionedInReply = extractMoviesFromReply(reply);
    if (mentionedInReply.length > 0) {
      lastBotMoviesMentioned = mentionedInReply;
      console.log('[Anaphora] Last discussed:', lastBotMoviesMentioned[0]);
    }

    // RAG badge: show how many movie cards came from the RAG index
    const ragSourceCount = movies.filter(m => m.id).length;

    addBotMessage(reply, false, ragSourceCount);
    if (movies.length > 0) {
      renderSidebar(movies);   // sidebar only — no inline cards in chat
    }

    // Rafraîchit la liste des discussions (titre affiché = 1er message envoyé)
    loadSessions();

  } catch (err) {
    hideLoading();
    addError('Connection error — is the server running?');
    console.error(err);
  } finally {
    isLoading = false;
    btn.disabled = false;
    input.focus();
  }
}

function sendSuggestion(text) {
  document.getElementById('chatInput').value = text;
  sendMessage();
}

// ══════════════════════════════════════════════════════
// RAG DEBUG BUTTON — quick health check
// ══════════════════════════════════════════════════════
document.getElementById('ragDebugBtn').addEventListener('click', async () => {
  addBotMessage('🔍 Testing RAG connection…');
  try {
    const r = await fetch('{{ url("/api/chat") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ message: 'test: list 3 movies from your RAG index' }),
    });
    const d = await r.json();
    const movies = d?.recommended_movies ?? [];
    const reply  = d?.reply ?? 'No reply';

    if (movies.length > 0) {
      addBotMessage(
        `✅ **RAG is working!**\n\n` +
        `**${movies.length} movies retrieved** from the FAISS index.\n\n` +
        `First result: **${movies[0].title}** (${movies[0].year || '?'}) — ★ ${(movies[0].rating||0).toFixed(1)}\n\n` +
        `_The reply was: "${reply.slice(0, 120)}…"_`,
        false, movies.length
      );
      addMovieCards(movies);
    } else {
      addBotMessage(
        `⚠️ **RAG returned 0 movies.**\n\n` +
        `The FastAPI server responded but the FAISS index may still be building, or the query matched nothing.\n\n` +
        `Check your Python console for \`[RAG]\` log lines.\n\n` +
        `Reply from AI: _"${reply.slice(0, 200)}"_`
      );
    }
  } catch (e) {
    addError('RAG test failed — is the FastAPI server running on port 8001?');
  }
});

// ══════════════════════════════════════════════════════
// KEYBOARD
// ══════════════════════════════════════════════════════
chatInput.addEventListener('keydown', e => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendMessage();
  }
});

// ══════════════════════════════════════════════════════
// CLEAR
// ══════════════════════════════════════════════════════
document.getElementById('clearBtn').addEventListener('click', () => {
  // Reset session context too
  sessionRecommendations = [];
  lastBotMoviesMentioned  = [];

  const box = document.getElementById('chatBox');
  box.innerHTML = `
    <div class="welcome" id="welcomeState">
      <div class="welcome-icon">🍿</div>
      <h2>New conversation</h2>
      <p>Ask me anything about cinema.</p>
    </div>`;
});

// ══════════════════════════════════════════════════════
// SIDEBAR
// ══════════════════════════════════════════════════════
function renderSidebar(movies) {
  const el = document.getElementById('sidebarContent');
  if (!movies?.length) return;
  el.innerHTML = '';
  movies.slice(0, 15).forEach(m => {
    const item = document.createElement('div');
    item.className = 'rec-item';
    item.title = m.title || '';
    item.innerHTML = `
      <img class="rec-poster"
           src="${posterSrc(m.poster_path)}"
           alt="${escapeHtml(m.title||'')}"
           loading="lazy"
           onerror="this.src='https://placehold.co/44x64/13151f/5a5f7a?text=?'">
      <div class="rec-info">
        <div class="rec-title">${escapeHtml(m.title||'Unknown')}</div>
        <div class="rec-meta">
          <span class="star-rating">★ ${(m.rating??m.vote_average??0).toFixed(1)}</span>
          <span>${m.year||(m.release_date||'').slice(0,4)||''}</span>
          ${m.match_percent != null ? `<span class="match-badge" title="Hybrid ranking engine score (relevance + rating + popularity + your preferences)">🎯 ${m.match_percent}%</span>` : ''}
        </div>
        ${m.id ? `
        <div class="rec-feedback">
          <button class="fb-btn fb-like" title="Like">👍</button>
          <button class="fb-btn fb-dislike" title="Dislike">👎</button>
          <div class="fb-stars">
            ${[1,2,3,4,5].map(v => `<span data-v="${v}">★</span>`).join('')}
          </div>
        </div>` : ''}
      </div>`;
    item.addEventListener('click', () => sendSuggestion(`Tell me about "${m.title}"`));

    if (m.id) {
      const fb = item.querySelector('.rec-feedback');
      fb.querySelector('.fb-like').addEventListener('click', e => {
        e.stopPropagation();
        sendMovieFeedback(m.id, m.title, m.poster_path, 'like', null, fb);
      });
      fb.querySelector('.fb-dislike').addEventListener('click', e => {
        e.stopPropagation();
        sendMovieFeedback(m.id, m.title, m.poster_path, 'dislike', null, fb);
      });
      fb.querySelectorAll('.fb-stars span').forEach(star => {
        star.addEventListener('click', e => {
          e.stopPropagation();
          sendMovieFeedback(m.id, m.title, m.poster_path, 'rate', +star.dataset.v, fb);
        });
      });
    }

    el.appendChild(item);
  });
}

async function loadSidebar() {
  try {
    const r = await fetch(API_RECS + '/guest', { headers: { Accept: 'application/json' } });
    if (!r.ok) return;
    const d = await r.json();
    renderSidebar(d.movies ?? d.recommended_movies ?? []);
  } catch { /* silent */ }
}

// ══════════════════════════════════════════════════════
// HISTORIQUE DES DISCUSSIONS (sidebar droite — style Claude/ChatGPT)
// ══════════════════════════════════════════════════════
function fmtRelativeDate(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  const now = new Date();
  const sameDay = d.toDateString() === now.toDateString();
  if (sameDay) return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  return d.toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
}

async function loadSessions() {
  const list = document.getElementById('historyList');
  try {
    const res = await fetch('{{ url("/api/chat/sessions") }}', { headers: { Accept: 'application/json' } });
    if (!res.ok) return;
    const sessions = await res.json();

    if (!sessions?.length) {
      list.innerHTML = '<div class="history-empty">No conversations yet.<br>Click + to start.</div>';
      return;
    }

    list.innerHTML = '';
    sessions.forEach(s => {
      const item = document.createElement('div');
      item.className = 'history-item' + (s.session_id === currentSessionId ? ' active' : '');
      item.innerHTML = `
        <div class="history-item-title">${escapeHtml(s.title || 'New chat')}</div>
        <div class="history-item-date">${fmtRelativeDate(s.last_at)}</div>
        <button class="history-item-delete" title="Delete"><i class="bi bi-trash3"></i></button>`;

      item.addEventListener('click', () => openSession(s.session_id));
      item.querySelector('.history-item-delete').addEventListener('click', (e) => {
        e.stopPropagation();
        deleteSessionConfirm(s.session_id);
      });

      list.appendChild(item);
    });
  } catch (e) {
    console.error('[Sessions]', e);
  }
}

async function openSession(sessionId) {
  if (sessionId === currentSessionId) return;
  try {
    const res = await fetch(`{{ url('/api/chat/sessions') }}/${sessionId}`, { headers: { Accept: 'application/json' } });
    if (!res.ok) return;
    const messages = await res.json();

    currentSessionId = sessionId;

    const box = document.getElementById('chatBox');
    box.innerHTML = '';

    let lastMovies = [];
    messages.forEach(m => {
      if (m.role === 'user') {
        addUserMessage(m.content);
      } else {
        let cards = [];
        try { cards = JSON.parse(m.movie_cards || '[]'); } catch { cards = []; }
        if (cards?.length) lastMovies = cards;
        addBotMessage(m.content, false, cards.filter(c => c.id).length);
      }
    });

    if (lastMovies.length) renderSidebar(lastMovies);
    scrollBottom();
    loadSessions(); // met à jour la surbrillance "active"
  } catch (e) {
    console.error('[OpenSession]', e);
  }
}

function newConversation() {
  currentSessionId = null;
  sessionRecommendations = [];
  lastBotMoviesMentioned = [];

  const box = document.getElementById('chatBox');
  box.innerHTML = `
    <div class="welcome" id="welcomeState">
      <div class="welcome-icon">🍿</div>
      <h2>What are you watching tonight?</h2>
      <p>Ask me anything about movies — plots, cast, recommendations, trailers, genres, directors. Our AI searches a real movie database for you.</p>
      <div class="chips">
        <span class="chip" onclick="sendSuggestion('Recommend me great sci-fi films')">🚀 Best sci-fi</span>
        <span class="chip" onclick="sendSuggestion('Tell me about The Shawshank Redemption')">🎩 Shawshank Redemption</span>
        <span class="chip" onclick="sendSuggestion('Recommend horror movies like Get Out')">👻 Like Get Out</span>
        <span class="chip" onclick="sendSuggestion('Best Christopher Nolan films ranked')">🎬 Nolan films</span>
        <span class="chip" onclick="sendSuggestion('Sad anime movies that will make me cry')">😢 Sad anime</span>
        <span class="chip" onclick="sendSuggestion('Action movies from the 2000s')">💥 2000s action</span>
        <span class="chip" onclick="sendSuggestion('Explain what makes a good cinematography')">🎥 Cinematography</span>
        <span class="chip" onclick="sendSuggestion('Movies with twist endings')">🌀 Twist endings</span>
      </div>
    </div>`;

  loadSessions();
  document.getElementById('chatInput')?.focus();
}

function deleteSessionConfirm(sessionId) {
  if (!confirm('Permanently delete this conversation?')) return;
  deleteSession(sessionId);
}

async function deleteSession(sessionId) {
  try {
    const res = await fetch(`{{ url('/api/chat/sessions') }}/${sessionId}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    });
    if (!res.ok) return;

    if (sessionId === currentSessionId) {
      newConversation();
    } else {
      loadSessions();
    }
  } catch (e) {
    console.error('[DeleteSession]', e);
  }
}

document.getElementById('newChatBtn').addEventListener('click', newConversation);

// ══════════════════════════════════════════════════════
// INIT
// ══════════════════════════════════════════════════════
loadSidebar();
loadSessions();
</script>
</body>
</html>
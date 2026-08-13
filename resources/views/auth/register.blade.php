<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Sign up — CineBot AI</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root,
[data-theme="dark"]{
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
  --radius:       14px;
  --radius-sm:    8px;
  --ease:         cubic-bezier(.4,0,.2,1);
  --shadow-card:  0 8px 32px rgba(0,0,0,.6);
}
[data-theme="light"]{
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
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}

/* ═══════════════════════════════════════════════════════════
   SPLIT LAYOUT
═══════════════════════════════════════════════════════════ */
.split{display:flex;min-height:100vh}

/* ── Left: brand panel ── */
.brand-panel{
  flex:1.15;position:relative;overflow:hidden;
  display:flex;flex-direction:column;justify-content:space-between;
  padding:48px 56px;color:#f4f4f8;
  background:
    radial-gradient(ellipse 60% 50% at 15% 15%, rgba(229,9,20,.35) 0%, transparent 60%),
    radial-gradient(ellipse 50% 45% at 85% 85%, rgba(245,184,0,.18) 0%, transparent 60%),
    linear-gradient(155deg, #0c0004 0%, #2a040c 42%, #0a0a10 100%);
}
.brand-panel::before{
  content:'';position:absolute;inset:0;pointer-events:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='.05'/%3E%3C/svg%3E");
}
.orb{position:absolute;border-radius:50%;filter:blur(70px);pointer-events:none;opacity:.55}
.orb-1{width:340px;height:340px;background:var(--gold);top:-80px;right:-100px;animation:float1 14s ease-in-out infinite alternate}
.orb-2{width:280px;height:280px;background:var(--accent);bottom:-90px;left:-60px;animation:float2 17s ease-in-out infinite alternate}
.orb-3{width:200px;height:200px;background:#7a1230;top:40%;left:8%;animation:float1 11s ease-in-out infinite alternate-reverse}
@keyframes float1{from{transform:translate(0,0)}to{transform:translate(30px,40px)}}
@keyframes float2{from{transform:translate(0,0)}to{transform:translate(-25px,-30px)}}

.brand-panel-top{position:relative;z-index:1;display:flex;align-items:center;gap:10px}
.brand-panel-icon{
  width:38px;height:38px;background:var(--accent);border-radius:8px;
  display:grid;place-items:center;font-size:1.1rem;box-shadow:0 0 18px rgba(229,9,20,.5);
}
.brand-panel-name{font-family:'Bebas Neue',sans-serif;font-size:1.7rem;letter-spacing:.06em;color:#fff}

.brand-panel-mid{position:relative;z-index:1;max-width:440px}
.brand-panel-mid h2{
  font-family:'Bebas Neue',sans-serif;font-size:2.6rem;line-height:1.1;
  letter-spacing:.01em;margin-bottom:16px;color:#fff;
}
.brand-panel-mid h2 span{color:var(--gold)}
.brand-panel-mid p{font-size:.95rem;line-height:1.6;color:rgba(244,244,248,.72);margin-bottom:32px}

.feature-list{display:flex;flex-direction:column;gap:16px}
.feature-item{display:flex;align-items:flex-start;gap:12px}
.feature-icon{
  width:32px;height:32px;flex-shrink:0;border-radius:8px;
  background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);
  display:grid;place-items:center;font-size:.9rem;color:var(--gold);
  backdrop-filter:blur(4px);
}
.feature-text{font-size:.85rem;color:rgba(244,244,248,.85);line-height:1.4;padding-top:4px}
.feature-text strong{color:#fff;display:block;font-size:.87rem;margin-bottom:1px}

.brand-panel-bottom{position:relative;z-index:1;font-size:.72rem;color:rgba(244,244,248,.4)}

@media (max-width:900px){
  .brand-panel{display:none}
}

/* ── Right: form panel ── */
.form-panel{
  flex:1;display:flex;align-items:center;justify-content:center;
  padding:40px 24px;position:relative;
  min-height:100vh;
}
[data-theme="dark"] .form-panel{
  background-image:radial-gradient(ellipse 70% 50% at 50% -10%, rgba(229,9,20,.06) 0%, transparent 60%);
}

/* ── Theme toggle pill ── */
.theme-toggle-wrap{position:fixed;top:20px;right:20px;z-index:10}
.theme-toggle{
  position:relative;width:60px;height:30px;border-radius:99px;
  background:var(--surface-3);border:1px solid var(--border);
  cursor:pointer;transition:.25s var(--ease);display:flex;align-items:center;padding:3px;
}
.theme-toggle:hover{border-color:var(--accent)}
.toggle-thumb{
  width:24px;height:24px;border-radius:50%;background:var(--accent);
  display:grid;place-items:center;font-size:.75rem;color:#fff;
  transition:transform .25s var(--ease), background .25s;transform:translateX(0);
}
[data-theme="light"] .toggle-thumb{transform:translateX(30px);background:#f5b800}
.toggle-icons{position:absolute;width:100%;display:flex;justify-content:space-between;padding:0 7px;pointer-events:none;font-size:.7rem}

.card{
  width:100%;max-width:400px;
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:var(--shadow-card);
  padding:36px 32px;
  animation:fadeUp .5s var(--ease);
}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

.brand{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:28px}
.brand-icon{
  width:38px;height:38px;background:var(--accent);border-radius:8px;
  display:grid;place-items:center;font-size:1.1rem;box-shadow:0 0 18px var(--accent-glow);
}
.brand-text{font-family:'Bebas Neue',sans-serif;font-size:1.9rem;letter-spacing:.06em}

h1{
  font-size:1.05rem;font-weight:600;text-align:center;
  margin-bottom:6px;color:var(--text);
}
.subtitle{
  text-align:center;color:var(--muted);font-size:.85rem;margin-bottom:26px;
}

.alert{
  padding:10px 14px;border-radius:var(--radius-sm);font-size:.82rem;margin-bottom:18px;
  border:1px solid; display:flex; gap:8px; align-items:flex-start;
}
.alert-error{background:var(--accent-dim);border-color:rgba(229,9,20,.4);color:#ff6b6f}
.alert ul{margin:0;padding-left:16px}

.field{margin-bottom:18px}
label{display:block;font-size:.78rem;color:var(--muted);margin-bottom:6px;letter-spacing:.02em}
.input-wrap{position:relative}
.input-wrap i{
  position:absolute;left:14px;top:50%;transform:translateY(-50%);
  color:var(--muted);font-size:.95rem;
}
input[type=text],input[type=email],input[type=password]{
  width:100%;
  padding:12px 14px 12px 40px;
  background:var(--surface-2);
  border:1px solid var(--border);
  border-radius:var(--radius-sm);
  color:var(--text);
  font-family:inherit;
  font-size:.9rem;
  transition:border-color .2s var(--ease), box-shadow .2s var(--ease);
}
input:focus{
  outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim);
}
input.invalid{border-color:#ff6b6f}
.field-error{color:#ff6b6f;font-size:.75rem;margin-top:5px}
.hint{color:var(--muted);font-size:.72rem;margin-top:5px}

.btn-primary{
  width:100%;
  padding:13px;
  background:var(--accent);
  color:#fff;
  border:none;
  border-radius:var(--radius-sm);
  font-family:inherit;font-size:.92rem;font-weight:600;letter-spacing:.02em;
  cursor:pointer;
  box-shadow:0 4px 20px var(--accent-glow);
  transition:transform .15s var(--ease), box-shadow .15s var(--ease);
}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 26px var(--accent-glow)}
.btn-primary:active{transform:translateY(0)}

.footer-link{text-align:center;margin-top:22px;font-size:.85rem;color:var(--muted)}

.app-credit{
  text-align:center;margin-top:18px;font-size:.72rem;color:var(--muted);
  display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;
}
.app-credit a{color:var(--muted);transition:color .15s var(--ease)}
.app-credit a:hover{color:var(--accent)}
.app-credit .dot{opacity:.4}

.divider{display:flex;align-items:center;gap:12px;margin:24px 0;color:var(--muted);font-size:.75rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border)}
</style>
</head>
<body>

<div class="theme-toggle-wrap">
  <div class="theme-toggle" id="themeToggle" title="Toggle light/dark theme">
    <div class="toggle-icons">
      <span>🌙</span>
      <span>☀️</span>
    </div>
    <div class="toggle-thumb" id="toggleThumb">
      <i class="bi bi-moon-stars-fill" id="toggleIcon"></i>
    </div>
  </div>
</div>

<div class="split">

  <!-- ── Brand panel (hidden on mobile) ── -->
  <aside class="brand-panel">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="brand-panel-top">
      <div class="brand-panel-icon">🎬</div>
      <div class="brand-panel-name">CineBot AI</div>
    </div>

    <div class="brand-panel-mid">
      <h2>Join and get<br>recommendations<br>made for <span>you</span>.</h2>
      <p>Create a free account and CineBot starts learning your taste from your very
      first like — no generic top-10 lists, just films picked for you.</p>

      <div class="feature-list">
        <div class="feature-item">
          <div class="feature-icon"><i class="bi bi-chat-dots"></i></div>
          <div class="feature-text">
            <strong>Talk, don't search</strong>
            Ask in plain English — genres, moods, directors, "something like Interstellar".
          </div>
        </div>
        <div class="feature-item">
          <div class="feature-icon"><i class="bi bi-hand-thumbs-up"></i></div>
          <div class="feature-text">
            <strong>Like, dislike, rate</strong>
            Every reaction refines your profile in real time.
          </div>
        </div>
        <div class="feature-item">
          <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
          <div class="feature-text">
            <strong>Your data, your control</strong>
            Delete your account and everything with it, anytime.
          </div>
        </div>
      </div>
    </div>

    <div class="brand-panel-bottom">© 2026 CineBot AI </div>
  </aside>

  <!-- ── Form panel ── -->
  <div class="form-panel">
    <div class="card">
      <div class="brand">
        <div class="brand-icon">🎬</div>
        <div class="brand-text">CineBot AI</div>
      </div>

      <h1>Create your account</h1>
      <p class="subtitle">Recommendations that learn your taste</p>

      @if ($errors->any())
        <div class="alert alert-error">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="field">
          <label for="name">Full name</label>
          <div class="input-wrap">
            <i class="bi bi-person"></i>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   class="{{ $errors->has('name') ? 'invalid' : '' }}"
                   placeholder="Your name" required autofocus>
          </div>
          @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
          <label for="email">Email address</label>
          <div class="input-wrap">
            <i class="bi bi-envelope"></i>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="{{ $errors->has('email') ? 'invalid' : '' }}"
                   placeholder="you@example.com" required>
          </div>
          @error('email') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <i class="bi bi-lock"></i>
            <input type="password" id="password" name="password"
                   class="{{ $errors->has('password') ? 'invalid' : '' }}"
                   placeholder="8 characters minimum" required>
          </div>
          @error('password') <div class="field-error">{{ $message }}</div> @enderror
          <div class="hint">Minimum 8 characters.</div>
        </div>

        <div class="field">
          <label for="password_confirmation">Confirm password</label>
          <div class="input-wrap">
            <i class="bi bi-lock-fill"></i>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn-primary">
          <i class="bi bi-person-plus"></i> Create my account
        </button>
      </form>

      <div class="divider">OR</div>

      <p class="footer-link">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
      </p>
    </div>

    <div class="app-credit" style="position:absolute;bottom:20px;left:0;right:0">
      <span>CineBot AI © 2026 — Ons Ajmi</span>
      <span class="dot">·</span>
      <a href="https://github.com/AjmiOns" target="_blank" rel="noopener"><i class="bi bi-github"></i> GitHub</a>
      <span class="dot">·</span>
      <a href="https://www.linkedin.com/in/ons-ajmi-0ab2982a2/" target="_blank" rel="noopener"><i class="bi bi-linkedin"></i> LinkedIn</a>
    </div>
  </div>
</div>

<script>
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

applyTheme(localStorage.getItem('cinebot_theme') || 'dark');

themeToggle.addEventListener('click', () => {
  const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  applyTheme(next);
});
</script>

</body>
</html>

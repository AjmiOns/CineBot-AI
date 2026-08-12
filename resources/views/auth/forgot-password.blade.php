<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Forgot password — CineBot AI</title>

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
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  min-height:100vh;
  padding:20px;
  transition:background .3s var(--ease), color .3s var(--ease);
}
[data-theme="dark"] body{
  background-image:
    radial-gradient(ellipse 80% 60% at 50% -10%, rgba(229,9,20,.12) 0%, transparent 60%),
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='.025'/%3E%3C/svg%3E");
}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}

.theme-toggle-wrap{position:fixed;top:20px;right:20px;z-index:10}
.theme-toggle{
  position:relative;width:60px;height:30px;border-radius:99px;
  background:var(--surface-3);border:1px solid var(--border);
  cursor:pointer;display:flex;align-items:center;padding:3px;transition:.25s var(--ease);
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
  width:100%;max-width:420px;
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

h1{font-size:1.05rem;font-weight:600;text-align:center;margin-bottom:6px;color:var(--text)}
.subtitle{text-align:center;color:var(--muted);font-size:.85rem;margin-bottom:26px;line-height:1.5}

.alert{
  padding:10px 14px;border-radius:var(--radius-sm);font-size:.82rem;margin-bottom:18px;
  border:1px solid; display:flex; gap:8px; align-items:flex-start;
}
.alert-success{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.35);color:#4ade80}
.alert-error{background:var(--accent-dim);border-color:rgba(229,9,20,.4);color:#ff6b6f}
.alert ul{margin:0;padding-left:16px}

.field{margin-bottom:20px}
label{display:block;font-size:.78rem;color:var(--muted);margin-bottom:6px;letter-spacing:.02em}
.input-wrap{position:relative}
.input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.95rem}
input[type=email]{
  width:100%;padding:12px 14px 12px 40px;
  background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);
  color:var(--text);font-family:inherit;font-size:.9rem;
  transition:border-color .2s var(--ease), box-shadow .2s var(--ease);
}
input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim)}
input.invalid{border-color:#ff6b6f}
.field-error{color:#ff6b6f;font-size:.75rem;margin-top:5px}

.btn-primary{
  width:100%;padding:13px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);
  font-family:inherit;font-size:.92rem;font-weight:600;letter-spacing:.02em;cursor:pointer;
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
</style>
</head>
<body>

<div class="theme-toggle-wrap">
  <div class="theme-toggle" id="themeToggle" title="Toggle light/dark theme">
    <div class="toggle-icons"><span>🌙</span><span>☀️</span></div>
    <div class="toggle-thumb" id="toggleThumb"><i class="bi bi-moon-stars-fill" id="toggleIcon"></i></div>
  </div>
</div>

<div class="card">
  <div class="brand">
    <div class="brand-icon">🎬</div>
    <div class="brand-text">CineBot AI</div>
  </div>

  <h1>Forgot your password?</h1>
  <p class="subtitle">Enter your email address — if an account exists, you will receive a link to reset your password.</p>

  @if (session('success'))
    <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i><span>{{ session('success') }}</span></div>
  @endif

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

  <form method="POST" action="{{ route('password.email') }}" novalidate>
    @csrf
    <div class="field">
      <label for="email">Email address</label>
      <div class="input-wrap">
        <i class="bi bi-envelope"></i>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
               class="{{ $errors->has('email') ? 'invalid' : '' }}"
               placeholder="you@example.com" required autofocus>
      </div>
      @error('email') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn-primary">
      <i class="bi bi-send"></i> Send reset link
    </button>
  </form>

  <p class="footer-link">
    <a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Back to login</a>
  </p>
</div>

<div class="app-credit">
  <span>CineBot AI © 2026 — Ons Ajmi</span>
  <span class="dot">·</span>
  <a href="https://github.com/AjmiOns" target="_blank" rel="noopener"><i class="bi bi-github"></i> GitHub</a>
  <span class="dot">·</span>
  <a href="https://www.linkedin.com/in/ons-ajmi-0ab2982a2/" target="_blank" rel="noopener"><i class="bi bi-linkedin"></i> LinkedIn</a>
</div>

<script>
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
</script>

</body>
</html>

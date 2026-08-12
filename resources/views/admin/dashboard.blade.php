<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Admin Dashboard — CineBot AI</title>


<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

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
  --green:        #4ade80;
  --green-dim:    rgba(74,222,128,.12);
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
  --green:        #16a34a;
  --green-dim:    rgba(22,163,74,.1);
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
::-webkit-scrollbar{width:4px}
::-webkit-scrollbar-thumb{background:var(--border-glow);border-radius:99px}

.navbar{
  height:58px;background:rgba(8,10,16,.9);backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  padding:0 20px;position:sticky;top:0;z-index:10;
}
[data-theme="light"] .navbar{background:rgba(255,255,255,.92)}
.brand{display:flex;align-items:center;gap:10px;font-family:'Bebas Neue',sans-serif;font-size:1.6rem;letter-spacing:.06em}
.brand-icon{width:32px;height:32px;background:var(--accent);border-radius:6px;display:grid;place-items:center;font-size:1rem;box-shadow:0 0 18px var(--accent-glow)}
.brand-sub{font-family:'DM Sans',sans-serif;font-size:.65rem;font-weight:300;color:var(--muted);letter-spacing:.1em;text-transform:uppercase;display:block;margin-top:-4px}
.nav-right{display:flex;gap:8px;align-items:center}
.btn-icon{width:34px;height:34px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface-2);color:var(--muted);display:grid;place-items:center;cursor:pointer;font-size:.95rem;transition:.2s var(--ease)}
.btn-icon:hover{color:var(--text);border-color:var(--border-glow)}
.theme-toggle{position:relative;width:60px;height:30px;border-radius:99px;background:var(--surface-3);border:1px solid var(--border);cursor:pointer;display:flex;align-items:center;padding:3px;transition:.25s var(--ease)}
.theme-toggle:hover{border-color:var(--accent)}
.toggle-thumb{width:24px;height:24px;border-radius:50%;background:var(--accent);display:grid;place-items:center;font-size:.75rem;color:#fff;transition:transform .25s var(--ease), background .25s;transform:translateX(0)}
[data-theme="light"] .toggle-thumb{transform:translateX(30px);background:#f5b800}
.toggle-icons{position:absolute;width:100%;display:flex;justify-content:space-between;padding:0 7px;pointer-events:none;font-size:.7rem}

.page{max-width:1200px;margin:0 auto;padding:32px 20px 60px}
.page-header{margin-bottom:26px}
.page-header-row{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap}
.page-header h1{font-family:'Bebas Neue',sans-serif;font-size:2rem;letter-spacing:.03em}
.page-header p{color:var(--muted);font-size:.88rem;margin-top:2px}

.btn-export{
  display:flex;align-items:center;gap:8px;
  padding:10px 18px;border-radius:var(--radius-sm);
  background:var(--accent);color:#fff;
  font-size:.85rem;font-weight:600;
  box-shadow:0 4px 16px var(--accent-glow);
  transition:transform .15s var(--ease), box-shadow .15s var(--ease);
  white-space:nowrap;text-decoration:none;
}
.btn-export:hover{transform:translateY(-1px);box-shadow:0 6px 20px var(--accent-glow);text-decoration:none}

/* Stat cards */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:28px}
.stat-card{
  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
  padding:18px;
}
.stat-card .icon{
  width:34px;height:34px;border-radius:9px;display:grid;place-items:center;
  margin-bottom:10px;font-size:1rem;
}
.stat-card .value{font-family:'Bebas Neue',sans-serif;font-size:1.9rem;letter-spacing:.02em}
.stat-card .label{font-size:.75rem;color:var(--muted);margin-top:2px}

.icon-accent{background:var(--accent-dim);color:var(--accent)}
.icon-gold{background:rgba(245,184,0,.12);color:var(--gold)}
.icon-green{background:var(--green-dim);color:var(--green)}

section{margin-bottom:32px}
.section-title{
  font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);
  margin-bottom:14px;display:flex;align-items:center;gap:6px;
}

.grid-2{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:20px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px}

.bar-item{margin-bottom:12px}
.bar-item:last-child{margin-bottom:0}
.bar-top{display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:5px}
.bar-track{height:7px;border-radius:99px;background:var(--surface-3);overflow:hidden}
.bar-fill{height:100%;border-radius:99px;background:var(--accent);transition:width .5s var(--ease)}

.movie-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:9px 0;border-bottom:1px solid var(--border);font-size:.85rem;
}
.movie-row:last-child{border-bottom:none}
.movie-rank{
  width:22px;height:22px;border-radius:50%;background:var(--surface-3);
  display:grid;place-items:center;font-size:.7rem;font-weight:700;color:var(--muted);
  margin-right:10px;flex-shrink:0;
}
.movie-row-title{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.movie-row-meta{color:var(--muted);font-size:.78rem;display:flex;gap:10px;flex-shrink:0}
.tile-liked{color:var(--green)}

/* Charts (Chart.js canvases) */
.chart-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px}
.chart-canvas-wrap{position:relative;height:260px}
.chart-canvas-wrap.small{height:220px}
.chart-legend-row{display:flex;justify-content:center;gap:18px;margin-top:14px;flex-wrap:wrap}
.chart-legend-item{display:flex;align-items:center;gap:6px;font-size:.78rem;color:var(--muted)}
.chart-legend-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0}

.grid-3{display:grid;grid-template-columns:2fr 1fr;gap:20px}
@media (max-width:900px){.grid-3{grid-template-columns:1fr}}

.empty-state{text-align:center;padding:40px 20px;color:var(--muted);font-size:.88rem}
.empty-state i{font-size:1.8rem;display:block;margin-bottom:10px;opacity:.5}

.app-credit{
  text-align:center;margin:30px 0 10px;font-size:.72rem;color:var(--muted);
  display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;
}
.app-credit a{color:var(--muted);transition:color .15s var(--ease)}
.app-credit a:hover{color:var(--accent)}
.app-credit .dot{opacity:.4}
</style>
</head>
<body>

<nav class="navbar">
  <a href="{{ route('chatbot') }}" class="brand" style="color:var(--text)">
    <div class="brand-icon">🎬</div>
    <div>CineBot AI<span class="brand-sub">Admin Dashboard</span></div>
  </a>
  <div class="nav-right">
    <a href="{{ route('chatbot') }}" class="btn-icon" title="Back to chat"><i class="bi bi-chat-dots"></i></a>
    <div class="theme-toggle" id="themeToggle" title="Toggle light/dark theme">
      <div class="toggle-icons"><span>🌙</span><span>☀️</span></div>
      <div class="toggle-thumb" id="toggleThumb"><i class="bi bi-moon-stars-fill" id="toggleIcon"></i></div>
    </div>
  </div>
</nav>

<div class="page">
  <div class="page-header page-header-row">
    <div>
      <h1>Admin Dashboard</h1>
      <p>Overview of CineBot AI usage, user feedback, and trends.</p>
    </div>
    <a href="{{ route('admin.export') }}" class="btn-export">
      <i class="bi bi-download"></i> Exporter (CSV)
    </a>
  </div>

  <div class="stat-grid" id="statGrid">
    <div class="empty-state"><i class="bi bi-hourglass-split"></i>Loading statistics…</div>
  </div>

  <section>
    <div class="section-title"><i class="bi bi-graph-up"></i> Chatbot activity (last 14 days)</div>
    <div class="chart-card">
      <div class="chart-canvas-wrap">
        <canvas id="usageChart"></canvas>
      </div>
      <div id="usageEmpty"></div>
    </div>
  </section>

  <section>
    <div class="grid-3">
      <div class="chart-card">
        <div class="section-title" style="margin-bottom:14px"><i class="bi bi-film"></i> Most liked genres</div>
        <div class="chart-canvas-wrap small">
          <canvas id="genresChart"></canvas>
        </div>
        <div id="genresEmpty"></div>
      </div>
      <div class="chart-card">
        <div class="section-title" style="margin-bottom:14px"><i class="bi bi-pie-chart-fill"></i> Likes vs Dislikes</div>
        <div class="chart-canvas-wrap small">
          <canvas id="feedbackDoughnut"></canvas>
        </div>
        <div id="doughnutEmpty"></div>
      </div>
    </div>
  </section>

  <section>
    <div class="card">
      <div class="section-title" style="margin-bottom:14px"><i class="bi bi-heart-fill"></i> Most liked movies</div>
      <div id="moviesList"></div>
    </div>
  </section>

  <section>
    <div class="section-title"><i class="bi bi-hand-thumbs-up"></i> Feedback statistics</div>
    <div class="stat-grid" id="feedbackGrid"></div>
  </section>
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

// ── Theme ──
const html = document.documentElement;
const themeToggle = document.getElementById('themeToggle');
const toggleIcon = document.getElementById('toggleIcon');

let lastStats = null; // cached so charts can be redrawn on theme change

function chartColors(){
  const cs = getComputedStyle(html);
  return {
    text:      cs.getPropertyValue('--muted').trim(),
    grid:      cs.getPropertyValue('--border').trim(),
    accent:    cs.getPropertyValue('--accent').trim(),
    gold:      cs.getPropertyValue('--gold').trim(),
    green:     cs.getPropertyValue('--green').trim(),
    surface:   cs.getPropertyValue('--surface').trim(),
  };
}

function applyTheme(t){
  html.setAttribute('data-theme', t);
  localStorage.setItem('cinebot_theme', t);
  toggleIcon.className = t === 'dark' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
  // Redraw charts with the new theme's colors once the CSS vars have applied
  if (lastStats) {
    requestAnimationFrame(() => {
      renderUsageChart(lastStats.usage.daily);
      renderGenresChart(lastStats.top_genres);
      renderFeedbackDoughnut(lastStats.feedback);
    });
  }
}
applyTheme(localStorage.getItem('cinebot_theme') || 'dark');
themeToggle.addEventListener('click', () => {
  applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
});

function escapeHtml(s){
  const d = document.createElement('div');
  d.textContent = s ?? '';
  return d.innerHTML;
}
function fmtDay(iso){
  const d = new Date(iso);
  return d.toLocaleDateString('en-US', { day: '2-digit', month: '2-digit' });
}

async function loadStats() {
  try {
    const res = await fetch('{{ url("/api/admin/stats") }}', { headers: { Accept: 'application/json' } });
    if (!res.ok) {
      document.getElementById('statGrid').innerHTML =
        '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Unable to load statistics (admin access required).</div>';
      return;
    }
    const data = await res.json();
    lastStats = data;
    renderStatCards(data);
    renderUsageChart(data.usage.daily);
    renderGenresChart(data.top_genres);
    renderFeedbackDoughnut(data.feedback);
    renderMovies(data.top_liked_movies);
    renderFeedback(data.feedback);
  } catch (e) {
    console.error('[AdminDashboard]', e);
  }
}

function renderStatCards(data) {
  const grid = document.getElementById('statGrid');
  grid.innerHTML = `
    <div class="stat-card">
      <div class="icon icon-accent"><i class="bi bi-people-fill"></i></div>
      <div class="value">${data.users.total}</div>
      <div class="label">Registered users</div>
    </div>
    <div class="stat-card">
      <div class="icon icon-green"><i class="bi bi-person-check-fill"></i></div>
      <div class="value">${data.users.active_7d}</div>
      <div class="label">Active (last 7 days)</div>
    </div>
    <div class="stat-card">
      <div class="icon icon-gold"><i class="bi bi-chat-dots-fill"></i></div>
      <div class="value">${data.usage.total_conversations}</div>
      <div class="label">Total conversations</div>
    </div>
    <div class="stat-card">
      <div class="icon icon-accent"><i class="bi bi-envelope-paper-fill"></i></div>
      <div class="value">${data.usage.total_messages}</div>
      <div class="label">Messages exchanged</div>
    </div>
  `;
}

// ── Chart.js instances (kept so we can destroy/redraw on theme toggle or reload) ──
let usageChartInstance = null;
let genresChartInstance = null;
let doughnutChartInstance = null;

function renderUsageChart(daily) {
  const canvas = document.getElementById('usageChart');
  const emptyBox = document.getElementById('usageEmpty');
  if (usageChartInstance) { usageChartInstance.destroy(); usageChartInstance = null; }

  if (!daily?.length) {
    canvas.style.display = 'none';
    emptyBox.innerHTML = '<div class="empty-state"><i class="bi bi-bar-chart"></i>No usage data yet.</div>';
    return;
  }
  canvas.style.display = '';
  emptyBox.innerHTML = '';

  const c = chartColors();
  usageChartInstance = new Chart(canvas, {
    data: {
      labels: daily.map(d => fmtDay(d.day)),
      datasets: [
        {
          type: 'bar',
          label: 'Messages',
          data: daily.map(d => d.messages),
          backgroundColor: c.accent + 'cc',
          borderRadius: 5,
          maxBarThickness: 28,
          order: 2,
          yAxisID: 'y',
        },
        {
          type: 'line',
          label: 'Active users',
          data: daily.map(d => d.active_users),
          borderColor: c.gold,
          backgroundColor: c.gold,
          tension: 0.35,
          pointRadius: 3,
          pointBackgroundColor: c.gold,
          borderWidth: 2,
          order: 1,
          yAxisID: 'y1',
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          display: true, position: 'top', align: 'end',
          labels: { color: c.text, boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 11 } },
        },
        tooltip: { backgroundColor: c.surface, titleColor: c.text, bodyColor: c.text, borderColor: c.grid, borderWidth: 1 },
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: c.text, font: { size: 11 } } },
        y: { position: 'left', beginAtZero: true, ticks: { color: c.text, precision: 0 }, grid: { color: c.grid } },
        y1: { position: 'right', beginAtZero: true, ticks: { color: c.text, precision: 0 }, grid: { display: false } },
      },
    },
  });
}

function renderGenresChart(genres) {
  const canvas = document.getElementById('genresChart');
  const emptyBox = document.getElementById('genresEmpty');
  if (genresChartInstance) { genresChartInstance.destroy(); genresChartInstance = null; }

  if (!genres?.length) {
    canvas.style.display = 'none';
    emptyBox.innerHTML = '<div class="empty-state"><i class="bi bi-film"></i>Not enough data yet.</div>';
    return;
  }
  canvas.style.display = '';
  emptyBox.innerHTML = '';

  const c = chartColors();
  const top = genres.slice(0, 8);
  genresChartInstance = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: top.map(g => g.preference_value),
      datasets: [{
        data: top.map(g => g.total_score),
        backgroundColor: c.accent + 'cc',
        borderRadius: 5,
        maxBarThickness: 20,
      }],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { backgroundColor: c.surface, titleColor: c.text, bodyColor: c.text, borderColor: c.grid, borderWidth: 1 } },
      scales: {
        x: { beginAtZero: true, ticks: { color: c.text, precision: 0 }, grid: { color: c.grid } },
        y: { ticks: { color: c.text, font: { size: 11 } }, grid: { display: false } },
      },
    },
  });
}

function renderFeedbackDoughnut(fb) {
  const canvas = document.getElementById('feedbackDoughnut');
  const emptyBox = document.getElementById('doughnutEmpty');
  if (doughnutChartInstance) { doughnutChartInstance.destroy(); doughnutChartInstance = null; }

  if (!fb || (fb.likes === 0 && fb.dislikes === 0)) {
    canvas.style.display = 'none';
    emptyBox.innerHTML = '<div class="empty-state"><i class="bi bi-pie-chart"></i>No feedback yet.</div>';
    return;
  }
  canvas.style.display = '';
  emptyBox.innerHTML = '';

  const c = chartColors();
  doughnutChartInstance = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: ['Likes', 'Dislikes'],
      datasets: [{
        data: [fb.likes, fb.dislikes],
        backgroundColor: [c.green, c.accent],
        borderColor: c.surface,
        borderWidth: 3,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: {
          display: true, position: 'bottom',
          labels: { color: c.text, boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 11 } },
        },
        tooltip: { backgroundColor: c.surface, titleColor: c.text, bodyColor: c.text, borderColor: c.grid, borderWidth: 1 },
      },
    },
  });
}

function renderMovies(movies) {
  const box = document.getElementById('moviesList');
  if (!movies?.length) {
    box.innerHTML = '<div class="empty-state"><i class="bi bi-heart"></i>No liked movies yet.</div>';
    return;
  }
  box.innerHTML = movies.map((m, i) => `
    <div class="movie-row">
      <div class="movie-rank">${i + 1}</div>
      <div class="movie-row-title">${escapeHtml(m.movie_title)}</div>
      <div class="movie-row-meta">
        <span class="tile-liked"><i class="bi bi-heart-fill"></i> ${m.likes}</span>
        ${m.avg_rating ? `<span>★ ${m.avg_rating}</span>` : ''}
      </div>
    </div>`).join('');
}

function renderFeedback(fb) {
  const grid = document.getElementById('feedbackGrid');
  grid.innerHTML = `
    <div class="stat-card">
      <div class="icon icon-green"><i class="bi bi-hand-thumbs-up-fill"></i></div>
      <div class="value">${fb.likes}</div>
      <div class="label">👍 Likes</div>
    </div>
    <div class="stat-card">
      <div class="icon icon-accent"><i class="bi bi-hand-thumbs-down-fill"></i></div>
      <div class="value">${fb.dislikes}</div>
      <div class="label">👎 Dislikes</div>
    </div>
    <div class="stat-card">
      <div class="icon icon-gold"><i class="bi bi-star-fill"></i></div>
      <div class="value">${fb.avg_rating ?? '—'}</div>
      <div class="label">Average rating (${fb.ratings} ratings)</div>
    </div>
    <div class="stat-card">
      <div class="icon icon-accent"><i class="bi bi-eye-fill"></i></div>
      <div class="value">${fb.watched}</div>
      <div class="label">Movies marked "watched"</div>
    </div>
  `;
}

loadStats();
</script>

</body>
</html>

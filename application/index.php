<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Zyphor Command Center</title>

  <script>
    (function () {
      var t = null, h = null;
      try { t = localStorage.getItem('zcc.theme'); h = localStorage.getItem('zcc.hue'); } catch (e) {}
      document.documentElement.setAttribute('data-bs-theme', t || 'dark');
      if (h) document.documentElement.style.setProperty('--hue', h);
    })();
  </script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --hue: 205;
      --accent: hsl(var(--hue) 85% 48%);
      --accent-soft: hsl(var(--hue) 85% 48% / .12);
      --chrome: #e9edf5;
      --surface: #ffffff;
      --page: #f5f7fb;
      --line: #d8dfeb;
      --muted: #637089;
      --ok: #16a05a; --err: #d64545; --warn: #b7791f;
      --brand: linear-gradient(135deg, #3b82f6, #22d3ee 55%, #8b5cf6);
      --sb-w: 232px; --sb-w-c: 52px;
      --ease: .2s cubic-bezier(.4, 0, .2, 1);
      --ui: 13px;
      --mono: ui-monospace, "Cascadia Mono", "JetBrains Mono", Menlo, Consolas, monospace;
      font-size: var(--ui);
    }
    [data-bs-theme="dark"] {
      --accent: hsl(var(--hue) 95% 66%);
      --accent-soft: hsl(var(--hue) 95% 66% / .15);
      --chrome: #0d1426;
      --surface: #111a30;
      --page: #0a1020;
      --line: #1d2a47;
      --muted: #8493b1;
      --ok: #4ade80; --err: #ff6b6b; --warn: #fbbf24;
    }

    html, body { height: 100%; overflow: hidden; }
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; font-size: 1rem; background: var(--chrome); user-select: none; }
    .display-font { font-family: "Space Grotesk", system-ui, sans-serif; letter-spacing: -.01em; }

    .app { position: fixed; inset: 0; display: flex; flex-direction: column; background: var(--page); overflow: hidden; }

    /* ---------- Menu bar ---------- */
    .menubar { height: 32px; flex: 0 0 auto; display: flex; align-items: center; gap: 2px; background: var(--chrome); border-bottom: 1px solid var(--line); padding: 0 .5rem; }
    .menu-btn { border: 0; background: none; color: var(--bs-body-color); padding: .2rem .55rem; border-radius: 5px; font-size: .92rem; }
    .menu-btn:hover, .menu-btn.show { background: var(--accent-soft); }
    .dropdown-menu { font-size: .92rem; min-width: 230px; padding: .3rem; border-radius: 8px; border-color: var(--line); background: var(--surface); }
    .dropdown-item { border-radius: 5px; padding: .3rem .6rem; display: flex; justify-content: space-between; gap: 1.5rem; }
    .dropdown-item kbd { background: none; color: var(--muted); padding: 0; font-size: .82em; }

    /* ---------- Sidebar ---------- */
    .workspace { flex: 1 1 auto; display: flex; min-height: 0; position: relative; }
    .sidebar { width: var(--sb-w); flex: 0 0 auto; background: var(--chrome); border-right: 1px solid var(--line); display: flex; flex-direction: column; overflow-x: hidden; overflow-y: auto; transition: width var(--ease), transform var(--ease); }
    .sb-brand { display: flex; align-items: center; gap: .65rem; padding: .85rem .9rem .5rem; white-space: nowrap; }
    .brand-mark { width: 28px; height: 28px; border-radius: 8px; background: var(--brand); color: #fff; display: grid; place-items: center; font-size: .95rem; flex: 0 0 auto; }
    .brand-mark.lg { width: 46px; height: 46px; border-radius: 12px; font-size: 1.4rem; }
    .sb-brand .wordmark { font-size: 1.05rem; font-weight: 600; }
    .sb-head { padding: .85rem 1rem .3rem; font-size: .72rem; font-weight: 600; color: var(--muted); white-space: nowrap; transition: opacity var(--ease); }
    .sb-list { list-style: none; margin: 0; padding: 0 .5rem; }
    .sb-link { position: relative; display: flex; align-items: center; gap: .7rem; padding: .42rem .65rem; margin-bottom: 1px; border-radius: 6px; color: var(--bs-body-color); text-decoration: none; white-space: nowrap; cursor: default; }
    .sb-link i.ic { width: 18px; text-align: center; font-size: 1.05rem; color: var(--muted); flex: 0 0 auto; }
    .sb-link:hover { background: var(--accent-soft); color: var(--bs-body-color); }
    .sb-link.active { background: var(--accent-soft); color: var(--accent); font-weight: 600; box-shadow: inset 2px 0 0 var(--accent); }
    .sb-link.active i.ic { color: var(--accent); }
    .run-dot { display: none; position: absolute; right: 10px; top: 50%; margin-top: -4px; width: 8px; height: 8px; border-radius: 50%; background: var(--accent); animation: pulse 1.2s ease-in-out infinite; }
    .sb-link.running .run-dot { display: block; }
    .sb-foot { margin-top: auto; padding: .5rem; border-top: 1px solid var(--line); }
    .sb-foot .sb-link { margin: 0; }
    @keyframes pulse { 50% { opacity: .25; transform: scale(.7); } }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes blink { 50% { opacity: 0; } }

    @media (min-width: 992px) {
      body.sb-collapsed .sidebar { width: var(--sb-w-c); }
      body.sb-collapsed .sb-brand .wordmark, body.sb-collapsed .sb-link .label { opacity: 0; }
      body.sb-collapsed .sb-head { opacity: 0; height: .5rem; padding: 0; overflow: hidden; }
      body.sb-collapsed .run-dot { right: 6px; top: 7px; margin: 0; }
    }
    .sb-overlay { display: none; }
    @media (max-width: 991.98px) {
      .sidebar { position: absolute; inset: 0 auto 0 0; z-index: 50; transform: translateX(-100%); }
      body.sb-open .sidebar { transform: none; box-shadow: 0 0 30px rgba(0, 0, 0, .4); }
      body.sb-open .sb-overlay { display: block; position: absolute; inset: 0; z-index: 40; background: rgba(0, 0, 0, .45); }
    }

    /* ---------- Tabs ---------- */
    .editor { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; background: var(--page); }
    .tabs { display: flex; background: var(--chrome); border-bottom: 1px solid var(--line); overflow-x: auto; flex: 0 0 auto; scrollbar-width: none; }
    .tab { display: flex; align-items: center; gap: .5rem; padding: .5rem .5rem .5rem 1rem; border: 0; border-right: 1px solid var(--line); background: none; color: var(--muted); white-space: nowrap; position: relative; }
    .tab.pinned { padding-right: 1rem; }
    .tab:hover { color: var(--bs-body-color); }
    .tab.active { background: var(--page); color: var(--bs-body-color); font-weight: 600; }
    .tab.active::before { content: ""; position: absolute; left: 0; right: 0; top: 0; height: 2px; background: var(--accent); }
    .tab .spin-dot { display: none; width: 7px; height: 7px; border-radius: 50%; background: var(--accent); animation: pulse 1.2s infinite; }
    .tab.running .spin-dot { display: inline-block; }
    .tab-x { width: 20px; height: 20px; border-radius: 4px; display: inline-grid; place-items: center; font-size: .8rem; color: var(--muted); }
    .tab-x:hover { background: var(--accent-soft); color: var(--bs-body-color); }

    .pane-wrap { flex: 1 1 auto; min-height: 0; overflow: auto; padding: 1rem; display: flex; flex-direction: column; }
    .pane { display: none; }
    .pane.active { display: flex; flex-direction: column; flex: 1 0 auto; min-width: 0; }

    /* ---------- Shared bits ---------- */
    .panel { background: var(--surface); border: 1px solid var(--line); border-radius: 8px; }
    .panel-head { padding: .6rem .9rem; border-bottom: 1px solid var(--line); font-weight: 600; display: flex; align-items: center; justify-content: space-between; }
    .stat-icon { width: 38px; height: 38px; border-radius: 9px; display: grid; place-items: center; background: var(--accent-soft); color: var(--accent); font-size: 1.15rem; flex: 0 0 auto; }
    .tool-btn { border: 1px solid var(--line); background: var(--surface); color: var(--bs-body-color); border-radius: 6px; padding: .3rem .7rem; display: inline-flex; align-items: center; gap: .4rem; white-space: nowrap; }
    .tool-btn:hover { background: var(--accent-soft); }
    .tool-btn.primary { background: var(--accent); border-color: var(--accent); color: #fff; font-weight: 600; }
    [data-bs-theme="dark"] .tool-btn.primary { color: #06101f; }
    .tool-btn.primary:hover { filter: brightness(1.08); }
    .tool-btn.danger { background: var(--err); border-color: var(--err); color: #fff; font-weight: 600; }
    [data-bs-theme="dark"] .tool-btn.danger { color: #1a0707; }
    .tool-btn:disabled { opacity: .5; }
    .form-control, .form-select { background-color: var(--surface); border-color: var(--line); font-size: 1rem; }
    .form-check-input:checked { background-color: var(--accent); border-color: var(--accent); }
    .swatch { width: 26px; height: 26px; border-radius: 50%; border: 2px solid transparent; outline: 1px solid var(--line); }
    .swatch.active { border-color: var(--surface); outline: 2px solid var(--accent); }

    /* ---------- Home ---------- */
    .home-hero { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.1rem; }
    .home-hero h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 .15rem; }
    .home-hero p { margin: 0; color: var(--muted); max-width: 62ch; }
    .last-tile { padding: .8rem .95rem; display: flex; align-items: center; gap: .8rem; }
    .last-tile .v { font-weight: 700; font-size: 1.1rem; line-height: 1.15; }
    .last-tile .n { color: var(--muted); font-size: .88rem; }
    .st-done { color: var(--ok); } .st-failed { color: var(--err); } .st-stopped { color: var(--warn); } .st-running { color: var(--accent); } .st-idle { color: var(--muted); }
    .action-tile { width: 100%; text-align: left; display: flex; gap: .8rem; align-items: flex-start; padding: .8rem .95rem; background: var(--surface); border: 1px solid var(--line); border-radius: 8px; color: var(--bs-body-color); }
    .action-tile:hover { border-color: var(--accent); background: var(--accent-soft); }
    .action-tile .t { font-weight: 600; }
    .action-tile .d { color: var(--muted); font-size: .9rem; margin-top: .1rem; }
    .recent { list-style: none; margin: 0; padding: 0; }
    .recent li { display: flex; gap: .7rem; align-items: center; padding: .55rem .9rem; border-bottom: 1px solid var(--line); cursor: default; }
    .recent li:last-child { border-bottom: 0; }
    .recent li:hover { background: var(--accent-soft); }
    .recent .when { margin-left: auto; color: var(--muted); font-size: .85rem; white-space: nowrap; }
    .sdot { width: 8px; height: 8px; border-radius: 50%; flex: 0 0 auto; background: currentColor; }

    /* ---------- Job pages ---------- */
    .job-head { display: flex; align-items: center; gap: .85rem; margin-bottom: .9rem; flex-wrap: wrap; }
    .job-title { font-size: 1.25rem; font-weight: 700; margin: 0; }
    .job-desc { margin: .1rem 0 0; color: var(--muted); }
    .script-chip { margin-left: auto; display: inline-flex; align-items: center; gap: .4rem; font-family: var(--mono); font-size: .85rem; color: var(--muted); border: 1px solid var(--line); border-radius: 6px; padding: .2rem .55rem; background: var(--surface); }
    .job-controls { display: flex; gap: .5rem; margin-bottom: .75rem; flex-wrap: wrap; align-items: center; }
    .job-controls .arg { max-width: 360px; flex: 1 1 220px; font-family: var(--mono); }

    .term { --term-bg: #070c18; flex: 1 1 280px; min-height: 280px; display: flex; flex-direction: column; border: 1px solid var(--line); border-radius: 8px; overflow: hidden; background: var(--term-bg); }
    .term-bar { display: flex; align-items: center; gap: .6rem; padding: .35rem .6rem; background: var(--chrome); border-bottom: 1px solid var(--line); flex-wrap: wrap; }
    .term-bar .file { font-family: var(--mono); font-size: .85rem; color: var(--muted); }
    .st { display: inline-flex; align-items: center; gap: .35rem; font-size: .8rem; font-weight: 600; padding: .1rem .55rem; border-radius: 99px; background: var(--accent-soft); }
    .st .bi-arrow-repeat { animation: spin 1s linear infinite; }
    .elapsed { font-family: var(--mono); font-size: .85rem; color: var(--muted); }
    .term-tools { margin-left: auto; display: flex; gap: 2px; }
    .term-tools button { border: 0; background: none; color: var(--muted); border-radius: 5px; width: 28px; height: 26px; }
    .term-tools button:hover { background: var(--accent-soft); color: var(--bs-body-color); }
    .term-tools button.on { color: var(--accent); background: var(--accent-soft); }
    .term-body { flex: 1 1 0; min-height: 0; overflow: auto; padding: .7rem .9rem; font-family: var(--mono); font-size: 12.5px; line-height: 1.55; color: #d4dcee; user-select: text; }
    .term-body:empty::before { content: attr(data-empty); color: #6c7a96; }
    .term-body.running::after { content: "▍"; color: #5ad8ff; animation: blink 1s steps(2) infinite; }
    .ln { white-space: pre-wrap; word-break: break-word; }
    .ln.cmd { color: #5ad8ff; } .ln.ok { color: #4ade80; } .ln.err { color: #ff7b7b; } .ln.warn { color: #fbbf24; } .ln.meta { color: #7b8aa8; }
    .term-foot { padding: .3rem .8rem; font-size: .8rem; color: #6c7a96; border-top: 1px solid #16213a; display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }

    /* ---------- Status bar ---------- */
    .statusbar { height: 26px; flex: 0 0 auto; display: flex; align-items: center; gap: 1rem; padding: 0 .75rem; background: var(--chrome); border-top: 1px solid var(--line); font-size: .82rem; white-space: nowrap; overflow: hidden; color: var(--muted); }
    .statusbar button { border: 0; background: none; color: inherit; padding: 0 .4rem; height: 100%; }
    .statusbar button:hover { background: var(--accent-soft); color: var(--bs-body-color); }
    .statusbar .right { margin-left: auto; display: flex; align-items: center; gap: .3rem; }
    .mdot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: .3rem; }

    .toast { border-left: 3px solid var(--accent); }
    .toast.k-ok { border-left-color: var(--ok); } .toast.k-err { border-left-color: var(--err); } .toast.k-warn { border-left-color: var(--warn); }
    .modal-content { background: var(--surface); border-color: var(--line); }
    .modal-header, .modal-footer { border-color: var(--line); }

    ::-webkit-scrollbar { width: 11px; height: 11px; }
    ::-webkit-scrollbar-thumb { background: var(--line); border-radius: 8px; border: 3px solid transparent; background-clip: content-box; }
    ::-webkit-scrollbar-thumb:hover { background: var(--muted); background-clip: content-box; }
    :focus-visible { outline: 2px solid var(--accent); outline-offset: 1px; }
    @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation-duration: .01ms !important; } }
  </style>
</head>
<body>
  <div class="app">

    <!-- Menu bar -->
    <header class="menubar">
      <button class="menu-btn d-lg-none me-1" id="mobileMenu" aria-label="Open sidebar"><i class="bi bi-list"></i></button>
      <div class="dropdown"><button class="menu-btn" data-bs-toggle="dropdown">File</button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#" data-action="run">Run this page<kbd>Ctrl+Enter</kbd></a></li>
          <li><a class="dropdown-item" href="#" data-action="stop">Stop this page<kbd>Ctrl+.</kbd></a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#" data-action="clearall">Clear all cached output</a></li>
          <li><a class="dropdown-item" href="#" data-action="settings">Settings<kbd>Ctrl+,</kbd></a></li>
        </ul></div>
      <div class="dropdown"><button class="menu-btn" data-bs-toggle="dropdown">View</button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#" data-action="sidebar">Toggle sidebar<kbd>Ctrl+B</kbd></a></li>
          <li><a class="dropdown-item" href="#" data-action="theme">Toggle dark mode<kbd>Alt+T</kbd></a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#" data-action="zoomin">Zoom in<kbd>Ctrl +</kbd></a></li>
          <li><a class="dropdown-item" href="#" data-action="zoomout">Zoom out<kbd>Ctrl −</kbd></a></li>
          <li><a class="dropdown-item" href="#" data-action="zoomreset">Reset zoom<kbd>Ctrl 0</kbd></a></li>
        </ul></div>
      <div class="dropdown d-none d-sm-block"><button class="menu-btn" data-bs-toggle="dropdown">Help</button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#" data-action="about">About Command Center</a></li>
        </ul></div>
    </header>

    <div class="workspace">
      <aside class="sidebar" id="sidebar" aria-label="Navigation">
        <div class="sb-brand"><span class="brand-mark"><i class="bi bi-lightning-charge-fill"></i></span><span class="wordmark display-font">Zyphor</span></div>
        <div id="nav"></div>
        <div class="sb-foot"><ul class="sb-list" style="padding:0"><li><a class="sb-link" href="#" data-action="settings" title="Settings"><i class="ic bi bi-gear"></i><span class="label">Settings</span></a></li></ul></div>
      </aside>
      <div class="sb-overlay" id="sbOverlay"></div>

      <section class="editor">
        <div class="tabs" id="tabs"></div>
        <div class="pane-wrap" id="panes"></div>
      </section>
    </div>

    <footer class="statusbar">
      <button id="modeBtn" title="Open settings"><span class="mdot" id="modeDot"></span><span id="modeLabel">Demo mode</span></button>
      <span id="runCount" class="d-none"></span>
      <span class="right">
        <span id="zoomLabel" class="d-none d-md-inline">100%</span>
        <button id="themeBtn" title="Toggle dark mode (Alt+T)"><i class="bi bi-sun"></i></button>
        <button id="sbBtn" title="Toggle sidebar (Ctrl+B)"><i class="bi bi-layout-sidebar"></i></button>
        <button data-action="settings" title="Settings (Ctrl+,)"><i class="bi bi-gear"></i></button>
      </span>
    </footer>
  </div>

  <!-- Settings -->
  <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <div class="modal-header"><h2 class="modal-title fs-6" id="settingsTitle">Settings</h2><button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body d-flex flex-column gap-3">
        <div>
          <label class="form-label" for="setMode">Run scripts with</label>
          <select class="form-select" id="setMode">
            <option value="demo">Demo (simulated output, nothing is executed)</option>
            <option value="live">Live backend (process/run.php)</option>
          </select>
        </div>
        <div>
          <label class="form-label" for="setEndpoint">Backend endpoint</label>
          <div class="input-group">
            <input class="form-control" id="setEndpoint" value="process/run.php" style="font-family:var(--mono)">
            <button class="tool-btn" id="pingBtn" type="button">Test</button>
          </div>
        </div>
        <hr class="my-0" style="border-color:var(--line)">
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="setDark"><label class="form-check-label" for="setDark">Dark mode</label></div>
        <div>
          <div class="mb-2">Accent color</div>
          <div class="d-flex gap-2" id="swatches">
            <button class="swatch" data-hue="205" style="background:hsl(205 85% 48%)" aria-label="Sky"></button>
            <button class="swatch" data-hue="262" style="background:hsl(262 85% 60%)" aria-label="Violet"></button>
            <button class="swatch" data-hue="162" style="background:hsl(162 85% 40%)" aria-label="Teal"></button>
            <button class="swatch" data-hue="18" style="background:hsl(18 90% 55%)" aria-label="Orange"></button>
          </div>
        </div>
        <div>
          <label class="form-label" for="zoomRange">Interface size <span id="zoomVal" class="text-body-secondary"></span></label>
          <input type="range" class="form-range" id="zoomRange" min="11" max="17" step="1" value="13">
        </div>
      </div>
      <div class="modal-footer"><button class="tool-btn primary" data-bs-dismiss="modal">Done</button></div>
    </div></div>
  </div>

  <!-- Confirm -->
  <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content">
      <div class="modal-header"><h2 class="modal-title fs-6" id="confirmTitle">Are you sure?</h2></div>
      <div class="modal-body" id="confirmText"></div>
      <div class="modal-footer"><button class="tool-btn" data-bs-dismiss="modal">Cancel</button><button class="tool-btn danger" id="confirmOk">Continue</button></div>
    </div></div>
  </div>

  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="margin-bottom:26px;z-index:3100">
    <div id="toast" class="toast" role="status"><div class="d-flex"><div class="toast-body" id="toastBody"></div><button class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  $(function () {
    var $b = $('body'), $h = $('html');
    var desktop = function () { return window.matchMedia('(min-width: 992px)').matches; };
    var rnd = function (a, b) { return Math.floor(a + Math.random() * (b - a)); };
    function load(k, d) { try { var v = localStorage.getItem(k); return v ? JSON.parse(v) : d; } catch (e) { return d; } }
    function save(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }

    /* ============ Definitions ============ */
    var ARG_RE = /^[A-Za-z0-9._+:@\/-]{1,100}$/;
    var MAXL = 1500;
    var JOBS = {
      'upgrade':       { title: 'System Upgrade',          icon: 'bi-arrow-up-circle',       script: 'upgrade.sh',       btn: 'Run upgrade',      desc: 'Refresh package lists and install all available updates.' },
      'clean':         { title: 'System Clean',            icon: 'bi-trash3',                script: 'clean.sh',         btn: 'Run clean',        desc: 'Remove unused packages, old kernels and cached files.' },
      'info':          { title: 'System Info',             icon: 'bi-info-circle',           script: 'info.sh',          btn: 'Show system info', desc: 'Hardware, kernel, desktop and storage details.' },
      'doctor-fix':    { title: 'Doctor Fix',              icon: 'bi-bandaid',               script: 'doctor-fix.sh',    btn: 'Run fixes',        desc: 'Repair broken packages, dangling shims and common config problems.', confirm: 'Doctor Fix will change system settings and packages. Continue?' },
      'doctor-report': { title: 'Doctor Report',           icon: 'bi-file-earmark-medical',  script: 'doctor-report.sh', btn: 'Generate report',  desc: 'Summarise the health of packages, disk, services and network.' },
      'doctor-scan':   { title: 'Doctor Scan',             icon: 'bi-shield-check',          script: 'doctor-scan.sh',   btn: 'Start scan',       desc: 'Scan the system for problems without changing anything.' },
      'packages':      { title: 'Packages/Apps',           icon: 'bi-box-seam',              script: 'packages.sh',      btn: 'List installed',   desc: 'List installed packages and apps from apt, Flatpak and Snap.' },
      'search':        { title: 'Search Packages/Apps',    icon: 'bi-search',                script: 'search.sh',        btn: 'Search',           desc: 'Search the Zyphor registry, then apt, Snap and Flatpak.', arg: 'Package or app name' },
      'install':       { title: 'Install Packages/Apps',   icon: 'bi-download',              script: 'install.sh',       btn: 'Install',          desc: 'Install a package or app by name.', arg: 'Package or app name' },
      'uninstall':     { title: 'Uninstall Packages/Apps', icon: 'bi-trash',                 script: 'uninstall.sh',     btn: 'Uninstall',        desc: 'Remove a package or app by name.', arg: 'Package or app name', danger: true, confirm: 'Uninstall "{arg}"? This removes the package from your system.' }
    };
    var NAV = [
      { h: '', items: ['home'] },
      { h: 'SYSTEM', items: ['upgrade', 'clean', 'info'] },
      { h: 'DOCTOR', items: ['doctor-fix', 'doctor-report', 'doctor-scan'] },
      { h: 'PACKAGES', items: ['packages', 'search', 'install', 'uninstall'] }
    ];
    var HOME = { title: 'Home', icon: 'bi-house-door' };
    function meta(id) { return id === 'home' ? HOME : JOBS[id]; }

    /* ============ State (cached in this browser) ============ */
    var S = $.extend({ mode: 'demo', endpoint: 'process/run.php' }, load('zcc.settings', {}));
    var J = load('zcc.jobs', {});
    var H = load('zcc.hist', []);
    var T = load('zcc.tabs', { open: ['home'], active: 'home' });
    var FOLLOW = {}, BUSY = {};
    var saveTimer;
    function persist() { clearTimeout(saveTimer); saveTimer = setTimeout(flush, 300); }
    function flush() { save('zcc.jobs', J); save('zcc.hist', H); save('zcc.tabs', T); save('zcc.settings', S); }
    $(window).on('beforeunload', flush);
    function job(id) { return J[id] || (J[id] = { status: 'idle', lines: [], arg: '', startedAt: 0, endedAt: 0, code: null }); }

    /* ============ Helpers ============ */
    var toastEl = bootstrap.Toast.getOrCreateInstance($('#toast')[0], { delay: 2800 });
    function toast(msg, kind) {
      $('#toast').removeClass('k-ok k-err k-warn').addClass(kind ? 'k-' + kind : '');
      $('#toastBody').text(msg); toastEl.show();
    }
    function fmtDur(ms) { var s = Math.max(0, Math.floor(ms / 1000)); return ('0' + Math.floor(s / 60)).slice(-2) + ':' + ('0' + (s % 60)).slice(-2); }
    function rel(ts) {
      if (!ts) return '';
      var s = Math.floor((Date.now() - ts) / 1000);
      if (s < 45) return 'just now';
      if (s < 3600) return Math.round(s / 60) + ' min ago';
      if (s < 86400) return Math.round(s / 3600) + ' hr ago';
      return Math.round(s / 86400) + ' d ago';
    }
    var STAT = { idle: ['Idle', 'bi-circle'], running: ['Running', 'bi-arrow-repeat'], done: ['Finished', 'bi-check-circle-fill'], failed: ['Failed', 'bi-x-circle-fill'], stopped: ['Stopped', 'bi-slash-circle-fill'] };
    function cls(t) {
      if (/^\$ /.test(t)) return 'cmd';
      if (/^\[exit|^\^C/.test(t)) return 'meta';
      if (/⚠|\bwarn/i.test(t)) return 'warn';
      if (/✖|\b(error|failed|fatal)\b/i.test(t)) return 'err';
      if (/✔/.test(t)) return 'ok';
      return '';
    }
    function lineEl(t) { return $('<div class="ln"></div>').addClass(cls(t)).text(t === '' ? '\u00a0' : t)[0]; }

    /* ============ Simulated scripts (Demo mode) ============ */
    var SIM = {
      'upgrade': ['$ ./upgrade.sh', 'Zyphor: Refreshing package lists...', 'Hit:1 http://deb.debian.org/debian bookworm InRelease', 'Hit:2 https://zyphor-os.github.io/v2-ada-lovelace stable InRelease', 'Reading package lists... Done', 'Zyphor: 12 packages can be upgraded', 'Get:1 http://deb.debian.org/debian bookworm/main amd64 libssl3 [2,031 kB]', 'Get:2 http://deb.debian.org/debian bookworm/main amd64 openssl [1,420 kB]', 'Fetched 38.2 MB in 6s (6,310 kB/s)', 'Unpacking libssl3:amd64 ...', 'Setting up libssl3:amd64 ...', 'Setting up openssl ...', 'Processing triggers for man-db ...', 'Zyphor: ✔ System is up to date'],
      'clean': ['$ ./clean.sh', 'Zyphor: Removing unused packages...', 'The following packages will be REMOVED: libfoo1 libbar-dev linux-image-6.1.0-35-amd64', 'Removing linux-image-6.1.0-35-amd64 ...', 'Zyphor: Cleaning apt cache...', 'Freed 412 MB', 'Zyphor: Clearing thumbnail cache...', 'Zyphor: Emptying trash...', 'Zyphor: ✔ Cleanup complete, 1.1 GB freed'],
      'info': ['$ ./info.sh', 'OS:        Zyphor OS 2 "Ada Lovelace" (Debian-based)', 'Kernel:    Linux 6.1.0-37-amd64', 'Uptime:    3 hours, 12 mins', 'Packages:  1,842 (dpkg), 6 (flatpak)', 'Shell:     bash 5.2.15', 'Desktop:   Xfce 4.18', 'CPU:       4 cores', 'Memory:    3.1 GiB / 15.5 GiB', 'Disk (/):  42 GiB / 233 GiB (19%)', 'Zyphor: ✔ Done'],
      'doctor-fix': ['$ ./doctor-fix.sh', 'Zyphor: Checking dpkg state...', 'Zyphor: ✔ dpkg database is consistent', 'Zyphor: Running apt --fix-broken install...', '0 upgraded, 0 newly installed, 0 to remove', 'Zyphor: Checking dangling shims in /usr/local/bin...', '⚠ Removed dangling shim: /usr/local/bin/oldapp', 'Zyphor: Verifying XFCE panel configuration...', 'Zyphor: ✔ Panel profile OK', 'Zyphor: ✔ 1 issue fixed, 0 remaining'],
      'doctor-report': ['$ ./doctor-report.sh', 'Zyphor Doctor Report', '--------------------', 'Packages ........ ✔ OK', 'Disk space ...... ✔ OK (81% free)', 'Services ........ ⚠ 1 failed unit: bluetooth.service', 'Network ......... ✔ OK', 'Display manager . ✔ lightdm active', 'Zyphor: ✔ Report saved to ~/zyphor-doctor-report.txt'],
      'doctor-scan': ['$ ./doctor-scan.sh', 'Zyphor: Scanning system health...', '[1/5] Package integrity ... ✔', '[2/5] Filesystem ........... ✔', '[3/5] Services ............ ⚠ bluetooth.service failed', '[4/5] Security updates .... ✔', '[5/5] Disk SMART .......... ✔', 'Zyphor: ✔ Scan finished: 0 errors, 1 warning'],
      'packages': ['$ ./packages.sh', 'Listing installed packages and apps...', 'apt      firefox-esr           115.14.0esr', 'apt      libreoffice-writer    7.4.7', 'apt      xfce4-panel           4.18.4', 'flatpak  com.tencent.WeChat   stable', 'snap     spotify               1.2.31', 'Zyphor: ✔ 1,848 packages installed (first 5 shown)'],
      'search': ['$ ./search.sh {arg}', 'Zyphor: Searching registry for "{arg}"...', '{arg}    type=apt  channel=stable  [registered]', 'Looking for other related packages... (Ctrl+C to cancel)', '{arg}-dev    apt      (unregistered)', 'org.example.{arg}    flatpak  (unregistered)', 'Zyphor: ✔ 3 results'],
      'install': ['$ ./install.sh {arg}', 'Zyphor: Resolving "{arg}"...', 'Zyphor: Found in registry (type: apt)', 'Reading package lists... Done', 'The following NEW packages will be installed: {arg}', 'Need to get 5,420 kB of archives.', 'Get:1 http://deb.debian.org/debian bookworm/main amd64 {arg} [5,420 kB]', 'Fetched 5,420 kB in 2s (2,710 kB/s)', 'Unpacking {arg} ...', 'Setting up {arg} ...', 'Zyphor: ✔ {arg} installed'],
      'uninstall': ['$ ./uninstall.sh {arg}', 'Zyphor: Removing "{arg}"...', 'The following packages will be REMOVED: {arg}', 'Removing {arg} ...', 'Zyphor: Cleaning leftover shims...', 'Zyphor: ✔ {arg} removed']
    };
    function simPlan(id, arg, start) {
      var t = start, a = arg || 'package';
      return SIM[id].map(function (line, i) {
        var slow = /^Zyphor:|^Get:|^Unpacking|^Fetched/.test(line);
        t += i === 0 ? 250 : rnd(slow ? 350 : 90, slow ? 850 : 380);
        return { at: t, text: line.replace(/\{arg\}/g, a) };
      });
    }

    /* ============ Sidebar + panes ============ */
    var $nav = $('#nav'), $panes = $('#panes');
    $.each(NAV, function (_, g) {
      if (g.h) $nav.append('<div class="sb-head">' + g.h + '</div>');
      var $ul = $('<ul class="sb-list"></ul>');
      $.each(g.items, function (_, id) {
        var m = meta(id);
        $ul.append('<li><a class="sb-link" href="#" data-page="' + id + '" title="' + m.title + '"><i class="ic bi ' + m.icon + '"></i><span class="label">' + m.title + '</span><span class="run-dot"></span></a></li>');
      });
      $nav.append($ul);
    });

    function createPane(id) {
      var d = JOBS[id], j = job(id);
      var $p = $(
        '<div class="pane" id="pane-' + id + '">' +
          '<div class="job-head"><span class="stat-icon"><i class="bi ' + d.icon + '"></i></span>' +
            '<div><h1 class="job-title display-font">' + d.title + '</h1><p class="job-desc">' + d.desc + '</p></div>' +
            '<span class="script-chip"><i class="bi bi-file-earmark-code"></i>' + d.script + '</span></div>' +
          '<div class="job-controls">' +
            (d.arg ? '<input class="form-control arg" type="text" spellcheck="false" autocomplete="off" placeholder="' + d.arg + '" aria-label="' + d.arg + '">' : '') +
            '<button class="tool-btn ' + (d.danger ? 'danger' : 'primary') + ' btn-run"><i class="bi bi-play-fill"></i>' + d.btn + '</button>' +
            '<button class="tool-btn btn-stop" hidden><i class="bi bi-stop-fill"></i>Stop</button>' +
          '</div>' +
          '<div class="term">' +
            '<div class="term-bar"><span class="file">' + d.script + '</span><span class="st st-idle"></span><span class="elapsed"></span>' +
              '<div class="term-tools">' +
                '<button class="t-follow on" title="Follow output"><i class="bi bi-arrow-down-circle"></i></button>' +
                '<button class="t-copy" title="Copy output"><i class="bi bi-clipboard"></i></button>' +
                '<button class="t-save" title="Download log"><i class="bi bi-download"></i></button>' +
                '<button class="t-clear" title="Clear output"><i class="bi bi-eraser"></i></button>' +
              '</div></div>' +
            '<div class="term-body" tabindex="0" role="log" aria-live="off" data-empty="No output yet. Press ' + d.btn + ' to run ' + d.script + '. Output is kept here while the script runs in the background."></div>' +
            '<div class="term-foot"><span class="f-lines"></span><span>Output is cached in this browser</span></div>' +
          '</div>' +
        '</div>');
      $panes.append($p);
      FOLLOW[id] = true;
      if (j.arg) $p.find('.arg').val(j.arg);

      var $body = $p.find('.term-body');
      if (j.lines.length) $body.append(j.lines.map(lineEl));
      $body.on('scroll', function () {
        var atEnd = this.scrollTop + this.clientHeight >= this.scrollHeight - 24;
        if (FOLLOW[id] !== atEnd) { FOLLOW[id] = atEnd; $p.find('.t-follow').toggleClass('on', atEnd); }
      });

      $p.find('.btn-run').on('click', function () { requestRun(id); });
      $p.find('.btn-stop').on('click', function () { stopJob(id); });
      $p.find('.arg').on('keydown', function (e) { if (e.key === 'Enter') requestRun(id); });
      $p.find('.t-follow').on('click', function () {
        FOLLOW[id] = !FOLLOW[id]; $(this).toggleClass('on', FOLLOW[id]);
        if (FOLLOW[id]) $body.scrollTop($body[0].scrollHeight);
      });
      $p.find('.t-copy').on('click', function () {
        var txt = job(id).lines.join('\n');
        if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(txt).then(function () { toast('Output copied', 'ok'); }, function () { toast('Copy was blocked by the browser', 'warn'); });
        else toast('Copy is not available in this browser', 'warn');
      });
      $p.find('.t-save').on('click', function () {
        var d2 = new Date(), n = id + '-' + d2.toISOString().slice(0, 16).replace(/[-:T]/g, '') + '.log';
        var url = URL.createObjectURL(new Blob([job(id).lines.join('\n') + '\n'], { type: 'text/plain' }));
        $('<a>').attr({ href: url, download: n }).appendTo('body')[0].click(); URL.revokeObjectURL(url);
      });
      $p.find('.t-clear').on('click', function () { clearJob(id); });
    }

    /* ============ Terminal output ============ */
    function addLines(id, arr) {
      if (!arr.length) return;
      var j = job(id), $body = $('#pane-' + id + ' .term-body');
      Array.prototype.push.apply(j.lines, arr);
      $body.append(arr.map(lineEl));
      var over = j.lines.length - MAXL;
      if (over > 0) { j.lines.splice(0, over); $body.children().slice(0, over).remove(); }
      if (FOLLOW[id]) $body.scrollTop($body[0].scrollHeight);
      updateFoot(id); persist();
    }
    function addText(id, text) {
      text = text.replace(/\x1b\[[0-9;?]*[A-Za-z]/g, '');
      var arr = text.split(/\r?\n/); if (arr[arr.length - 1] === '') arr.pop();
      addLines(id, arr);
    }
    function updateFoot(id) {
      var j = job(id);
      $('#pane-' + id + ' .f-lines').text(j.lines.length ? j.lines.length + ' lines' + (j.endedAt ? ' · last run ' + rel(j.endedAt) : '') : 'Empty');
    }
    function clearJob(id) {
      var j = job(id);
      if (j.status === 'running') { toast('Stop the script before clearing its output', 'warn'); return; }
      j.lines = []; j.status = 'idle'; j.code = null; j.endedAt = 0;
      $('#pane-' + id + ' .term-body').empty(); setStatus(id); persist();
    }

    /* ============ Status ============ */
    function setStatus(id) {
      var j = job(id), st = STAT[j.status] || STAT.idle, running = j.status === 'running', $p = $('#pane-' + id);
      $p.find('.st').attr('class', 'st st-' + j.status).html('<i class="bi ' + st[1] + '"></i>' + st[0] + (j.status === 'done' || j.status === 'failed' ? (j.code != null ? ' · exit ' + j.code : '') : ''));
      $p.find('.term-body').toggleClass('running', running);
      $p.find('.btn-run').prop('disabled', running);
      $p.find('.btn-stop').prop('hidden', !running);
      $p.find('.arg').prop('disabled', running);
      $('.sb-link[data-page="' + id + '"]').toggleClass('running', running);
      $('.tab[data-page="' + id + '"]').toggleClass('running', running);
      tickElapsed(id); updateFoot(id);
      var n = $.map(J, function (x) { return x.status === 'running' ? 1 : null; }).length;
      $('#runCount').toggleClass('d-none', !n).html('<i class="bi bi-arrow-repeat" style="display:inline-block;animation:spin 1s linear infinite"></i> ' + n + ' running');
      if (T.active === 'home') renderHome();
    }
    function tickElapsed(id) {
      var j = job(id), t = '';
      if (j.status === 'running') t = fmtDur(Date.now() - j.startedAt);
      else if (j.endedAt && j.startedAt) t = fmtDur(j.endedAt - j.startedAt);
      $('#pane-' + id + ' .elapsed').text(t);
    }
    setInterval(function () { $.each(J, function (id, j) { if (j.status === 'running') tickElapsed(id); }); }, 1000);

    /* ============ Running jobs ============ */
    function requestRun(id) {
      var d = JOBS[id], j = job(id); if (!d || j.status === 'running') return;
      var arg = '';
      if (d.arg) {
        var $a = $('#pane-' + id + ' .arg'); arg = $.trim($a.val());
        if (!arg) { toast('Enter a package or app name first', 'warn'); $a.focus(); return; }
        if (!ARG_RE.test(arg)) { toast('Use letters, numbers and . _ + : @ / - only', 'warn'); $a.focus(); return; }
      }
      if (d.confirm) {
        $('#confirmText').text(d.confirm.replace('{arg}', arg));
        $('#confirmOk').off('click').on('click', function () { bootstrap.Modal.getInstance($('#confirmModal')[0]).hide(); startJob(id, arg); });
        bootstrap.Modal.getOrCreateInstance($('#confirmModal')[0]).show();
      } else startJob(id, arg);
    }

    function startJob(id, arg) {
      var j = job(id); if (j.status === 'running') return;
      j.status = 'running'; j.lines = []; j.arg = arg; j.startedAt = Date.now(); j.endedAt = 0; j.code = null; j.offset = 0; j.runId = null;
      j.live = S.mode === 'live'; delete j.plan;
      $('#pane-' + id + ' .term-body').empty(); FOLLOW[id] = true; $('#pane-' + id + ' .t-follow').addClass('on');
      if (!j.live) { j.plan = simPlan(id, arg, j.startedAt); j.next = 0; }
      else {
        $.ajax({ url: S.endpoint, method: 'POST', dataType: 'json', data: { action: 'start', job: id, arg: arg } })
          .done(function (r) { if (r && r.ok) { j.runId = r.id; persist(); } else finish(id, 'failed', null, '✖ Could not start: ' + ((r && r.error) || 'unknown error')); })
          .fail(function () { finish(id, 'failed', null, '✖ Could not reach ' + S.endpoint + '. Check the endpoint in Settings.'); });
      }
      setStatus(id); persist();
    }

    function stopJob(id) {
      var j = job(id); if (j.status !== 'running') return;
      if (!j.live) { finish(id, 'stopped', 130); return; }
      if (j.runId) $.post(S.endpoint, { action: 'stop', id: j.runId }).always(function () { pollJob(id); });
      else finish(id, 'stopped', 130);
    }

    function finish(id, status, code, note) {
      var j = job(id); if (j.status !== 'running') return;
      if (note) addLines(id, [note]);
      if (status === 'stopped') addLines(id, ['^C', '[stopped]']);
      else if (code != null) addLines(id, ['[exit ' + code + ']']);
      j.status = status; j.code = code; j.endedAt = Date.now(); delete j.plan; delete j.next;
      H.unshift({ job: id, status: status, at: j.endedAt, dur: j.endedAt - j.startedAt, arg: j.arg }); H.length = Math.min(H.length, 30);
      setStatus(id); persist();
      var kind = status === 'done' ? 'ok' : status === 'failed' ? 'err' : 'warn';
      if (T.active !== id) toast(JOBS[id].title + ' ' + (status === 'done' ? 'finished' : status), kind);
    }

    function pollJob(id) {
      var j = job(id);
      if (!j.live || !j.runId || BUSY[id] || j.status !== 'running') return;
      BUSY[id] = true;
      $.getJSON(S.endpoint, { action: 'poll', id: j.runId, offset: j.offset || 0 })
        .done(function (r) {
          j.errs = 0; j.warned = false;
          if (!r || !r.ok) { finish(id, 'failed', null, '✖ ' + ((r && r.error) || 'The backend lost track of this run')); return; }
          if (r.text) addText(id, r.text);
          j.offset = r.offset;
          if (r.status && r.status !== 'running') finish(id, r.status, r.code);
        })
        .fail(function () {
          j.errs = (j.errs || 0) + 1;
          if (j.errs >= 3 && !j.warned) { j.warned = true; addLines(id, ['⚠ Lost connection to the backend. The script keeps running; reconnecting...']); }
        })
        .always(function () { BUSY[id] = false; });
    }

    var tickN = 0;
    setInterval(function () {
      var now = Date.now(); tickN++;
      $.each(J, function (id, j) {
        if (j.status !== 'running' || !JOBS[id]) return;
        if (!j.live) {
          if (!j.plan) { finish(id, 'failed', null, '✖ Run was interrupted'); return; }
          var out = [];
          while (j.next < j.plan.length && j.plan[j.next].at <= now) out.push(j.plan[j.next++].text);
          if (out.length) addLines(id, out);
          if (j.next >= j.plan.length) finish(id, 'done', 0);
        } else if (tickN % 4 === 0) pollJob(id);
      });
    }, 250);

    /* ============ Tabs / navigation ============ */
    function renderTabs() {
      var $t = $('#tabs').empty();
      $.each(T.open, function (_, id) {
        var m = meta(id);
        var $tab = $('<button class="tab" data-page="' + id + '"><i class="bi ' + m.icon + '"></i><span>' + m.title + '</span><span class="spin-dot"></span></button>');
        if (id === 'home') $tab.addClass('pinned'); else $tab.append('<span class="tab-x" role="button" aria-label="Close tab"><i class="bi bi-x"></i></span>');
        if (id !== 'home' && job(id).status === 'running') $tab.addClass('running');
        $t.append($tab);
      });
      $('.tab[data-page="' + T.active + '"]').addClass('active');
    }
    function openPage(id) {
      if (!meta(id)) id = 'home';
      if ($.inArray(id, T.open) < 0) T.open.push(id);
      T.active = id; renderTabs(); showPage(id); persist();
    }
    function closeTab(id) {
      var i = $.inArray(id, T.open); if (i < 1) return;
      T.open.splice(i, 1);
      if (T.active === id) T.active = T.open[Math.min(i, T.open.length - 1)];
      renderTabs(); showPage(T.active); persist();
    }
    function showPage(id) {
      $('.pane').removeClass('active'); $('#pane-' + id).addClass('active');
      $('.sb-link[data-page]').removeClass('active').filter('[data-page="' + id + '"]').addClass('active');
      $('.tab').removeClass('active').filter('[data-page="' + id + '"]').addClass('active');
      if (id === 'home') renderHome();
      else { var b = $('#pane-' + id + ' .term-body')[0]; if (FOLLOW[id]) b.scrollTop = b.scrollHeight; }
      $('#panes').scrollTop(0);
    }
    $(document).on('click', '.sb-link[data-page], .tab[data-page]', function (e) {
      e.preventDefault();
      if ($(e.target).closest('.tab-x').length) { closeTab($(this).data('page')); return; }
      openPage($(this).data('page')); if (!desktop()) $b.removeClass('sb-open');
    });

    /* ============ Home ============ */
    var $home = $('<div class="pane" id="pane-home"></div>'); $panes.append($home);
    function lastOf(ids) {
      var best = null;
      $.each(ids, function (_, i) { var j = J[i]; if (j && j.status !== 'idle' && (!best || j.startedAt > J[best].startedAt)) best = i; });
      return best;
    }
    function renderHome() {
      var tiles = [['Last upgrade', ['upgrade'], 'bi-arrow-up-circle'], ['Last clean', ['clean'], 'bi-trash3'], ['Last doctor scan', ['doctor-scan'], 'bi-shield-check'], ['Last package change', ['install', 'uninstall'], 'bi-box-seam']];
      var h = '<div class="home-hero"><span class="brand-mark lg"><i class="bi bi-lightning-charge-fill"></i></span><div><h1 class="display-font">Zyphor Command Center</h1><p>Maintain your system and manage apps from one place. Scripts keep running in the background, and their output stays in each page\'s terminal even when you switch away.</p></div></div>';
      h += '<div class="row g-3 mb-3">';
      $.each(tiles, function (_, t) {
        var id = lastOf(t[1]), j = id && J[id], st = j ? j.status : 'idle';
        var word = j ? (st === 'done' ? 'Finished' : STAT[st][0]) : 'Never run';
        h += '<div class="col-6 col-xl-3"><div class="panel last-tile"><span class="stat-icon"><i class="bi ' + t[2] + '"></i></span><div><div class="n">' + t[0] + '</div><div class="v st-' + st + '">' + word + '</div><div class="n">' + (j ? (st === 'running' ? 'started ' + rel(j.startedAt) : rel(j.endedAt || j.startedAt)) : 'Open a page to run it') + '</div></div></div></div>';
      });
      h += '</div><div class="row g-3"><div class="col-xl-8"><div class="panel h-100"><div class="panel-head">Quick actions</div><div class="p-3"><div class="row g-2">';
      $.each(JOBS, function (id, d) {
        h += '<div class="col-md-6"><button class="action-tile" data-open="' + id + '"><span class="stat-icon"><i class="bi ' + d.icon + '"></i></span><span><div class="t">' + d.title + '</div><div class="d">' + d.desc + '</div></span></button></div>';
      });
      h += '</div></div></div></div><div class="col-xl-4"><div class="panel h-100"><div class="panel-head">Recent activity</div>';
      if (!H.length) h += '<div class="p-3 text-body-secondary">Nothing has run yet. Pick an action to get started.</div>';
      else {
        h += '<ul class="recent">';
        $.each(H.slice(0, 8), function (_, e) {
          if (!JOBS[e.job]) return;
          h += '<li data-open="' + e.job + '"><span class="sdot st-' + e.status + '"></span><div class="text-truncate">' + JOBS[e.job].title + (e.arg ? ' <span class="text-body-secondary">' + $('<i>').text(e.arg).html() + '</span>' : '') + '</div><span class="when">' + rel(e.at) + ' · ' + fmtDur(e.dur) + '</span></li>';
        });
        h += '</ul>';
      }
      h += '</div></div></div>';
      $home.html(h);
    }
    $(document).on('click', '[data-open]', function () { openPage($(this).data('open')); });
    setInterval(function () { if (T.active === 'home') renderHome(); }, 30000);

    /* ============ Sidebar collapse ============ */
    try { if (localStorage.getItem('zcc.sb') === '1' && desktop()) $b.addClass('sb-collapsed'); } catch (e) {}
    function toggleSidebar() {
      if (desktop()) { $b.toggleClass('sb-collapsed'); try { localStorage.setItem('zcc.sb', $b.hasClass('sb-collapsed') ? '1' : '0'); } catch (e) {} }
      else $b.toggleClass('sb-open');
    }
    $('#sbBtn, #mobileMenu').on('click', toggleSidebar);
    $('#sbOverlay').on('click', function () { $b.removeClass('sb-open'); });
    $(window).on('resize', function () { if (desktop()) $b.removeClass('sb-open'); });

    /* ============ Theme / accent / zoom / mode ============ */
    function paintTheme() {
      var dark = $h.attr('data-bs-theme') === 'dark';
      $('#themeBtn i').attr('class', dark ? 'bi bi-sun' : 'bi bi-moon-stars'); $('#setDark').prop('checked', dark);
    }
    function setTheme(t) { $h.attr('data-bs-theme', t); try { localStorage.setItem('zcc.theme', t); } catch (e) {} paintTheme(); }
    function toggleTheme() { setTheme($h.attr('data-bs-theme') === 'dark' ? 'light' : 'dark'); }
    $('#themeBtn').on('click', toggleTheme);
    $('#setDark').on('change', function () { setTheme(this.checked ? 'dark' : 'light'); });
    paintTheme();

    var hue = $.trim($h[0].style.getPropertyValue('--hue')) || '205';
    function paintSwatch() { $('.swatch').removeClass('active').filter('[data-hue="' + hue + '"]').addClass('active'); }
    $('#swatches').on('click', '.swatch', function () { hue = String($(this).data('hue')); $h[0].style.setProperty('--hue', hue); try { localStorage.setItem('zcc.hue', hue); } catch (e) {} paintSwatch(); });
    paintSwatch();

    var size = 13; try { size = parseInt(localStorage.getItem('zcc.ui'), 10) || 13; } catch (e) {}
    function setSize(n) {
      size = Math.max(11, Math.min(17, n)); try { localStorage.setItem('zcc.ui', size); } catch (e) {}
      $h[0].style.setProperty('--ui', size + 'px'); $h.css('font-size', size + 'px');
      var pct = Math.round(size / 13 * 100) + '%'; $('#zoomRange').val(size); $('#zoomVal, #zoomLabel').text(pct);
    }
    $('#zoomRange').on('input', function () { setSize(+this.value); });
    setSize(size);

    function paintMode() {
      var live = S.mode === 'live';
      $('#modeLabel').text(live ? 'Live backend' : 'Demo mode (simulated)'); $('#modeDot').css('background', live ? 'var(--ok)' : 'var(--warn)');
      $('#setMode').val(S.mode); $('#setEndpoint').val(S.endpoint);
    }
    $('#setMode').on('change', function () { S.mode = this.value; paintMode(); persist(); });
    $('#setEndpoint').on('change', function () { S.endpoint = $.trim(this.value) || 'process/run.php'; persist(); });
    $('#pingBtn').on('click', function () {
      S.endpoint = $.trim($('#setEndpoint').val()) || 'process/run.php';
      $.getJSON(S.endpoint, { action: 'ping' }).done(function (r) { toast(r && r.ok ? 'Backend is reachable' : 'Backend replied with an error', r && r.ok ? 'ok' : 'err'); })
        .fail(function () { toast('Could not reach ' + S.endpoint, 'err'); });
    });
    $('#modeBtn').on('click', function () { actions.settings(); });
    paintMode();

    /* ============ Actions + shortcuts ============ */
    var actions = {
      run: function () { if (JOBS[T.active]) requestRun(T.active); },
      stop: function () { if (JOBS[T.active]) stopJob(T.active); },
      clearall: function () {
        var busy = false; $.each(J, function (_, j) { if (j.status === 'running') busy = true; });
        if (busy) { toast('Stop running scripts before clearing output', 'warn'); return; }
        $.each(JOBS, function (id) { clearJob(id); }); H = []; renderHome(); persist(); toast('Cached output cleared', 'ok');
      },
      settings: function () { bootstrap.Modal.getOrCreateInstance($('#settingsModal')[0]).show(); },
      sidebar: toggleSidebar, theme: toggleTheme,
      zoomin: function () { setSize(size + 1); }, zoomout: function () { setSize(size - 1); }, zoomreset: function () { setSize(13); },
      about: function () { toast('Zyphor Command Center · Bootstrap 5.3 + jQuery 3.7'); }
    };
    $(document).on('click', '[data-action]', function (e) { e.preventDefault(); var f = actions[$(this).data('action')]; if (f) f(); });
    $(document).on('keydown', function (e) {
      var mod = e.ctrlKey || e.metaKey, k = e.key.toLowerCase();
      if (mod && k === 'b') { e.preventDefault(); toggleSidebar(); }
      else if (mod && k === 'enter') { e.preventDefault(); actions.run(); }
      else if (mod && k === '.') { e.preventDefault(); actions.stop(); }
      else if (mod && k === ',') { e.preventDefault(); actions.settings(); }
      else if (e.altKey && k === 't') { e.preventDefault(); toggleTheme(); }
      else if (mod && (k === '=' || k === '+')) { e.preventDefault(); setSize(size + 1); }
      else if (mod && k === '-') { e.preventDefault(); setSize(size - 1); }
      else if (mod && k === '0') { e.preventDefault(); setSize(13); }
      else if (k === 'escape') $b.removeClass('sb-open');
    });

    /* ============ Boot ============ */
    $.each(JOBS, function (id) { createPane(id); });
    // Runs that were mid-flight when the page closed
    $.each(J, function (id, j) {
      if (!JOBS[id]) { delete J[id]; return; }
      if (j.status === 'running' && j.live && !j.runId) { j.status = 'failed'; j.endedAt = Date.now(); j.lines.push('✖ Run was interrupted before it started'); $('#pane-' + id + ' .term-body').append(lineEl(j.lines[j.lines.length - 1])); }
    });
    T.open = $.grep(T.open, function (id) { return !!meta(id); });
    if ($.inArray('home', T.open) < 0) T.open.unshift('home');
    if (!meta(T.active)) T.active = 'home';
    $.each(JOBS, function (id) { setStatus(id); });
    renderTabs(); showPage(T.active);
  });
  </script>
</body>
</html>
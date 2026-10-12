<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — Desktop</title>

  <script>
    (function () {
      var t = null, h = null;
      try { t = localStorage.getItem('theme'); h = localStorage.getItem('hue'); } catch (e) {}
      if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      document.documentElement.setAttribute('data-bs-theme', t);
      if (h) document.documentElement.style.setProperty('--hue', h);
    })();
  </script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    :root {
      --hue: 217;
      --accent: hsl(var(--hue) 85% 52%); 
      --accent-soft: hsl(var(--hue) 85% 52% / .12);
      --chrome: #e8ebf0;
      --surface: #ffffff;
      --page: #f5f6f8;
      --line: #d8dde5;
      --muted: #667085;
      --sb-w: 224px;
      --sb-w-c: 52px;
      --ease: .2s cubic-bezier(.4, 0, .2, 1);
      --ui: 13px;
      font-size: var(--ui);
    }
    [data-bs-theme="dark"] {
      --accent: hsl(var(--hue) 90% 68%);
      --accent-soft: hsl(var(--hue) 90% 68% / .16);
      --chrome: #1a1d24;
      --surface: #20242d;
      --page: #15181e;
      --line: #2f3541;
      --muted: #8b95a7;
    }

    html, body { height: 100%; overflow: hidden; }
    body {
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
      font-size: 1rem;
      background: var(--chrome);
      user-select: none;
    }
    .selectable { user-select: text; }

    /* ---------- Window ---------- */
    .app { position: fixed; inset: 0; display: flex; flex-direction: column; background: var(--page); overflow: hidden; }

    /* ---------- Menu bar ---------- */
    .menubar {
      height: 32px; flex: 0 0 auto; display: flex; align-items: center; gap: 2px;
      background: var(--chrome); border-bottom: 1px solid var(--line); padding: 0 .5rem;
    }
    .menu-btn { border: 0; background: none; color: var(--bs-body-color); padding: .2rem .55rem; border-radius: 5px; font-size: .92rem; }
    .menu-btn:hover, .menu-btn.show { background: var(--accent-soft); }
    .dropdown-menu { font-size: .92rem; min-width: 220px; padding: .3rem; border-radius: 8px; border-color: var(--line); background: var(--surface); }
    .dropdown-item { border-radius: 5px; padding: .3rem .6rem; display: flex; justify-content: space-between; gap: 1.5rem; }
    .dropdown-item kbd { background: none; color: var(--muted); padding: 0; font-size: .82em; }

    /* ---------- Workspace ---------- */
    .workspace { flex: 1 1 auto; display: flex; min-height: 0; position: relative; }

    .sidebar {
      width: var(--sb-w); flex: 0 0 auto; background: var(--chrome); border-right: 1px solid var(--line);
      display: flex; flex-direction: column; overflow: hidden;
      transition: width var(--ease), transform var(--ease);
    }
    .sb-head { padding: .7rem 1rem .3rem; font-size: .75rem; font-weight: 600; color: var(--muted); white-space: nowrap; transition: opacity var(--ease); }
    .sb-list { list-style: none; margin: 0; padding: 0 .5rem; }
    .sb-link {
      display: flex; align-items: center; gap: .7rem; padding: .42rem .65rem; margin-bottom: 1px;
      border-radius: 6px; color: var(--bs-body-color); text-decoration: none; white-space: nowrap; cursor: default;
    }
    .sb-link i { width: 18px; text-align: center; font-size: 1.05rem; color: var(--muted); flex: 0 0 auto; }
    .sb-link:hover { background: var(--accent-soft); }
    .sb-link.active { background: var(--accent-soft); color: var(--accent); font-weight: 600; box-shadow: inset 2px 0 0 var(--accent); }
    .sb-link.active i { color: var(--accent); }
    .sb-link .count { margin-left: auto; font-size: .78rem; color: var(--muted); }
    .sb-foot { margin-top: auto; padding: .5rem; border-top: 1px solid var(--line); }
    .user-chip { display: flex; align-items: center; gap: .6rem; padding: .35rem .5rem; white-space: nowrap; }
    .avatar { width: 28px; height: 28px; border-radius: 50%; display: inline-grid; place-items: center; font-size: .7rem; font-weight: 600; background: var(--accent-soft); color: var(--accent); flex: 0 0 auto; }

    @media (min-width: 992px) {
      body.sb-collapsed .sidebar { width: var(--sb-w-c); }
      body.sb-collapsed .sb-head, body.sb-collapsed .sb-link .label, body.sb-collapsed .sb-link .count, body.sb-collapsed .user-chip .meta { opacity: 0; }
      body.sb-collapsed .sb-head { height: .6rem; padding: 0; overflow: hidden; }
    }
    .sb-overlay { display: none; }
    @media (max-width: 991.98px) {
      .sidebar { position: absolute; inset: 0 auto 0 0; z-index: 50; transform: translateX(-100%); }
      body.sb-open .sidebar { transform: none; box-shadow: 0 0 30px rgba(0, 0, 0, .35); }
      body.sb-open .sb-overlay { display: block; position: absolute; inset: 0; z-index: 40; background: rgba(0, 0, 0, .4); }
    }

    /* ---------- Editor area ---------- */
    .editor { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; background: var(--page); }
    .tabs { display: flex; background: var(--chrome); border-bottom: 1px solid var(--line); overflow-x: auto; flex: 0 0 auto; scrollbar-width: none; }
    .tab {
      display: flex; align-items: center; gap: .5rem; padding: .5rem 1rem; border: 0; border-right: 1px solid var(--line);
      background: none; color: var(--muted); white-space: nowrap; position: relative;
    }
    .tab:hover { color: var(--bs-body-color); }
    .tab.active { background: var(--page); color: var(--bs-body-color); font-weight: 600; }
    .tab.active::before { content: ""; position: absolute; left: 0; right: 0; top: 0; height: 2px; background: var(--accent); }
    .toolbar { display: flex; align-items: center; gap: .5rem; padding: .5rem .9rem; border-bottom: 1px solid var(--line); background: var(--surface); flex: 0 0 auto; flex-wrap: wrap; }
    .tool-btn { border: 1px solid transparent; background: none; color: var(--bs-body-color); border-radius: 6px; padding: .25rem .55rem; display: inline-flex; align-items: center; gap: .4rem; }
    .tool-btn:hover { background: var(--accent-soft); }
    .tool-btn.primary { background: var(--accent); color: #fff; font-weight: 600; }
    [data-bs-theme="dark"] .tool-btn.primary { color: #10131a; }
    .tool-btn.primary:hover { filter: brightness(1.08); }
    .tool-sep { width: 1px; height: 20px; background: var(--line); margin: 0 .2rem; }
    .tool-search { position: relative; margin-left: auto; width: 230px; max-width: 100%; }
    .tool-search i { position: absolute; left: .55rem; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: .85rem; }
    .tool-search input { padding-left: 1.9rem; height: 28px; font-size: .92rem; border-radius: 6px; background: var(--page); border-color: var(--line); }

    .pane-wrap { flex: 1 1 auto; overflow: auto; padding: 1rem; min-height: 0; }
    .pane { display: none; }
    .pane.active { display: block; }

    /* Panels */
    .panel { background: var(--surface); border: 1px solid var(--line); border-radius: 8px; }
    .panel-head { padding: .6rem .9rem; border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; font-weight: 600; }
    .panel-body { padding: .9rem; }
    .stat { padding: .8rem .95rem; display: flex; align-items: center; gap: .8rem; }
    .stat-icon { width: 36px; height: 36px; border-radius: 8px; display: grid; place-items: center; background: var(--accent-soft); color: var(--accent); font-size: 1.1rem; flex: 0 0 auto; }
    .stat .value { font-size: 1.45rem; font-weight: 700; line-height: 1.1; letter-spacing: -.02em; }
    .stat .name { color: var(--muted); }
    .trend-up { color: #1a9d5c; } .trend-down { color: #d64545; }
    [data-bs-theme="dark"] .trend-up { color: #4cc990; } [data-bs-theme="dark"] .trend-down { color: #ff7b7b; }

    .bars { display: flex; align-items: flex-end; gap: 8px; height: 210px; }
    .bar-col { flex: 1; min-width: 0; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: 6px; }
    .bar { width: 100%; max-width: 34px; height: 0; border-radius: 5px 5px 2px 2px; background: var(--accent-soft); transition: height .7s cubic-bezier(.2, .8, .2, 1), background .15s; position: relative; }
    .bar:hover, .bar.peak { background: var(--accent); }
    .bar[data-tip]:hover::after { content: attr(data-tip); position: absolute; bottom: calc(100% + 5px); left: 50%; transform: translateX(-50%); background: var(--bs-body-color); color: var(--bs-body-bg); padding: 2px 7px; border-radius: 5px; font-size: .78rem; font-weight: 600; white-space: nowrap; }
    .bar-col small { color: var(--muted); font-size: .75rem; }

    .activity { list-style: none; margin: 0; padding: 0; }
    .activity li { display: flex; gap: .7rem; padding: .55rem .9rem; border-bottom: 1px solid var(--line); }
    .activity li:last-child { border-bottom: 0; }
    .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); margin-top: .4rem; flex: 0 0 auto; }
    .activity time { color: var(--muted); font-size: .8rem; }

    .table { --bs-table-bg: transparent; margin: 0; font-size: .95rem; }
    .table th { position: sticky; top: 0; z-index: 1; background: var(--surface); color: var(--muted); font-weight: 600; font-size: .8rem; border-bottom: 1px solid var(--line); white-space: nowrap; padding: .5rem .9rem; }
    .table td { padding: .45rem .9rem; border-bottom-color: var(--line); vertical-align: middle; }
    .table tbody tr { cursor: default; }
    .table tbody tr:hover td { background: var(--accent-soft); }
    .table tbody tr.selected td { background: var(--accent-soft); box-shadow: inset 0 0 0 9999px transparent; }
    .table tbody tr.selected td:first-child { box-shadow: inset 2px 0 0 var(--accent); }

    .swatch { width: 26px; height: 26px; border-radius: 50%; border: 2px solid transparent; outline: 1px solid var(--line); }
    .swatch.active { border-color: var(--surface); outline: 2px solid var(--accent); }
    .form-check-input:checked { background-color: var(--accent); border-color: var(--accent); }

    /* ---------- Status bar ---------- */
    .statusbar { height: 26px; flex: 0 0 auto; display: flex; align-items: center; gap: 1rem; padding: 0 .75rem; background: var(--accent); color: #fff; font-size: .82rem; white-space: nowrap; overflow: hidden; }
    [data-bs-theme="dark"] .statusbar { background: var(--chrome); color: var(--bs-body-color); border-top: 1px solid var(--line); }
    .statusbar button { border: 0; background: none; color: inherit; padding: 0 .35rem; height: 100%; }
    .statusbar button:hover { background: rgba(255, 255, 255, .18); }
    .statusbar .right { margin-left: auto; display: flex; align-items: center; gap: .4rem; }

    ::-webkit-scrollbar { width: 11px; height: 11px; }
    ::-webkit-scrollbar-thumb { background: var(--line); border-radius: 8px; border: 3px solid transparent; background-clip: content-box; }
    ::-webkit-scrollbar-thumb:hover { background: var(--muted); background-clip: content-box; border: 3px solid transparent; }
    :focus-visible { outline: 2px solid var(--accent); outline-offset: 1px; }
    @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
  </style>
</head>
<body>

  <div class="app" id="app">

    <!-- Menu bar -->
    <header class="menubar">
      <button class="menu-btn d-lg-none me-1" id="mobileMenu" aria-label="Open sidebar"><i class="bi bi-list"></i></button>

      <div class="dropdown"><button class="menu-btn" data-bs-toggle="dropdown">File</button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#" data-action="new">New report<kbd>Ctrl+N</kbd></a></li>
          <li><a class="dropdown-item" href="#" data-action="export">Export CSV<kbd>Ctrl+E</kbd></a></li>
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
          <li><a class="dropdown-item" href="#" data-action="about">About Dashboard</a></li>
        </ul></div>

    </header>

    <div class="workspace">
      <!-- Sidebar -->
      <aside class="sidebar" id="sidebar">
        <div class="sb-head">WORKSPACE</div>
        <ul class="sb-list">
          <li><a class="sb-link active" href="#" data-pane="overview" title="Overview"><i class="bi bi-speedometer2"></i><span class="label">Overview</span></a></li>
          <li><a class="sb-link" href="#" data-pane="orders" title="Orders"><i class="bi bi-receipt"></i><span class="label">Orders</span><span class="count">10</span></a></li>
          <li><a class="sb-link" href="#" data-pane="overview" title="Customers"><i class="bi bi-people"></i><span class="label">Customers</span></a></li>
          <li><a class="sb-link" href="#" data-pane="overview" title="Inventory"><i class="bi bi-box-seam"></i><span class="label">Inventory</span></a></li>
        </ul>
        <div class="sb-head">TOOLS</div>
        <ul class="sb-list">
          <li><a class="sb-link" href="#" data-pane="overview" title="Reports"><i class="bi bi-file-earmark-bar-graph"></i><span class="label">Reports</span></a></li>
          <li><a class="sb-link" href="#" data-pane="prefs" title="Preferences"><i class="bi bi-sliders"></i><span class="label">Preferences</span></a></li>
        </ul>
        <div class="sb-foot">
          <div class="user-chip"><span class="avatar">MJ</span><span class="meta"><div class="fw-semibold lh-1">Mark Jason</div><small class="text-body-secondary">Administrator</small></span></div>
        </div>
      </aside>
      <div class="sb-overlay" id="sbOverlay"></div>

      <!-- Editor -->
      <section class="editor">
        <div class="tabs" id="tabs">
          <button class="tab active" data-pane="overview"><i class="bi bi-speedometer2"></i>Overview</button>
          <button class="tab" data-pane="orders"><i class="bi bi-receipt"></i>Orders</button>
          <button class="tab" data-pane="prefs"><i class="bi bi-sliders"></i>Preferences</button>
        </div>

        <div class="toolbar">
          <button class="tool-btn primary" data-action="new"><i class="bi bi-plus-lg"></i>New report</button>
          <span class="tool-sep"></span>
          <button class="tool-btn" data-action="refresh"><i class="bi bi-arrow-clockwise"></i><span class="d-none d-md-inline">Refresh</span></button>
          <button class="tool-btn" data-action="export"><i class="bi bi-download"></i><span class="d-none d-md-inline">Export</span></button>
          <div class="tool-search">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" id="filter" placeholder="Filter orders" aria-label="Filter orders">
          </div>
        </div>

        <div class="pane-wrap">

          <!-- Overview -->
          <div class="pane active" id="pane-overview">
            <div class="row g-3 mb-3">
              <div class="col-6 col-xl-3"><div class="panel stat"><span class="stat-icon"><i class="bi bi-currency-dollar"></i></span><div><div class="value" data-count="48290" data-prefix="$">$0</div><div class="name">Revenue <span class="trend-up fw-semibold">+12.4%</span></div></div></div></div>
              <div class="col-6 col-xl-3"><div class="panel stat"><span class="stat-icon"><i class="bi bi-bag-check"></i></span><div><div class="value" data-count="1284">0</div><div class="name">Orders <span class="trend-up fw-semibold">+8.1%</span></div></div></div></div>
              <div class="col-6 col-xl-3"><div class="panel stat"><span class="stat-icon"><i class="bi bi-person-plus"></i></span><div><div class="value" data-count="326">0</div><div class="name">New customers <span class="trend-down fw-semibold">−2.3%</span></div></div></div></div>
              <div class="col-6 col-xl-3"><div class="panel stat"><span class="stat-icon"><i class="bi bi-lightning-charge"></i></span><div><div class="value" data-count="94" data-suffix="%">0%</div><div class="name">Fulfilment <span class="trend-up fw-semibold">+0.6%</span></div></div></div></div>
            </div>
            <div class="row g-3">
              <div class="col-lg-8">
                <div class="panel h-100">
                  <div class="panel-head"><span>Revenue by month</span>
                    <select class="form-select form-select-sm w-auto" id="rangeSelect" aria-label="Range"><option value="a">This year</option><option value="b">Last year</option></select>
                  </div>
                  <div class="panel-body"><div class="bars" id="bars"></div></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="panel h-100">
                  <div class="panel-head">Activity</div>
                  <ul class="activity" id="activity">
                    <li><span class="dot"></span><div>Order #10482 paid<br><time>2 min ago</time></div></li>
                    <li><span class="dot"></span><div>Ravi Kumar created a ticket<br><time>18 min ago</time></div></li>
                    <li><span class="dot"></span><div>Stock low: Walnut desk<br><time>1 hr ago</time></div></li>
                    <li><span class="dot"></span><div>Refund issued for #10479<br><time>3 hr ago</time></div></li>
                    <li><span class="dot"></span><div>Weekly report exported<br><time>Yesterday</time></div></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Orders -->
          <div class="pane" id="pane-orders">
            <div class="panel">
              <div class="table-responsive" style="max-height: calc(100vh - 240px)">
                <table class="table align-middle selectable">
                  <thead><tr><th>Customer</th><th>Order</th><th>Date</th><th class="text-end">Amount</th><th>Status</th></tr></thead>
                  <tbody id="orderRows"></tbody>
                </table>
              </div>
              <div class="panel-body py-2 small text-body-secondary border-top" style="border-color:var(--line)!important" id="rowCount"></div>
            </div>
          </div>

          <!-- Preferences -->
          <div class="pane" id="pane-prefs">
            <div class="row g-3">
              <div class="col-lg-6">
                <div class="panel">
                  <div class="panel-head">Appearance</div>
                  <div class="panel-body d-flex flex-column gap-3">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="prefDark"><label class="form-check-label" for="prefDark">Dark mode</label></div>
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="prefCompact"><label class="form-check-label" for="prefCompact">Compact table rows</label></div>
                    <div>
                      <div class="mb-2">Accent color</div>
                      <div class="d-flex gap-2" id="swatches">
                        <button class="swatch" data-hue="217" style="background:hsl(217 85% 52%)" aria-label="Blue"></button>
                        <button class="swatch" data-hue="162" style="background:hsl(162 85% 40%)" aria-label="Teal"></button>
                        <button class="swatch" data-hue="262" style="background:hsl(262 85% 60%)" aria-label="Violet"></button>
                        <button class="swatch" data-hue="18" style="background:hsl(18 90% 55%)" aria-label="Orange"></button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="panel">
                  <div class="panel-head">General</div>
                  <div class="panel-body d-flex flex-column gap-3">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="prefRefresh" checked><label class="form-check-label" for="prefRefresh">Update the status bar sync time</label></div>
                    <div>
                      <label class="form-label" for="zoomRange">Interface size <span id="zoomVal" class="text-body-secondary"></span></label>
                      <input type="range" class="form-range" id="zoomRange" min="11" max="17" step="1" value="13">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </section>
    </div>

    <!-- Status bar -->
    <footer class="statusbar">
      <span><i class="bi bi-circle-fill" style="font-size:.5rem;color:#6ee7a0"></i> Connected</span>
      <span class="d-none d-sm-inline"><i class="bi bi-arrow-repeat"></i> Synced <span id="syncAgo">just now</span></span>
      <span class="right">
        <span id="zoomLabel" class="d-none d-md-inline">100%</span>
        <button id="themeBtn" title="Toggle dark mode (Alt+T)"><i class="bi bi-moon-stars"></i></button>
        <button id="sbBtn" title="Toggle sidebar (Ctrl+B)"><i class="bi bi-layout-sidebar"></i></button>
        <span class="d-none d-md-inline">v2.4.0</span>
      </span>
    </footer>
  </div>

  <!-- Toast -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="margin-bottom:26px;z-index:3100">
    <div id="toast" class="toast align-items-center" role="status"><div class="d-flex"><div class="toast-body" id="toastBody"></div><button class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(function () {
      var $b = $('body'), $h = $('html');
      var desktop = function () { return window.matchMedia('(min-width: 992px)').matches; };
      var store = {
        get: function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
        set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} }
      };
      var toast = bootstrap.Toast.getOrCreateInstance($('#toast')[0], { delay: 2200 });
      function say(msg) { $('#toastBody').text(msg); toast.show(); }

      /* ----- Orders data ----- */
      var orders = [
        ['Ana Lopez','#10482','Oct 11',320.00,'Paid'], ['Ravi Kumar','#10481','Oct 11',89.50,'Pending'],
        ['Sofia Martin','#10480','Oct 10',1240.00,'Paid'], ['Tom Walker','#10479','Oct 10',64.99,'Refunded'],
        ['Jin Nakamura','#10478','Oct 9',458.20,'Paid'], ['Lena Fischer','#10477','Oct 9',212.00,'Pending'],
        ['Omar Haddad','#10476','Oct 8',975.40,'Paid'], ['Grace Kim','#10475','Oct 8',45.00,'Paid'],
        ['Diego Torres','#10474','Oct 7',389.90,'Refunded'], ['Maya Patel','#10473','Oct 7',128.75,'Pending']
      ];
      var badge = { Paid: 'success', Pending: 'warning', Refunded: 'danger' };
      var $rows = $('#orderRows');
      $.each(orders, function (_, o) {
        var ini = o[0].split(' ').map(function (s) { return s[0]; }).join('');
        $rows.append('<tr data-q="' + (o.join(' ')).toLowerCase() + '"><td><span class="avatar me-2">' + ini + '</span>' + o[0] +
          '</td><td>' + o[1] + '</td><td>' + o[2] + '</td><td class="text-end fw-semibold">$' + o[3].toLocaleString(undefined, { minimumFractionDigits: 2 }) +
          '</td><td><span class="badge text-bg-' + badge[o[4]] + '-subtle text-' + badge[o[4]] + '-emphasis">' + o[4] + '</span></td></tr>');
      });
      function updateCount() { var n = $rows.find('tr:visible').length; $('#rowCount').text(n + ' of ' + orders.length + ' orders'); }
      updateCount();
      $rows.on('click', 'tr', function () { $rows.find('tr').removeClass('selected'); $(this).addClass('selected'); });
      $('#filter').on('input', function () {
        var q = $.trim(this.value).toLowerCase();
        $rows.find('tr').each(function () { $(this).toggle(!q || $(this).data('q').indexOf(q) > -1); });
        updateCount();
        if (q) showPane('orders');
      });

      /* ----- Panes / tabs ----- */
      function showPane(id) {
        $('.pane').removeClass('active'); $('#pane-' + id).addClass('active');
        $('.tab').removeClass('active').filter('[data-pane="' + id + '"]').addClass('active');
        $('.sb-link').removeClass('active').filter('[data-pane="' + id + '"]').first().addClass('active');
        if (id === 'overview') drawBars($('#rangeSelect').val());
      }
      $('.tab').on('click', function () { showPane($(this).data('pane')); });
      $('.sb-link').on('click', function (e) {
        e.preventDefault();
        var id = $(this).data('pane'); showPane(id);
        $('.sb-link').removeClass('active'); $(this).addClass('active');
        if (!desktop()) $b.removeClass('sb-open');
      });

      /* ----- Sidebar ----- */
      if (store.get('sb') === '1' && desktop()) $b.addClass('sb-collapsed');
      function toggleSidebar() {
        if (desktop()) { $b.toggleClass('sb-collapsed'); store.set('sb', $b.hasClass('sb-collapsed') ? '1' : '0'); }
        else { $b.toggleClass('sb-open'); }
      }
      $('#sbBtn, #mobileMenu').on('click', toggleSidebar);
      $('#sbOverlay').on('click', function () { $b.removeClass('sb-open'); });
      $(window).on('resize', function () { if (desktop()) $b.removeClass('sb-open'); });

      /* ----- Theme ----- */
      function paintTheme() {
        var dark = $h.attr('data-bs-theme') === 'dark';
        $('#themeBtn i').attr('class', dark ? 'bi bi-sun' : 'bi bi-moon-stars');
        $('#prefDark').prop('checked', dark);
      }
      function toggleTheme(force) {
        var next = force || ($h.attr('data-bs-theme') === 'dark' ? 'light' : 'dark');
        $h.attr('data-bs-theme', next); store.set('theme', next); paintTheme();
      }
      $('#themeBtn').on('click', function () { toggleTheme(); });
      $('#prefDark').on('change', function () { toggleTheme(this.checked ? 'dark' : 'light'); });
      paintTheme();

      /* ----- Accent ----- */
      var hue = $h.css('--hue') ? $.trim($h.get(0).style.getPropertyValue('--hue')) || '217' : '217';
      function paintSwatch() { $('.swatch').removeClass('active').filter('[data-hue="' + hue + '"]').addClass('active'); }
      $('#swatches').on('click', '.swatch', function () {
        hue = String($(this).data('hue'));
        $h.get(0).style.setProperty('--hue', hue); store.set('hue', hue); paintSwatch();
      });
      paintSwatch();

      /* ----- Zoom ----- */
      var size = parseInt(store.get('ui'), 10) || 13;
      function setSize(n) {
        size = Math.max(11, Math.min(17, n)); store.set('ui', size);
        $h.get(0).style.setProperty('--ui', size + 'px'); $h.css('font-size', size + 'px');
        $('#zoomRange').val(size); $('#zoomVal').text(Math.round(size / 13 * 100) + '%'); $('#zoomLabel').text(Math.round(size / 13 * 100) + '%');
      }
      $('#zoomRange').on('input', function () { setSize(+this.value); });
      setSize(size);

      /* ----- Compact rows ----- */
      $('#prefCompact').on('change', function () { $('.table td').css('padding', this.checked ? '.2rem .9rem' : ''); });

      /* ----- Chart ----- */
      var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      var series = { a: [32,41,38,52,47,63,58,71,66,82,0,0], b: [24,28,35,33,40,44,49,46,55,58,62,70] };
      function drawBars(key) {
        var $bars = $('#bars').empty(), max = Math.max.apply(null, series[key]);
        $.each(series[key], function (i, v) {
          var $bar = $('<div class="bar"></div>').attr('data-tip', '$' + v + 'k').data('h', v ? v / max * 100 : 2);
          if (v === max) $bar.addClass('peak');
          $bars.append($('<div class="bar-col"></div>').append($bar, $('<small></small>').text(months[i])));
        });
        setTimeout(function () { $bars.find('.bar').each(function () { $(this).css('height', $(this).data('h') + '%'); }); }, 30);
      }
      $('#rangeSelect').on('change', function () { drawBars(this.value); });
      drawBars('a');

      /* ----- Count-up ----- */
      $('[data-count]').each(function () {
        var $e = $(this), end = +$e.data('count'), pre = $e.data('prefix') || '', suf = $e.data('suffix') || '';
        $({ n: 0 }).animate({ n: end }, { duration: 900,
          step: function (v) { $e.text(pre + Math.floor(v).toLocaleString() + suf); },
          complete: function () { $e.text(pre + end.toLocaleString() + suf); } });
      });

      /* ----- Sync time ----- */
      var last = Date.now();
      setInterval(function () {
        if (!$('#prefRefresh').is(':checked')) return;
        var s = Math.floor((Date.now() - last) / 1000);
        $('#syncAgo').text(s < 5 ? 'just now' : s < 60 ? s + 's ago' : Math.floor(s / 60) + 'm ago');
      }, 1000);

      /* ----- Actions (menus, toolbar, window) ----- */
      function exportCsv() {
        var csv = 'Customer,Order,Date,Amount,Status\n' + orders.map(function (o) { return o.join(','); }).join('\n');
        var url = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
        $('<a>').attr({ href: url, download: 'orders.csv' }).appendTo('body').get(0).click();
        URL.revokeObjectURL(url); say('Exported 10 orders to orders.csv');
      }
      var actions = {
        'new': function () { say('New report created'); },
        'export': exportCsv,
        'refresh': function () { last = Date.now(); $('#syncAgo').text('just now'); drawBars($('#rangeSelect').val()); say('Data refreshed'); },
        'sidebar': toggleSidebar,
        'theme': function () { toggleTheme(); },
        'zoomin': function () { setSize(size + 1); },
        'zoomout': function () { setSize(size - 1); },
        'zoomreset': function () { setSize(13); },
        'about': function () { say('Dashboard 2.4.0 — Bootstrap 5.3 + jQuery 3.7'); }
      };
      $(document).on('click', '[data-action]', function (e) {
        e.preventDefault();
        var fn = actions[$(this).data('action')]; if (fn) fn();
      });

      /* ----- Keyboard shortcuts ----- */
      $(document).on('keydown', function (e) {
        var mod = e.ctrlKey || e.metaKey, k = e.key.toLowerCase();
        if (mod && k === 'b') { e.preventDefault(); toggleSidebar(); }
        else if (mod && k === 'e') { e.preventDefault(); exportCsv(); }
        else if (mod && k === 'n') { e.preventDefault(); actions['new'](); }
        else if (e.altKey && k === 't') { e.preventDefault(); toggleTheme(); }
        else if (mod && (k === '=' || k === '+')) { e.preventDefault(); setSize(size + 1); }
        else if (mod && k === '-') { e.preventDefault(); setSize(size - 1); }
        else if (mod && k === '0') { e.preventDefault(); setSize(13); }
        else if (k === 'escape') { $b.removeClass('sb-open'); }
      });
    });
  </script>
</body>
</html>
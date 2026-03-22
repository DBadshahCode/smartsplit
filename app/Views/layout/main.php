<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> — SmartSplit</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0f4ff',
                            100: '#e0eaff',
                            200: '#c7d8fd',
                            300: '#a5bbfb',
                            400: '#7f94f7',
                            500: '#5c6af0',
                            600: '#4549e4',
                            700: '#3938ca',
                            800: '#2f2fa3',
                            900: '#2b2d82',
                            950: '#1a1b4b',
                        },
                        surface: {
                            50:  '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    },
                    fontFamily: {
                        display: ['"DM Sans"', 'sans-serif'],
                        body:    ['"DM Sans"', 'sans-serif'],
                        mono:    ['"JetBrains Mono"', 'monospace'],
                    },
                    boxShadow: {
                        'card':  '0 1px 3px 0 rgba(0,0,0,.06), 0 1px 2px -1px rgba(0,0,0,.06)',
                        'card-hover': '0 4px 12px 0 rgba(0,0,0,.10), 0 2px 4px -1px rgba(0,0,0,.06)',
                        'sidebar': '4px 0 24px 0 rgba(0,0,0,.08)',
                    },
                    borderRadius: {
                        'xl2': '1rem',
                        'xl3': '1.5rem',
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <!-- jQuery (needed by DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables with Tailwind styling -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        /* ── Base ─────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Sidebar ──────────────────────────────────────── */
        #sidebar {
            width: 256px;
            min-height: 100vh;
            background: #1a1b4b;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 40;
            transition: transform .25s ease;
            box-shadow: 4px 0 24px rgba(0,0,0,.12);
        }
        #sidebar.collapsed { transform: translateX(-256px); }

        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .sidebar-logo .logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #5c6af0, #818cf8);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(92,106,240,.4);
        }

        .sidebar-nav { flex: 1; padding: 12px 12px; overflow-y: auto; }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255,255,255,.35);
            padding: 16px 8px 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 12px;           /* ~44px tap target height */
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255,255,255,.65);
            text-decoration: none;
            transition: background .15s, color .15s;
            margin-bottom: 2px;
            min-height: 44px;             /* explicit touch target */
            touch-action: manipulation;
        }
        .nav-link:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }
        .nav-link.active {
            background: rgba(92,106,240,.25);
            color: #a5bbfb;
            border-left: 3px solid #7f94f7;
            padding-left: 9px;
        }
        .nav-link .nav-icon { opacity: .7; flex-shrink: 0; }
        .nav-link.active .nav-icon { opacity: 1; }
        .nav-link:hover .nav-icon { opacity: 1; }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        /* ── Top bar ──────────────────────────────────────── */
        #topbar {
            position: fixed;
            top: 0; right: 0;
            left: 256px;
            height: 60px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            z-index: 30;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 12px;
            transition: left .25s ease;
        }
        #topbar.sidebar-collapsed { left: 0; }

        /* ── Main content ─────────────────────────────────── */
        #main-content {
            margin-left: 256px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left .25s ease;
        }
        #main-content.sidebar-collapsed { margin-left: 0; }

        .page-content { padding: 28px 28px; }

        /* ── DataTables override ──────────────────────────── */
        .dataTables_wrapper {
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
        }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
            outline: none;
            background: #fff;
            color: #1e293b;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #7f94f7;
            box-shadow: 0 0 0 3px rgba(127,148,247,.15);
        }
        table.dataTable thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 12px;
            letter-spacing: .04em;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 12px 16px;
        }
        table.dataTable tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }
        table.dataTable tbody tr:hover td { background: #f8fafc; }
        table.dataTable { border-collapse: collapse !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px !important;
            padding: 4px 10px !important;
            font-size: 13px !important;
            color: #475569 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #5c6af0 !important;
            border-color: #5c6af0 !important;
            color: #fff !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        .dataTables_wrapper .dataTables_info { color: #94a3b8; font-size: 13px; }

        /* ── Select2 override ─────────────────────────────── */
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            min-height: 44px !important;  /* touch tap target */
            padding: 4px 8px !important;
            font-family: 'DM Sans', sans-serif !important;
            font-size: 15px !important;
            background: #fff !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px !important;
            color: #1e293b !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
        }
        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 24px rgba(0,0,0,.10) !important;
            overflow: hidden;
        }
        /* Larger touch targets inside dropdown */
        .select2-container--default .select2-results__option {
            padding: 10px 14px !important;
            font-size: 15px !important;
        }
        .select2-container--default .select2-results__option--highlighted {
            background-color: #5c6af0 !important;
        }

        /* ── Form inputs global style ─────────────────────── */
        .ss-input {
            width: 100%;
            padding: 11px 14px;           /* min 44px height with border */
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;              /* prevents iOS zoom on focus */
            font-family: 'DM Sans', sans-serif;
            color: #1e293b;
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            min-height: 44px;             /* touch tap target */
            -webkit-appearance: none;
        }
        .ss-input:focus {
            border-color: #7f94f7;
            box-shadow: 0 0 0 3px rgba(127,148,247,.15);
        }
        .ss-input::placeholder { color: #94a3b8; }
        .ss-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        /* ── Buttons ──────────────────────────────────────── */
        .ss-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 11px 20px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            border: none;
            transition: all .15s;
            min-height: 44px;             /* touch tap target */
            touch-action: manipulation;
        }
        .ss-btn-primary {
            background: #5c6af0;
            color: #fff;
        }
        .ss-btn-primary:hover  { background: #4549e4; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,106,240,.35); }
        .ss-btn-primary:active { transform: translateY(0); box-shadow: none; }
        .ss-btn-success {
            background: #10b981;
            color: #fff;
        }
        .ss-btn-success:hover  { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,.30); }
        .ss-btn-success:active { transform: translateY(0); box-shadow: none; }
        .ss-btn-danger {
            background: #fee2e2;
            color: #dc2626;
        }
        .ss-btn-danger:hover  { background: #fecaca; }
        .ss-btn-ghost {
            background: #f1f5f9;
            color: #475569;
        }
        .ss-btn-ghost:hover { background: #e2e8f0; }

        /* ── Table scroll wrapper (prevents horizontal overflow on mobile) ── */
        .ss-table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .ss-table-wrap table.dataTable {
            min-width: 520px;  /* ensures table doesn't collapse to unreadable width */
        }

        /* ── Card ─────────────────────────────────────────── */
        .ss-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .ss-card-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #f1f5f9;
        }
        .ss-card-body { padding: 24px; }

        /* ── Toast notification ───────────────────────────── */
        #ss-toast {
            position: fixed;
            bottom: 24px; right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .toast-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            pointer-events: all;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
            animation: toastIn .3s ease forwards;
            min-width: 260px;
            max-width: 380px;
        }
        .toast-success { background: #fff; border-left: 4px solid #10b981; color: #065f46; }
        .toast-error   { background: #fff; border-left: 4px solid #ef4444; color: #991b1b; }
        .toast-info    { background: #fff; border-left: 4px solid #5c6af0; color: #1e1b4b; }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* ── Page header ──────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -.02em;
        }
        .page-subtitle {
            font-size: 14px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── Badge ────────────────────────────────────────── */
        .ss-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .ss-badge-green  { background: #dcfce7; color: #15803d; }
        .ss-badge-red    { background: #fee2e2; color: #dc2626; }
        .ss-badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .ss-badge-amber  { background: #fef3c7; color: #b45309; }

        /* ── Mobile overlay ───────────────────────────────── */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 39;
        }

        /* ── Responsive breakpoints ───────────────────────── */
        @media (max-width: 1023px) {
            /* Sidebar hidden by default on mobile — slides in as drawer */
            #sidebar {
                transform: translateX(-256px);
                box-shadow: none;
            }
            #sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 4px 0 32px rgba(0,0,0,.25);
            }
            /* Topbar always full-width on mobile */
            #topbar { left: 0 !important; }
            /* Main content fills full width — no sidebar offset */
            #main-content { margin-left: 0 !important; }
            /* Tighter page padding on small screens */
            .page-content { padding: 16px 16px; }
        }
        @media (min-width: 1024px) {
            /* Hide mobile hamburger on desktop */
            .mobile-only { display: none !important; }
        }
        /* On desktop: sidebar visible by default; .collapsed class overrides when toggled */
        @media (min-width: 1024px) {
            #sidebar { transform: translateX(0); }
            #sidebar.collapsed { transform: translateX(-256px); }
        }

        /* ── Scrollbar ────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>

<body class="h-full bg-surface-50">

    <?php if (session()->get('isLoggedIn') === true): ?>

    <!-- ═══════════════════════════════════════════════════════
         SIDEBAR
    ════════════════════════════════════════════════════════ -->
    <?= $this->include('layout/navbar') ?>

    <!-- Mobile overlay -->
    <div id="sidebar-overlay" onclick="closeSidebar()"></div>

    <!-- ═══════════════════════════════════════════════════════
         TOP BAR
    ════════════════════════════════════════════════════════ -->
    <header id="topbar">
        <!-- Hamburger — mobile only -->
        <button onclick="toggleSidebar()"
                class="mobile-only p-2 rounded-lg text-surface-500 hover:bg-surface-100 hover:text-surface-700 transition-colors"
                aria-label="Open menu"
                style="min-width:44px;min-height:44px;display:flex;align-items:center;justify-content:center;">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
        <!-- Collapse toggle — desktop only -->
        <button onclick="toggleSidebarDesktop()"
                class="p-2 rounded-lg text-surface-500 hover:bg-surface-100 hover:text-surface-700 transition-colors hidden lg:flex"
                aria-label="Collapse sidebar"
                style="min-width:44px;min-height:44px;align-items:center;justify-content:center;">
            <i data-lucide="panel-left-close" id="sidebar-toggle-icon" class="w-5 h-5"></i>
        </button>

        <!-- Breadcrumb / page title -->
        <div class="flex-1 min-w-0">
            <span id="topbar-title" class="text-sm font-semibold text-surface-700 truncate"></span>
        </div>

        <!-- Right actions -->
        <div class="flex items-center gap-3">
            <!-- Month badge -->
            <span class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-surface-500 bg-surface-100 px-3 py-1.5 rounded-full">
                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                <?= date('F Y') ?>
            </span>

            <!-- User avatar dropdown -->
            <div class="relative" id="user-menu-wrapper">
                <button onclick="toggleUserMenu()" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg hover:bg-surface-100 transition-colors">
                    <div class="w-7 h-7 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        <?= strtoupper(substr(session()->get('name') ?? 'U', 0, 1)) ?>
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-surface-700 max-w-[120px] truncate">
                        <?= esc(session()->get('name')) ?>
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-surface-400 hidden sm:block"></i>
                </button>

                <!-- Dropdown — right-anchored on desktop, left-anchored on very narrow screens -->
                <div id="user-dropdown"
                     class="hidden absolute right-0 top-full mt-2 w-52 bg-white border border-surface-200 rounded-xl shadow-xl z-50 overflow-hidden py-1"
                     style="max-width: calc(100vw - 16px);">
                    <div class="px-4 py-3 border-b border-surface-100">
                        <p class="text-sm font-semibold text-surface-800 truncate"><?= esc(session()->get('name')) ?></p>
                        <p class="text-xs text-surface-400 capitalize mt-0.5"><?= esc(session()->get('role') ?? 'user') ?></p>
                    </div>
                    <a href="#" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-surface-600 hover:bg-surface-50 hover:text-surface-900 transition-colors">
                        <i data-lucide="user" class="w-4 h-4"></i> Profile
                    </a>
                    <a href="#" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-surface-600 hover:bg-surface-50 hover:text-surface-900 transition-colors">
                        <i data-lucide="settings" class="w-4 h-4"></i> Settings
                    </a>
                    <div class="border-t border-surface-100 mt-1 pt-1">
                        <a href="<?= base_url('/auth/logout') ?>"
                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ═══════════════════════════════════════════════════════
         MAIN CONTENT
    ════════════════════════════════════════════════════════ -->
    <main id="main-content">
        <div class="page-content">
            <?= $this->renderSection('content') ?>
        </div>
        <?= $this->include('layout/footer') ?>
    </main>

    <?php else: ?>

    <!-- Not logged in — full-page layout -->
    <div class="min-h-screen flex items-center justify-center bg-surface-50">
        <?= $this->renderSection('content') ?>
    </div>

    <?php endif; ?>

    <!-- Toast container -->
    <div id="ss-toast"></div>

    <!-- Scripts -->
    <script>
        // ── Lucide icons ──────────────────────────────────────
        lucide.createIcons();

        // ── Sidebar toggle (mobile) ───────────────────────────
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isOpen  = sidebar.classList.contains('mobile-open');
            sidebar.classList.toggle('mobile-open', !isOpen);
            overlay.style.display = isOpen ? 'none' : 'block';
            document.body.style.overflow = isOpen ? '' : 'hidden'; // prevent bg scroll
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('mobile-open');
            document.getElementById('sidebar-overlay').style.display = 'none';
            document.body.style.overflow = '';
        }

        // Close sidebar when a nav link is tapped on mobile
        document.querySelectorAll('#sidebar .nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) closeSidebar();
            });
        });

        // ── Swipe right-to-left to close sidebar on mobile ───
        (function() {
            let startX = 0;
            const sidebar = document.getElementById('sidebar');
            sidebar.addEventListener('touchstart', function(e) { startX = e.touches[0].clientX; }, { passive: true });
            sidebar.addEventListener('touchend', function(e) {
                const dx = e.changedTouches[0].clientX - startX;
                if (dx < -60 && window.innerWidth < 1024) closeSidebar();
            }, { passive: true });
        })();

        // ── Sidebar toggle (desktop collapse) ────────────────
        let sidebarCollapsed = false;
        function toggleSidebarDesktop() {
            sidebarCollapsed = !sidebarCollapsed;
            const sidebar = document.getElementById('sidebar');
            const topbar  = document.getElementById('topbar');
            const main    = document.getElementById('main-content');
            const icon    = document.getElementById('sidebar-toggle-icon');
            sidebar.classList.toggle('collapsed', sidebarCollapsed);
            topbar.classList.toggle('sidebar-collapsed', sidebarCollapsed);
            main.classList.toggle('sidebar-collapsed', sidebarCollapsed);
            if (icon) icon.setAttribute('data-lucide', sidebarCollapsed ? 'panel-left-open' : 'panel-left-close');
            lucide.createIcons();
        }

        // ── User dropdown ─────────────────────────────────────
        function toggleUserMenu() {
            document.getElementById('user-dropdown').classList.toggle('hidden');
        }
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('user-menu-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                document.getElementById('user-dropdown')?.classList.add('hidden');
            }
        });

        // ── Topbar title sync from active nav link ─────────────
        document.addEventListener('DOMContentLoaded', function() {
            const active = document.querySelector('.nav-link.active');
            const el     = document.getElementById('topbar-title');
            if (active && el) el.textContent = active.querySelector('.nav-text')?.textContent ?? '';
        });

        // ── Global toast helper (replaces alert()) ────────────
        window.ssToast = function(message, type = 'success') {
            const container = document.getElementById('ss-toast');
            const icons = { success: 'check-circle', error: 'x-circle', info: 'info' };
            const toast = document.createElement('div');
            toast.className = `toast-item toast-${type}`;
            toast.innerHTML = `<i data-lucide="${icons[type] || 'info'}" style="width:18px;height:18px;flex-shrink:0"></i><span>${message}</span>`;
            container.appendChild(toast);
            lucide.createIcons({ nodes: [toast] });
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(20px)';
                toast.style.transition = 'all .3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        };
    </script>

    <?= $this->renderSection('scripts') ?>

</body>
</html>
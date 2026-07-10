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
                            50: '#f0f4ff', 100: '#e0eaff', 200: '#c7d8fd',
                            300: '#a5bbfb', 400: '#7f94f7', 500: '#5c6af0',
                            600: '#4549e4', 700: '#3938ca', 800: '#2f2fa3',
                            900: '#2b2d82', 950: '#1a1b4b',
                        },
                        surface: {
                            50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0',
                            300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b',
                            600: '#475569', 700: '#334155', 800: '#1e293b',
                            900: '#0f172a', 950: '#020617',
                        },
                    },
                    fontFamily: {
                        display: ['"DM Sans"', 'sans-serif'],
                        body: ['"DM Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    boxShadow: {
                        'card': '0 1px 3px 0 rgba(0,0,0,.06), 0 1px 2px -1px rgba(0,0,0,.06)',
                        'card-hover': '0 4px 12px 0 rgba(0,0,0,.10), 0 2px 4px -1px rgba(0,0,0,.06)',
                        'sidebar': '4px 0 24px 0 rgba(0,0,0,.08)',
                    },
                    borderRadius: {
                        'xl2': '1rem',
                        'xl3': '1.5rem',
                    },
                },
            },
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    <!-- Lucide Icons (UMD — global `lucide` object) -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- SS.Table (custom table component — replaces DataTables) -->
    <script src="<?= base_url('assets/js/ss-table.js') ?>"></script>

    <style>
        /* ── Reset / base ─────────────────────────────────── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

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
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 40;
            transition: transform .25s ease;
            box-shadow: 4px 0 24px rgba(0, 0, 0, .12);
        }

        #sidebar.collapsed {
            transform: translateX(-256px);
        }

        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        /* Logo icon — flat colour, no gradient per project rules */
        .sidebar-logo .logo-icon {
            width: 36px;
            height: 36px;
            background: #5c6af0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 12px;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .35);
            padding: 16px 8px 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, .65);
            text-decoration: none;
            transition: background .15s, color .15s;
            margin-bottom: 2px;
            min-height: 44px;
            touch-action: manipulation;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, .08);
            color: #fff;
        }

        .nav-link.active {
            background: rgba(92, 106, 240, .25);
            color: #a5bbfb;
            border-left: 3px solid #7f94f7;
            padding-left: 9px;
        }

        .nav-link .nav-icon {
            opacity: .7;
            flex-shrink: 0;
        }

        .nav-link.active .nav-icon {
            opacity: 1;
        }

        .nav-link:hover .nav-icon {
            opacity: 1;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        /* ── Top bar ──────────────────────────────────────── */
        #topbar {
            position: fixed;
            top: 0;
            right: 0;
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

        #topbar.sidebar-collapsed {
            left: 0;
        }

        /* ── Main content ─────────────────────────────────── */
        #main-content {
            margin-left: 256px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left .25s ease;
        }

        #main-content.sidebar-collapsed {
            margin-left: 0;
        }

        .page-content {
            padding: 28px;
        }

        /* ── Form inputs ──────────────────────────────────── */
        .ss-input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            /* prevents iOS zoom on focus */
            font-family: 'DM Sans', sans-serif;
            color: #1e293b;
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            min-height: 44px;
            appearance: none;
            -webkit-appearance: none;
        }

        .ss-input:focus {
            border-color: #7f94f7;
            box-shadow: 0 0 0 3px rgba(127, 148, 247, .15);
        }

        .ss-input::placeholder {
            color: #94a3b8;
        }

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
            min-height: 44px;
            touch-action: manipulation;
        }

        .ss-btn-primary {
            background: #5c6af0;
            color: #fff;
        }

        .ss-btn-primary:hover {
            background: #4549e4;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(92, 106, 240, .35);
        }

        .ss-btn-primary:active {
            transform: translateY(0);
            box-shadow: none;
        }

        .ss-btn-success {
            background: #10b981;
            color: #fff;
        }

        .ss-btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, .30);
        }

        .ss-btn-success:active {
            transform: translateY(0);
            box-shadow: none;
        }

        .ss-btn-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .ss-btn-danger:hover {
            background: #fecaca;
        }

        .ss-btn-ghost {
            background: #f1f5f9;
            color: #475569;
        }

        .ss-btn-ghost:hover {
            background: #e2e8f0;
        }

        /* ── Table wrapper ────────────────────────────────── */
        .ss-table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        /* ── Cards ────────────────────────────────────────── */
        .ss-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
        }

        .ss-card-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .ss-card-body {
            padding: 24px;
        }

        /* ── Badges ───────────────────────────────────────── */
        .ss-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .ss-badge-green {
            background: #dcfce7;
            color: #15803d;
        }

        .ss-badge-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .ss-badge-blue {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .ss-badge-amber {
            background: #fef3c7;
            color: #b45309;
        }

        /* ── Toast ────────────────────────────────────────── */
        #ss-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
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
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
            animation: toastIn .3s ease forwards;
            min-width: 260px;
            max-width: 380px;
        }

        .toast-success {
            background: #fff;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .toast-error {
            background: #fff;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .toast-info {
            background: #fff;
            border-left: 4px solid #5c6af0;
            color: #1e1b4b;
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
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

        /* ── Mobile overlay ───────────────────────────────── */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 39;
        }

        /* ── Responsive ───────────────────────────────────── */
        @media (max-width: 1023px) {
            #sidebar {
                transform: translateX(-256px);
                box-shadow: none;
            }

            #sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 4px 0 32px rgba(0, 0, 0, .25);
            }

            #topbar {
                left: 0 !important;
            }

            #main-content {
                margin-left: 0 !important;
            }

            .page-content {
                padding: 16px;
            }
        }

        @media (min-width: 1024px) {
            .mobile-only {
                display: none !important;
            }

            #sidebar {
                transform: translateX(0);
            }

            #sidebar.collapsed {
                transform: translateX(-256px);
            }
        }

        /* ── Scrollbar ────────────────────────────────────── */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 99px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
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
            <button onclick="toggleSidebar()" class="mobile-only" aria-label="Open menu" style="
            min-width:44px;min-height:44px;padding:8px;border-radius:8px;border:none;
            background:transparent;display:flex;align-items:center;justify-content:center;
            color:#64748b;cursor:pointer;transition:background .15s;" onmouseover="this.style.background='#f1f5f9'"
                onmouseout="this.style.background='transparent'">
                <i data-lucide="menu" style="width:20px;height:20px;"></i>
            </button>

            <!-- Collapse toggle — desktop only -->
            <button onclick="toggleSidebarDesktop()" aria-label="Collapse sidebar" style="
            min-width:44px;min-height:44px;padding:8px;border-radius:8px;border:none;
            background:transparent;display:none;align-items:center;justify-content:center;
            color:#64748b;cursor:pointer;transition:background .15s;" class="lg:flex hidden"
                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'"
                id="sidebar-desktop-toggle">
                <i data-lucide="panel-left-close" style="width:20px;height:20px;" id="sidebar-toggle-icon"></i>
            </button>

            <!-- Breadcrumb / page title -->
            <div style="flex:1;min-width:0;">
                <span id="topbar-title" style="font-size:14px;font-weight:600;color:#334155;"></span>
            </div>

            <!-- Right actions -->
            <div style="display:flex;align-items:center;gap:10px;">

                <!-- Group name badge -->
                <?php if (session()->get('group_name')): ?>
                    <span style="
                    display:none;
                    align-items:center;gap:6px;
                    font-size:12px;font-weight:500;color:#64748b;
                    background:#f1f5f9;padding:5px 12px;border-radius:999px;
                    white-space:nowrap;" class="sm-show" id="group-badge">
                        <i data-lucide="home" style="width:13px;height:13px;"></i>
                        <?= esc(session()->get('group_name')) ?>
                    </span>
                <?php endif; ?>

                <!-- Current month chip -->
                <span style="
                display:none;
                align-items:center;gap:5px;
                font-size:12px;font-weight:500;color:#64748b;
                background:#f1f5f9;padding:5px 12px;border-radius:999px;
                white-space:nowrap;" id="month-chip">
                    <i data-lucide="calendar" style="width:13px;height:13px;"></i>
                    <?= date('F Y') ?>
                </span>

                <!-- User avatar + dropdown -->
                <div style="position:relative;" id="user-menu-wrapper">
                    <button onclick="toggleUserMenu()" style="
                    display:flex;align-items:center;gap:8px;
                    padding:6px 10px;border-radius:8px;border:none;
                    background:transparent;cursor:pointer;
                    transition:background .15s;min-height:44px;" onmouseover="this.style.background='#f1f5f9'"
                        onmouseout="this.style.background='transparent'">
                        <div style="
                        width:28px;height:28px;border-radius:50%;
                        background:#5c6af0;
                        display:flex;align-items:center;justify-content:center;
                        color:#fff;font-size:12px;font-weight:700;flex-shrink:0;">
                            <?= strtoupper(substr((string) session()->get('name'), 0, 1)) ?>
                        </div>
                        <span id="topbar-username" style="
                        display:none;font-size:14px;font-weight:500;
                        color:#334155;max-width:120px;
                        overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= esc((string) session()->get('name')) ?>
                        </span>
                        <i data-lucide="chevron-down" style="width:14px;height:14px;color:#94a3b8;" id="user-chevron"></i>
                    </button>

                    <!-- Dropdown -->
                    <div id="user-dropdown" style="
                    display:none;position:absolute;right:0;top:100%;margin-top:8px;
                    width:208px;background:#fff;
                    border:1px solid #e2e8f0;border-radius:12px;
                    box-shadow:0 8px 24px rgba(0,0,0,.10);
                    z-index:50;overflow:hidden;padding:4px 0;
                    max-width:calc(100vw - 16px);">
                        <!-- User info -->
                        <div style="padding:12px 16px 10px;border-bottom:1px solid #f1f5f9;">
                            <div style="font-size:13px;font-weight:600;color:#0f172a;
                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?= esc((string) session()->get('name')) ?>
                            </div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:2px;text-transform:capitalize;">
                                <?= esc((string) (session()->get('role') ?? 'user')) ?>
                                <?php if (session()->get('group_name')): ?>
                                    · <?= esc((string) session()->get('group_name')) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Menu items -->
                        <a href="<?= base_url('/profile') ?>"
                            style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:#475569;text-decoration:none;transition:background .1s;"
                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                            <i data-lucide="user" style="width:15px;height:15px;"></i> Profile
                        </a>
                        <a href="#"
                            style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:#475569;text-decoration:none;transition:background .1s;"
                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                            <i data-lucide="settings" style="width:15px;height:15px;"></i> Settings
                        </a>
                        <!-- Pay via UPI -->
                        <button onclick="openQRModal();closeUserDropdown();" style="
                        width:100%;display:flex;align-items:center;gap:10px;
                        padding:10px 16px;font-size:13px;color:#475569;
                        border:none;background:transparent;cursor:pointer;
                        font-family:'DM Sans',sans-serif;text-align:left;
                        transition:background .1s;" onmouseover="this.style.background='#f8fafc'"
                            onmouseout="this.style.background=''">
                            <i data-lucide="qr-code" style="width:15px;height:15px;"></i> Pay via UPI
                        </button>
                        <div style="border-top:1px solid #f1f5f9;margin:4px 0;"></div>
                        <a href="<?= base_url('/auth/logout') ?>" style="
                        display:flex;align-items:center;gap:10px;
                        padding:10px 16px;font-size:13px;color:#dc2626;
                        text-decoration:none;transition:background .1s;" onmouseover="this.style.background='#fef2f2'"
                            onmouseout="this.style.background=''">
                            <i data-lucide="log-out" style="width:15px;height:15px;"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- ═══════════════════════════════════════════════════════
         UPI / QR PAYMENT MODAL
    ════════════════════════════════════════════════════════ -->
        <?php
        $payment = config('Payment');
        $upiId = esc($payment->upiId);
        $payeeName = esc($payment->payeeName);
        $payNote = esc($payment->paymentNote);
        $upiString = 'upi://pay?pa=' . rawurlencode($payment->upiId)
            . '&pn=' . rawurlencode($payment->payeeName)
            . '&tn=' . rawurlencode($payment->paymentNote)
            . '&cu=INR';
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($upiString);
        ?>

        <!-- Backdrop -->
        <div id="qr-backdrop" onclick="closeQRModal()" style="
        display:none;position:fixed;inset:0;
        background:rgba(15,23,42,.45);z-index:200;
        backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);
    "></div>

        <!-- Modal -->
        <div id="qr-modal" style="
        display:none;position:fixed;
        top:50%;left:50%;
        transform:translate(-50%,-50%) scale(0.97);
        width:calc(100% - 32px);max-width:340px;
        background:#fff;border-radius:20px;
        box-shadow:0 20px 60px rgba(0,0,0,.18);
        z-index:201;opacity:0;
        transition:transform .2s ease, opacity .2s ease;
        text-align:center;">

            <!-- Header -->
            <div
                style="display:flex;align-items:center;justify-content:space-between;padding:18px 20px 14px;border-bottom:1px solid #f1f5f9;">
                <div style="display:flex;align-items:center;gap:9px;">
                    <div
                        style="width:32px;height:32px;border-radius:8px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="qr-code" style="width:15px;height:15px;color:#15803d;"></i>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-size:14px;font-weight:700;color:#0f172a;">Pay via UPI</div>
                        <div style="font-size:11px;color:#94a3b8;">Scan with any UPI app</div>
                    </div>
                </div>
                <button onclick="closeQRModal()" style="
                width:30px;height:30px;border-radius:8px;
                background:#f1f5f9;border:none;cursor:pointer;
                display:flex;align-items:center;justify-content:center;
                color:#64748b;transition:background .15s;flex-shrink:0;" onmouseover="this.style.background='#e2e8f0'"
                    onmouseout="this.style.background='#f1f5f9'">
                    <i data-lucide="x" style="width:15px;height:15px;"></i>
                </button>
            </div>

            <!-- Content -->
            <div style="padding:20px 24px 24px;">
                <p style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 4px;"><?= $payeeName ?></p>
                <p style="font-size:12px;color:#64748b;margin:0 0 16px;">SmartSplit Household</p>

                <!-- QR image -->
                <div
                    style="display:inline-block;padding:10px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;margin-bottom:16px;">
                    <img src="<?= $qrUrl ?>" alt="UPI QR Code" width="200" height="200"
                        style="display:block;border-radius:6px;"
                        onerror="this.parentElement.innerHTML='<div style=\'width:200px;height:200px;display:flex;align-items:center;justify-content:center;background:#f8fafc;border-radius:6px;\'><span style=\'font-size:12px;color:#94a3b8;text-align:center;padding:16px;\'>QR unavailable.<br>Use UPI ID below.</span></div>'">
                </div>

                <!-- UPI ID chip -->
                <div
                    style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:16px;">
                    <div style="text-align:left;min-width:0;">
                        <div
                            style="font-size:10px;font-weight:600;color:#94a3b8;letter-spacing:.06em;text-transform:uppercase;margin-bottom:2px;">
                            UPI ID</div>
                        <div id="upi-id-text"
                            style="font-size:14px;font-weight:600;color:#0f172a;font-family:'JetBrains Mono',monospace;word-break:break-all;">
                            <?= $upiId ?>
                        </div>
                    </div>
                    <button onclick="copyUpiId()" id="copy-btn" style="
                    flex-shrink:0;padding:6px 12px;border-radius:7px;
                    background:#e0e7ff;color:#4338ca;
                    border:none;cursor:pointer;
                    font-size:12px;font-weight:600;
                    font-family:'DM Sans',sans-serif;
                    display:flex;align-items:center;gap:4px;
                    transition:background .15s;min-height:32px;" onmouseover="this.style.background='#c7d2fe'"
                        onmouseout="this.style.background='#e0e7ff'">
                        <i data-lucide="copy" style="width:12px;height:12px;" id="copy-icon"></i>
                        <span id="copy-text">Copy</span>
                    </button>
                </div>

                <!-- Supported apps -->
                <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                    <span style="font-size:11px;color:#94a3b8;">Works with</span>
                    <?php foreach (['GPay', 'PhonePe', 'Paytm', 'BHIM', 'Amazon Pay'] as $app): ?>
                        <span
                            style="font-size:10px;font-weight:600;padding:2px 7px;border-radius:999px;background:#f1f5f9;color:#64748b;"><?= $app ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

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
        <!-- Not logged in — full-page centred layout -->
        <div class="min-h-screen flex items-center justify-center bg-surface-50">
            <?= $this->renderSection('content') ?>
        </div>
    <?php endif; ?>

    <!-- Toast container -->
    <div id="ss-toast"></div>

    <!-- ── ssConfirm modal ──────────────────────────────────────────── -->
    <div id="ssConfirmBackdrop"
        style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:300;transition:opacity .18s;"
        onclick="ssConfirmCancel()"></div>
    <div id="ssConfirmModal"
        style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.97);z-index:301;width:100%;max-width:400px;padding:0 16px;transition:opacity .18s,transform .18s;opacity:0;">
        <div style="background:#fff;border-radius:16px;padding:28px 24px 24px;">
            <div style="display:flex;flex-direction:column;align-items:center;text-align:center;gap:12px;">
                <div
                    style="width:48px;height:48px;border-radius:12px;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="triangle-alert" style="width:22px;height:22px;color:#dc2626;"></i>
                </div>
                <div>
                    <div id="ssConfirmTitle" style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:6px;">
                    </div>
                    <div id="ssConfirmMessage" style="font-size:13px;color:#64748b;line-height:1.5;"></div>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:24px;">
                <button onclick="ssConfirmCancel()" class="ss-btn ss-btn-ghost" style="flex:1;justify-content:center;">
                    <span id="ssConfirmCancelText">Cancel</span>
                </button>
                <button onclick="ssConfirmProceed()" id="ssConfirmBtn" class="ss-btn"
                    style="flex:1;justify-content:center;background:#dc2626;color:#fff;border:none;">
                    <span id="ssConfirmBtnText">Delete</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
     GLOBAL JAVASCRIPT
════════════════════════════════════════════════════════ -->
    <script>
        // Run Lucide on initial paint
        lucide.createIcons();

        // Show topbar chips on wider screens — done in JS to avoid
        // a flash of hidden content before CSS loads.
        (function () {
            var w = window.innerWidth;
            var monthChip = document.getElementById('month-chip');
            var groupBadge = document.getElementById('group-badge');
            var username = document.getElementById('topbar-username');
            var chevron = document.getElementById('user-chevron');
            if (w >= 640) {
                if (monthChip) { monthChip.style.display = 'inline-flex'; }
                if (groupBadge) { groupBadge.style.display = 'inline-flex'; }
                if (username) { username.style.display = 'block'; }
            }
            // Show desktop sidebar toggle button
            var desktopToggle = document.getElementById('sidebar-desktop-toggle');
            if (desktopToggle && w >= 1024) desktopToggle.style.display = 'flex';
        })();

        // Sync topbar title from active nav link text
        (function () {
            var active = document.querySelector('.nav-link.active');
            var el = document.getElementById('topbar-title');
            if (active && el) {
                var navText = active.querySelector('.nav-text');
                el.textContent = navText ? navText.textContent.trim() : '';
            }
        })();

        // ── Sidebar — mobile ──────────────────────────────────
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebar-overlay');
            var isOpen = sidebar.classList.contains('mobile-open');
            sidebar.classList.toggle('mobile-open', !isOpen);
            overlay.style.display = isOpen ? 'none' : 'block';
            document.body.style.overflow = isOpen ? '' : 'hidden';
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('mobile-open');
            document.getElementById('sidebar-overlay').style.display = 'none';
            document.body.style.overflow = '';
        }

        // Close sidebar on nav-link tap (mobile)
        document.querySelectorAll('#sidebar .nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 1024) closeSidebar();
            });
        });

        // Close sidebar on Escape (mobile)
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (window.innerWidth < 1024) closeSidebar();
                closeQRModal();
            }
        });

        // Swipe left to close sidebar on mobile
        (function () {
            var startX = 0;
            var sidebar = document.getElementById('sidebar');
            sidebar.addEventListener('touchstart', function (e) {
                startX = e.touches[0].clientX;
            }, { passive: true });
            sidebar.addEventListener('touchend', function (e) {
                var dx = e.changedTouches[0].clientX - startX;
                if (dx < -60 && window.innerWidth < 1024) closeSidebar();
            }, { passive: true });
        })();

        // ── Sidebar — desktop collapse ────────────────────────
        var sidebarCollapsed = false;
        function toggleSidebarDesktop() {
            sidebarCollapsed = !sidebarCollapsed;
            document.getElementById('sidebar').classList.toggle('collapsed', sidebarCollapsed);
            document.getElementById('topbar').classList.toggle('sidebar-collapsed', sidebarCollapsed);
            document.getElementById('main-content').classList.toggle('sidebar-collapsed', sidebarCollapsed);
            window.setLucideIcon('sidebar-toggle-icon', sidebarCollapsed ? 'panel-left-open' : 'panel-left-close');
        }

        // ── User dropdown ─────────────────────────────────────
        function toggleUserMenu() {
            var dd = document.getElementById('user-dropdown');
            dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        }
        function closeUserDropdown() {
            document.getElementById('user-dropdown').style.display = 'none';
        }
        // Click outside closes dropdown
        document.addEventListener('click', function (e) {
            var wrapper = document.getElementById('user-menu-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                closeUserDropdown();
            }
        });

        // ── Toast ─────────────────────────────────────────────
        window.ssToast = function (message, type) {
            type = type || 'success';
            var container = document.getElementById('ss-toast');
            var icons = { success: 'check-circle', error: 'x-circle', info: 'info' };
            var toast = document.createElement('div');
            toast.className = 'toast-item toast-' + type;
            toast.innerHTML = '<i data-lucide="' + (icons[type] || 'info') + '" style="width:18px;height:18px;flex-shrink:0;"></i>'
                + '<span>' + message + '</span>';
            container.appendChild(toast);
            lucide.createIcons({ nodes: [toast] });
            setTimeout(function () {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(20px)';
                toast.style.transition = 'all .3s ease';
                setTimeout(function () { toast.remove(); }, 300);
            }, 3500);
        };

        // ── setLucideIcon — safe icon swap after SVG render ───
        // Direct setAttribute() + createIcons() does NOT re-render once
        // Lucide has already replaced <i> with <svg>. This helper replaces
        // the element with a fresh <i> before calling createIcons on it.
        window.setLucideIcon = function (id, iconName) {
            var el = document.getElementById(id);
            if (!el) return;
            var fresh = document.createElement('i');
            var existingStyle = el.getAttribute('style') || '';
            fresh.setAttribute('data-lucide', iconName);
            fresh.setAttribute('id', id);
            if (existingStyle) fresh.setAttribute('style', existingStyle);
            el.parentNode.replaceChild(fresh, el);
            lucide.createIcons({ nodes: [fresh] });
        };

        // ── QR Payment modal ──────────────────────────────────
        window.openQRModal = function () {
            var backdrop = document.getElementById('qr-backdrop');
            var modal = document.getElementById('qr-modal');
            if (!backdrop || !modal) return;
            backdrop.style.display = 'block';
            modal.style.display = 'block';
            requestAnimationFrame(function () {
                modal.style.opacity = '1';
                modal.style.transform = 'translate(-50%,-50%) scale(1)';
            });
        };
        window.closeQRModal = function () {
            var modal = document.getElementById('qr-modal');
            var backdrop = document.getElementById('qr-backdrop');
            if (!modal || !backdrop) return;
            modal.style.opacity = '0';
            modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
            setTimeout(function () {
                modal.style.display = 'none';
                backdrop.style.display = 'none';
            }, 180);
        };

        // ── Copy UPI ID ───────────────────────────────────────
        // Uses setLucideIcon() to safely swap the copy icon after SVG render.
        window.copyUpiId = function () {
            var upiText = (document.getElementById('upi-id-text') || {}).textContent;
            if (!upiText) return;
            upiText = upiText.trim();

            function onCopied() {
                var btn = document.getElementById('copy-btn');
                var text = document.getElementById('copy-text');
                if (!btn || !text) return;
                text.textContent = 'Copied!';
                btn.style.background = '#dcfce7';
                btn.style.color = '#15803d';
                // FIX: use setLucideIcon — icon is already rendered as SVG
                window.setLucideIcon('copy-icon', 'check');
                setTimeout(function () {
                    text.textContent = 'Copy';
                    btn.style.background = '#e0e7ff';
                    btn.style.color = '#4338ca';
                    window.setLucideIcon('copy-icon', 'copy');
                }, 2000);
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(upiText).then(onCopied).catch(function () {
                    fallbackCopy(upiText);
                });
            } else {
                fallbackCopy(upiText);
            }
        };

        function fallbackCopy(text) {
            var el = document.createElement('textarea');
            el.value = text;
            el.style.cssText = 'position:fixed;opacity:0;pointer-events:none;';
            document.body.appendChild(el);
            el.select();
            try {
                document.execCommand('copy');
                ssToast('UPI ID copied!', 'success');
            } catch (e) {
                ssToast('Copy failed — please copy manually.', 'error');
            }
            document.body.removeChild(el);
        }

        var _ssConfirmCallback = null;

        function ssConfirm(opts) {
            document.getElementById('ssConfirmTitle').textContent = opts.title || 'Are you sure?';
            document.getElementById('ssConfirmMessage').textContent = opts.message || 'This action cannot be undone.';
            document.getElementById('ssConfirmBtnText').textContent = opts.confirmText || 'Delete';
            document.getElementById('ssConfirmCancelText').textContent = opts.cancelText || 'Cancel';
            _ssConfirmCallback = opts.onConfirm || null;

            var backdrop = document.getElementById('ssConfirmBackdrop');
            var modal = document.getElementById('ssConfirmModal');
            backdrop.style.opacity = '0';
            modal.style.opacity = '0';
            backdrop.style.display = 'block';
            modal.style.display = 'block';
            lucide.createIcons();
            requestAnimationFrame(function () {
                backdrop.style.opacity = '1';
                modal.style.opacity = '1';
                modal.style.transform = 'translate(-50%,-50%) scale(1)';
            });

            document.addEventListener('keydown', _ssConfirmEsc);
        }

        function ssConfirmCancel() {
            var backdrop = document.getElementById('ssConfirmBackdrop');
            var modal = document.getElementById('ssConfirmModal');
            backdrop.style.opacity = '0';
            modal.style.opacity = '0';
            modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
            setTimeout(function () {
                backdrop.style.display = 'none';
                modal.style.display = 'none';
            }, 180);
            document.removeEventListener('keydown', _ssConfirmEsc);
            _ssConfirmCallback = null;
        }

        function ssConfirmProceed() {
            var cb = _ssConfirmCallback;  // ← grab reference before cancel nulls it
            ssConfirmCancel();
            if (typeof cb === 'function') cb();
        }

        function _ssConfirmEsc(e) {
            if (e.key === 'Escape') ssConfirmCancel();
        }
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>
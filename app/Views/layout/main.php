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

    <!-- App javascript — externalized so the browser caches it across
         page navigations instead of re-downloading it with every
         request (bump ?v= when app.css changes) -->
    <script src="<?= base_url('assets/js/app.js') ?>?v=2"></script>

    <!-- App stylesheet — externalized so the browser caches it across
         page navigations instead of re-downloading it with every
         request (bump ?v= when app.css changes) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=2">
</head>

<body class="h-full bg-surface-50">

    <?php
    // Session values are read once here — every downstream usage below
    // reads from these locals instead of re-calling session()->get()
    // and re-casting inline.
    $isLoggedIn = session()->get('isLoggedIn') === true;
    $userName   = (string) (session()->get('name') ?? '');
    $userRole   = (string) (session()->get('role') ?? 'user');
    $groupName  = (string) (session()->get('group_name') ?? '');
    ?>

    <?php if ($isLoggedIn): ?>

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
            <button onclick="toggleSidebar()" class="icon-btn mobile-only" aria-label="Open menu">
                <i data-lucide="menu"></i>
            </button>

            <!-- Collapse toggle — desktop only -->
            <button onclick="toggleSidebarDesktop()" aria-label="Collapse sidebar"
                class="icon-btn hidden lg:flex" id="sidebar-desktop-toggle">
                <i data-lucide="panel-left-close" id="sidebar-toggle-icon"></i>
            </button>

            <!-- Breadcrumb / page title -->
            <div class="flex-1 min-w-0">
                <span id="topbar-title" class="text-sm font-semibold text-surface-700"></span>
            </div>

            <!-- Right actions -->
            <div class="flex items-center gap-2.5">

                <!-- Group name badge -->
                <?php if ($groupName !== ''): ?>
                    <span class="chip sm-show" id="group-badge">
                        <i data-lucide="home"></i>
                        <?= esc($groupName) ?>
                    </span>
                <?php endif; ?>

                <!-- Current month chip -->
                <span class="chip" id="month-chip">
                    <i data-lucide="calendar"></i>
                    <?= date('F Y') ?>
                </span>

                <!-- User avatar + dropdown -->
                <div class="relative" id="user-menu-wrapper">
                    <button onclick="toggleUserMenu()" class="user-menu-btn">
                        <div class="avatar-circle">
                            <?= esc(strtoupper(substr($userName, 0, 1))) ?>
                        </div>
                        <span id="topbar-username" class="hidden text-sm font-medium text-surface-700 max-w-[120px] truncate">
                            <?= esc($userName) ?>
                        </span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-surface-400" id="user-chevron"></i>
                    </button>

                    <!-- Dropdown -->
                    <div id="user-dropdown" class="dropdown-menu">
                        <!-- User info -->
                        <div class="px-4 pt-3 pb-2.5 border-b border-surface-100">
                            <div class="text-[13px] font-semibold text-surface-900 truncate">
                                <?= esc($userName) ?>
                            </div>
                            <div class="text-[11px] text-surface-400 mt-0.5 capitalize">
                                <?= esc($userRole) ?>
                                <?php if ($groupName !== ''): ?>
                                    · <?= esc($groupName) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Menu items -->
                        <a href="<?= base_url('/profile') ?>" class="dropdown-item">
                            <i data-lucide="user" class="w-[15px] h-[15px]"></i> Profile
                        </a>
                        <a href="#" class="dropdown-item">
                            <i data-lucide="settings" class="w-[15px] h-[15px]"></i> Settings
                        </a>
                        <!-- Pay via UPI -->
                        <button onclick="openQRModal();closeUserDropdown();" class="dropdown-item">
                            <i data-lucide="qr-code" class="w-[15px] h-[15px]"></i> Pay via UPI
                        </button>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('/auth/logout') ?>" class="dropdown-item dropdown-item-danger">
                            <i data-lucide="log-out" class="w-[15px] h-[15px]"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- ═══════════════════════════════════════════════════════
         UPI / QR PAYMENT MODAL
    ════════════════════════════════════════════════════════ -->
        <?php
        $payment   = config('Payment');
        $upiId     = esc($payment->upiId);
        $payeeName = esc($payment->payeeName);
        $upiString = 'upi://pay?pa=' . rawurlencode($payment->upiId)
            . '&pn=' . rawurlencode($payment->payeeName)
            . '&tn=' . rawurlencode($payment->paymentNote)
            . '&cu=INR';
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($upiString);
        ?>

        <!-- Backdrop -->
        <div id="qr-backdrop" class="modal-backdrop" onclick="closeQRModal()"></div>

        <!-- Modal -->
        <div id="qr-modal" class="modal-panel">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 pt-[18px] pb-3.5 border-b border-surface-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                        <i data-lucide="qr-code" class="w-[15px] h-[15px] text-green-700"></i>
                    </div>
                    <div class="text-left">
                        <div class="text-sm font-bold text-surface-900">Pay via UPI</div>
                        <div class="text-[11px] text-surface-400">Scan with any UPI app</div>
                    </div>
                </div>
                <button onclick="closeQRModal()" class="icon-btn !min-w-[30px] !min-h-[30px] !w-[30px] !h-[30px] bg-surface-100">
                    <i data-lucide="x" class="w-[15px] h-[15px]"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="px-6 pt-5 pb-6">
                <p class="text-[15px] font-bold text-surface-900 mb-1"><?= $payeeName ?></p>
                <p class="text-xs text-surface-500 mb-4">SmartSplit Household</p>

                <!-- QR image -->
                <div class="inline-block p-2.5 border border-surface-200 rounded-xl bg-white mb-4">
                    <img src="<?= $qrUrl ?>" alt="UPI QR Code" width="200" height="200" class="block rounded-md"
                        onerror="this.parentElement.innerHTML='<div style=\'width:200px;height:200px;display:flex;align-items:center;justify-content:center;background:#f8fafc;border-radius:6px;\'><span style=\'font-size:12px;color:#94a3b8;text-align:center;padding:16px;\'>QR unavailable.<br>Use UPI ID below.</span></div>'">
                </div>

                <!-- UPI ID chip -->
                <div class="flex items-center justify-between gap-2 px-3.5 py-2.5 bg-surface-50 border border-surface-200 rounded-[10px] mb-4">
                    <div class="text-left min-w-0">
                        <div class="text-[10px] font-semibold text-surface-400 tracking-wider uppercase mb-0.5">UPI ID</div>
                        <div id="upi-id-text" class="text-sm font-semibold text-surface-900 font-mono break-all">
                            <?= $upiId ?>
                        </div>
                    </div>
                    <button onclick="copyUpiId()" id="copy-btn"
                        class="shrink-0 px-3 py-1.5 rounded-[7px] bg-indigo-100 text-indigo-700 border-none cursor-pointer text-xs font-semibold flex items-center gap-1 transition-colors min-h-[32px] hover:bg-indigo-200">
                        <i data-lucide="copy" class="w-3 h-3" id="copy-icon"></i>
                        <span id="copy-text">Copy</span>
                    </button>
                </div>

                <!-- Supported apps -->
                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                    <span class="text-[11px] text-surface-400">Works with</span>
                    <?php foreach (['GPay', 'PhonePe', 'Paytm', 'BHIM', 'Amazon Pay'] as $app): ?>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-surface-100 text-surface-500"><?= esc($app) ?></span>
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
    <div id="ssConfirmBackdrop" class="modal-backdrop" style="transition:opacity .18s;" onclick="ssConfirmCancel()"></div>
    <div id="ssConfirmModal" class="modal-panel" style="transition:opacity .18s,transform .18s;">
        <div class="bg-white rounded-2xl px-6 pt-7 pb-6">
            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                    <i data-lucide="triangle-alert" class="w-[22px] h-[22px] text-red-600"></i>
                </div>
                <div>
                    <div id="ssConfirmTitle" class="text-base font-bold text-surface-900 mb-1.5"></div>
                    <div id="ssConfirmMessage" class="text-[13px] text-surface-500 leading-relaxed"></div>
                </div>
            </div>
            <div class="flex gap-2.5 mt-6">
                <button onclick="ssConfirmCancel()" class="ss-btn ss-btn-ghost flex-1 justify-center">
                    <span id="ssConfirmCancelText">Cancel</span>
                </button>
                <button onclick="ssConfirmProceed()" id="ssConfirmBtn" class="ss-btn flex-1 justify-center bg-red-600 text-white border-none">
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
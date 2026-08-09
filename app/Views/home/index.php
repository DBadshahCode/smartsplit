<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">
            Good <?php
                    $h = (int) date('H');
                    echo $h < 12 ? 'morning' : ($h < 17 ? 'afternoon' : 'evening');
                    ?>, <?= esc(session()->get('name')) ?> 👋
        </p>
    </div>
    <a href="<?= base_url('/finaldistribution') ?>" class="ss-btn ss-btn-primary no-underline">
        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
        <span>View Distribution</span>
    </a>
</div>

<!-- Stat cards -->
<div id="dash-stats-grid" class="grid gap-4 mb-7" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">

    <div class="ss-card stat-card">
        <div class="stat-card-head">
            <span class="stat-card-label">Total Expenses</span>
            <div class="stat-icon-box bg-pink-100">
                <i data-lucide="receipt" class="w-4 h-4 text-pink-700"></i>
            </div>
        </div>
        <div class="stat-value" id="stat-expenses">—</div>
        <div class="stat-caption">All recorded expenses</div>
    </div>

    <!-- This Month Expenses count — filtered by billing_month -->
    <div class="ss-card stat-card">
        <div class="stat-card-head">
            <span class="stat-card-label">This Month's Expenses</span>
            <div class="stat-icon-box bg-indigo-100">
                <i data-lucide="calendar-range" class="w-4 h-4 text-indigo-700"></i>
            </div>
        </div>
        <div class="stat-value" id="stat-month-count">—</div>
        <div class="stat-caption">Billing month: <span id="stat-month-label"><?= date('M Y') ?></span></div>
    </div>

    <?php if (session()->get('role') === 'admin'): ?>
        <div class="ss-card stat-card">
            <div class="stat-card-head">
                <span class="stat-card-label">Total Users</span>
                <div class="stat-icon-box bg-violet-100">
                    <i data-lucide="users" class="w-4 h-4 text-violet-600"></i>
                </div>
            </div>
            <div class="stat-value" id="stat-users">—</div>
            <div class="stat-caption">Registered roommates</div>
        </div>
    <?php endif; ?>

    <div class="ss-card stat-card">
        <div class="stat-card-head">
            <span class="stat-card-label">Current Month</span>
            <div class="stat-icon-box bg-green-100">
                <i data-lucide="calendar" class="w-4 h-4 text-green-700"></i>
            </div>
        </div>
        <div class="stat-value" style="font-size:22px;letter-spacing:-0.02em;"><?= date('M Y') ?></div>
        <div class="stat-caption"><?= date('l, d F') ?></div>
    </div>

</div>

<!-- Main grid -->
<div id="dashboard-grid" class="grid gap-5 items-start grid-cols-[1fr_300px]">

    <!-- Recent Expenses -->
    <div class="ss-card">
        <div class="ss-card-header flex items-center justify-between gap-3 flex-wrap">
            <div>
                <h2 class="text-[15px] font-bold text-surface-900 m-0">Recent Expenses</h2>
                <p class="text-[13px] text-surface-400 mt-[3px] mb-0">Most recent 5 expenses</p>
            </div>
            <a href="<?= base_url('/expense') ?>"
                class="text-[13px] font-semibold text-brand-500 no-underline flex items-center gap-1 whitespace-nowrap">
                View all <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        <div class="overflow-x-auto" style="-webkit-overflow-scrolling:touch;">
            <table class="recent-expenses-table w-full border-collapse" style="min-width:400px;">
                <thead>
                    <tr class="bg-surface-50">
                        <th class="mini-table-th">Type</th>
                        <th class="mini-table-th">Amount</th>
                        <th class="mini-table-th">Paid By</th>
                        <th class="mini-table-th">Billing Month</th>
                    </tr>
                </thead>
                <tbody id="recent-expenses-body">
                    <tr>
                        <td colspan="4" class="mini-table-empty-td">
                            <i data-lucide="loader" class="w-[18px] h-[18px] inline-block mb-1.5"></i>
                            <div>Loading…</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right column -->
    <div class="flex flex-col gap-4">

        <!-- Quick Actions -->
        <div class="ss-card">
            <div class="ss-card-header">
                <h2 class="text-[15px] font-bold text-surface-900 m-0">Quick Actions</h2>
            </div>
            <div class="py-3 px-2">

                <?php if (session()->get('role') === 'admin'): ?>
                    <a href="<?= base_url('/user') ?>" class="qa-link">
                        <div class="qa-icon-box bg-violet-100">
                            <i data-lucide="users" class="w-4 h-4 text-violet-600"></i>
                        </div>
                        <div>
                            <div class="qa-title">Manage Users</div>
                            <div class="qa-subtitle">Add or remove members</div>
                        </div>
                        <i data-lucide="chevron-right" class="qa-chevron"></i>
                    </a>
                <?php endif; ?>

                <a href="<?= base_url('/expense') ?>" class="qa-link">
                    <div class="qa-icon-box bg-pink-100">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-pink-700"></i>
                    </div>
                    <div>
                        <div class="qa-title">Add Expense</div>
                        <div class="qa-subtitle">Record a new expense</div>
                    </div>
                    <i data-lucide="chevron-right" class="qa-chevron"></i>
                </a>

                <a href="<?= base_url('/absentday') ?>" class="qa-link">
                    <div class="qa-icon-box bg-indigo-100">
                        <i data-lucide="calendar-x" class="w-4 h-4 text-indigo-700"></i>
                    </div>
                    <div>
                        <div class="qa-title">Mark Absence</div>
                        <div class="qa-subtitle">Record absent days</div>
                    </div>
                    <i data-lucide="chevron-right" class="qa-chevron"></i>
                </a>

                <a href="<?= base_url('/finaldistribution') ?>" class="qa-link">
                    <div class="qa-icon-box bg-green-100">
                        <i data-lucide="bar-chart-2" class="w-4 h-4 text-green-700"></i>
                    </div>
                    <div>
                        <div class="qa-title">
                            <?= session()->get('role') === 'admin' ? 'Generate Distribution' : 'View Distribution' ?>
                        </div>
                        <div class="qa-subtitle">
                            <?= session()->get('role') === 'admin' ? 'Calculate monthly split' : 'View monthly split' ?>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="qa-chevron"></i>
                </a>

            </div>
        </div>

        <!-- This month card — total driven by billing_month -->
        <div id="dash-billing-card" class="ss-card stat-card bg-brand-950 border-none">
            <div class="flex items-center gap-2 mb-4">
                <i data-lucide="zap" class="w-4 h-4 text-brand-300"></i>
                <span class="text-[13px] font-semibold text-brand-300">
                    Billing Month: <span id="stat-month-billing-label"><?= date('M Y') ?></span>
                </span>
            </div>
            <div class="stat-value-lg text-[28px] font-bold text-white font-mono" style="letter-spacing:-0.03em;"
                id="stat-month-total">—</div>
            <div class="text-xs text-white/50 mt-1">Total billed this month</div>

            <!-- Breakdown bar: filled proportionally by billing_month expenses vs all -->
            <div class="mt-3.5">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-[11px] text-white/45">Share of all-time total</span>
                    <span class="text-[11px] font-semibold text-brand-300" id="stat-month-pct">—</span>
                </div>
                <div class="h-1 bg-white/10 rounded-full overflow-hidden">
                    <div id="stat-month-bar" class="h-full rounded-full" style="width:0%;background:#818cf8;transition:width .6s ease;"></div>
                </div>
            </div>

            <div class="mt-4 pt-4" style="border-top:1px solid rgba(255,255,255,.1);">
                <a href="<?= base_url('/finaldistribution') ?>"
                    class="text-[13px] font-semibold text-brand-400 no-underline inline-flex items-center gap-1">
                    View breakdown <i data-lucide="arrow-right" class="w-[13px] h-[13px]"></i>
                </a>
            </div>
        </div>

    </div>
</div>

<!-- The @media style block below this in the original file (mobile grid
     collapse, .stat-value / .stat-value-lg overrides) is untouched — every
     class it targets (#dash-stats-grid, .recent-expenses-table, .stat-value,
     #dash-billing-card, .stat-value-lg) still exists with the same name. -->

<style>
    @media (max-width: 768px) {
        #dashboard-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* ── Mobile responsive pass ─────────────────────────────────────── */
    @media (max-width: 640px) {
        .page-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 14px !important;
        }

        .page-header .ss-btn-primary {
            width: 100%;
            justify-content: center;
        }

        #dash-stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
        }

        #dash-stats-grid .ss-card {
            padding: 14px 14px !important;
        }

        #dash-stats-grid .stat-value {
            font-size: 20px !important;
        }

        #dash-billing-card {
            padding: 18px !important;
        }

        #dash-billing-card .stat-value-lg {
            font-size: 22px !important;
        }
    }

    @media (max-width: 400px) {
        #dash-stats-grid {
            grid-template-columns: 1fr !important;
        }

        .recent-expenses-table {
            min-width: 340px !important;
        }

        .recent-expenses-table th,
        .recent-expenses-table td {
            padding: 8px 10px !important;
            font-size: 12px !important;
        }
    }
</style>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    lucide.createIcons();

    <?php if (session()->getFlashdata('error')): ?>
        ssToast('<?= addslashes(session()->getFlashdata('error')) ?>', 'error');
    <?php endif; ?>

    function fmt(n) {
        return '₹' + parseFloat(n || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Format a YYYY-MM billing_month string to a human label e.g. "May 2026"
    function fmtBillingMonth(bm) {
        if (!bm) return '—';
        var parts = String(bm).split('-');
        if (parts.length < 2) return bm;
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var mo = parseInt(parts[1], 10);
        return (months[mo - 1] || parts[1]) + ' ' + parts[0];
    }

    // Fallback to calendar month until the latest-generated lookup resolves
    var currentMonth = '<?= date('Y-m') ?>';

    // ── Resolve the actual "billing month" to use for dashboard stats ────────
    // Mirrors finaldistribution/index.php — always reflects the most recently
    // generated distribution month, not just today's calendar month.
    $.get('/finaldistribution/getLatestMonth', function(res) {
        if (res && res.month) {
            currentMonth = res.month;
            var label = fmtBillingMonth(currentMonth);
            var el1 = document.getElementById('stat-month-label');
            var el2 = document.getElementById('stat-month-billing-label');
            if (el1) el1.textContent = label;
            if (el2) el2.textContent = label;
        }
    }).always(function() {
        // Only fetch expenses once we know which billing month to filter by
        loadDashboardExpenses();
    });

    <?php if (session()->get('role') === 'admin'): ?>
        $.get('/user/getUsers', function(res) {
            var el = document.getElementById('stat-users');
            if (el) el.textContent = (res.data || []).length;
        });
    <?php endif; ?>

    // ══════════════════════════════════════════════════════════════
    // loadDashboardExpenses() — only the parts that build HTML strings
    // changed. Row hover now comes from `.recent-expenses-table tbody
    // tr:hover` in main.php (batch 1 of this pass), so the per-row
    // onmouseover/onmouseout pair is gone. Badges reuse the same
    // .ss-badge-pink / .ss-badge-indigo / .ss-badge-amber classes
    // already used on the expense list — "Pending" here now matches
    // "Pending" there exactly, instead of a slightly different yellow.
    // ══════════════════════════════════════════════════════════════
    function loadDashboardExpenses() {
        $.get('/expense/getExpenses', function(res) {
            var all = res.data || [];

            // ── Stat: total expense count (all time) ────────────────────────────
            document.getElementById('stat-expenses').textContent = all.length;

            // ── Filter by billing_month for this-month stats ─────────────────────
            // billing_month is the canonical field — do NOT use from_date for this.
            var thisMonth = all.filter(function(e) {
                return (e.billing_month || '') === currentMonth;
            });

            // ── Stat: count of expenses in current billing_month ────────────────
            document.getElementById('stat-month-count').textContent = thisMonth.length;

            // ── Stat: total amount billed this month ─────────────────────────────
            var monthTotal = thisMonth.reduce(function(sum, e) {
                return sum + parseFloat(e.amount || 0);
            }, 0);
            document.getElementById('stat-month-total').textContent = fmt(monthTotal);

            // ── Progress bar: this month's total vs all-time total ───────────────
            var allTotal = all.reduce(function(sum, e) {
                return sum + parseFloat(e.amount || 0);
            }, 0);
            var pct = allTotal > 0 ? Math.min(100, (monthTotal / allTotal) * 100) : 0;
            document.getElementById('stat-month-pct').textContent = pct.toFixed(1) + '%';
            // Defer so CSS transition fires after paint
            setTimeout(function() {
                document.getElementById('stat-month-bar').style.width = pct.toFixed(1) + '%';
            }, 100);

            // ── Recent expenses table (5 most recent, already DESC from API) ─────
            var recent = all.slice(0, 5);
            var tbody = document.getElementById('recent-expenses-body');

            if (recent.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="mini-table-empty-td">No expenses recorded yet</td></tr>';
                return;
            }

            tbody.innerHTML = recent.map(function(e) {
                var paidBy = e.paid_by_name ?
                    '<span class="text-[13px] text-surface-700">' + e.paid_by_name + '</span>' :
                    '<span class="ss-badge ss-badge-amber">Pending</span>';

                // Billing month pill — highlight if it matches the current billing month
                var bm = e.billing_month || '';
                var bmLabel = fmtBillingMonth(bm);
                var bmIsCurrentMonth = bm === currentMonth;
                var bmPill = '<span class="ss-badge font-mono ' + (bmIsCurrentMonth ? 'ss-badge-indigo' : 'ss-badge-gray') + '">' +
                    bmLabel + '</span>';

                return '<tr>' +
                    '<td class="mini-table-td font-medium">' +
                    '<span class="ss-badge ss-badge-pink">' + (e.expense_type || '—') + '</span>' +
                    '</td>' +
                    '<td class="mini-table-td font-mono whitespace-nowrap" style="font-weight:700;color:#0f172a;">' +
                    fmt(e.amount) +
                    '</td>' +
                    '<td class="mini-table-td">' + paidBy + '</td>' +
                    '<td class="mini-table-td">' + bmPill + '</td>' +
                    '</tr>';
            }).join('');

            lucide.createIcons();
        }).fail(function() {
            document.getElementById('recent-expenses-body').innerHTML =
                '<tr><td colspan="4" class="mini-table-empty-td" style="color:#ef4444;">Failed to load expenses.</td></tr>';
        });
    }
</script>
<?= $this->endSection() ?>
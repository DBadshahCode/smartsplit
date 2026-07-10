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
    <a href="<?= base_url('/finaldistribution') ?>" class="ss-btn ss-btn-primary" style="text-decoration:none;">
        <i data-lucide="bar-chart-2" style="width:16px;height:16px;"></i>
        <span>View Distribution</span>
    </a>
</div>

<!-- Stat cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px;">

    <div class="ss-card" style="padding:20px 22px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:13px;font-weight:600;color:#64748b;">Total Expenses</span>
            <div style="width:36px;height:36px;border-radius:10px;background:#fce7f3;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="receipt" style="width:16px;height:16px;color:#be185d;"></i>
            </div>
        </div>
        <div style="font-size:28px;font-weight:700;color:#0f172a;letter-spacing:-0.03em;" id="stat-expenses">—</div>
        <div style="font-size:12px;color:#94a3b8;margin-top:4px;">All recorded expenses</div>
    </div>

    <!-- This Month Expenses count — filtered by billing_month -->
    <div class="ss-card" style="padding:20px 22px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:13px;font-weight:600;color:#64748b;">This Month's Expenses</span>
            <div style="width:36px;height:36px;border-radius:10px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="calendar-range" style="width:16px;height:16px;color:#4338ca;"></i>
            </div>
        </div>
        <div style="font-size:28px;font-weight:700;color:#0f172a;letter-spacing:-0.03em;" id="stat-month-count">—</div>
        <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Billing month: <?= date('M Y') ?></div>
    </div>

    <?php if (session()->get('role') === 'admin'): ?>
        <div class="ss-card" style="padding:20px 22px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                <span style="font-size:13px;font-weight:600;color:#64748b;">Total Users</span>
                <div style="width:36px;height:36px;border-radius:10px;background:#ede9fe;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="users" style="width:16px;height:16px;color:#7c3aed;"></i>
                </div>
            </div>
            <div style="font-size:28px;font-weight:700;color:#0f172a;letter-spacing:-0.03em;" id="stat-users">—</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Registered roommates</div>
        </div>
    <?php endif; ?>

    <div class="ss-card" style="padding:20px 22px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:13px;font-weight:600;color:#64748b;">Current Month</span>
            <div style="width:36px;height:36px;border-radius:10px;background:#dcfce7;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="calendar" style="width:16px;height:16px;color:#15803d;"></i>
            </div>
        </div>
        <div style="font-size:22px;font-weight:700;color:#0f172a;letter-spacing:-0.02em;"><?= date('M Y') ?></div>
        <div style="font-size:12px;color:#94a3b8;margin-top:4px;"><?= date('l, d F') ?></div>
    </div>

</div>

<!-- Main grid -->
<div id="dashboard-grid" style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

    <!-- Recent Expenses -->
    <div class="ss-card">
        <div class="ss-card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Recent Expenses</h2>
                <p style="font-size:13px;color:#94a3b8;margin:3px 0 0;">Most recent 5 expenses</p>
            </div>
            <a href="<?= base_url('/expense') ?>"
                style="font-size:13px;font-weight:600;color:#5c6af0;text-decoration:none;display:flex;align-items:center;gap:4px;">
                View all <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
            </a>
        </div>
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table style="width:100%;border-collapse:collapse;min-width:400px;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;">Type</th>
                        <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;">Amount</th>
                        <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;">Paid By</th>
                        <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;">Billing Month</th>
                    </tr>
                </thead>
                <tbody id="recent-expenses-body">
                    <tr>
                        <td colspan="4" style="padding:32px 16px;text-align:center;color:#cbd5e1;font-size:14px;">
                            <i data-lucide="loader" style="width:18px;height:18px;display:inline-block;margin-bottom:6px;"></i>
                            <div>Loading…</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right column -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Quick Actions -->
        <div class="ss-card">
            <div class="ss-card-header">
                <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Quick Actions</h2>
            </div>
            <div style="padding:12px 8px;">

                <?php if (session()->get('role') === 'admin'): ?>
                    <a href="<?= base_url('/user') ?>"
                        style="display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:8px;text-decoration:none;color:#1e293b;transition:background .15s;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <div style="width:34px;height:34px;border-radius:8px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i data-lucide="users" style="width:16px;height:16px;color:#7c3aed;"></i>
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:600;">Manage Users</div>
                            <div style="font-size:12px;color:#94a3b8;">Add or remove members</div>
                        </div>
                        <i data-lucide="chevron-right" style="width:14px;height:14px;color:#cbd5e1;margin-left:auto;"></i>
                    </a>
                <?php endif; ?>

                <a href="<?= base_url('/expense') ?>"
                    style="display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:8px;text-decoration:none;color:#1e293b;transition:background .15s;"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <div style="width:34px;height:34px;border-radius:8px;background:#fce7f3;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="plus-circle" style="width:16px;height:16px;color:#be185d;"></i>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:600;">Add Expense</div>
                        <div style="font-size:12px;color:#94a3b8;">Record a new expense</div>
                    </div>
                    <i data-lucide="chevron-right" style="width:14px;height:14px;color:#cbd5e1;margin-left:auto;"></i>
                </a>

                <a href="<?= base_url('/absentday') ?>"
                    style="display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:8px;text-decoration:none;color:#1e293b;transition:background .15s;"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <div style="width:34px;height:34px;border-radius:8px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="calendar-x" style="width:16px;height:16px;color:#4338ca;"></i>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:600;">Mark Absence</div>
                        <div style="font-size:12px;color:#94a3b8;">Record absent days</div>
                    </div>
                    <i data-lucide="chevron-right" style="width:14px;height:14px;color:#cbd5e1;margin-left:auto;"></i>
                </a>

                <a href="<?= base_url('/finaldistribution') ?>"
                    style="display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:8px;text-decoration:none;color:#1e293b;transition:background .15s;"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <div style="width:34px;height:34px;border-radius:8px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="bar-chart-2" style="width:16px;height:16px;color:#15803d;"></i>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:600;">
                            <?= session()->get('role') === 'admin' ? 'Generate Distribution' : 'View Distribution' ?>
                        </div>
                        <div style="font-size:12px;color:#94a3b8;">
                            <?= session()->get('role') === 'admin' ? 'Calculate monthly split' : 'View monthly split' ?>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" style="width:14px;height:14px;color:#cbd5e1;margin-left:auto;"></i>
                </a>

            </div>
        </div>

        <!-- This month card — total driven by billing_month -->
        <div class="ss-card" style="padding:20px 22px;background:#1a1b4b;border:none;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                <i data-lucide="zap" style="width:16px;height:16px;color:#a5bbfb;"></i>
                <span style="font-size:13px;font-weight:600;color:#a5bbfb;">
                    Billing Month: <?= date('M Y') ?>
                </span>
            </div>
            <div style="font-size:28px;font-weight:700;color:#fff;letter-spacing:-0.03em;font-family:'JetBrains Mono',monospace;" id="stat-month-total">—</div>
            <div style="font-size:12px;color:rgba(255,255,255,.5);margin-top:4px;">Total billed this month</div>
            <!-- Breakdown bar: filled proportionally by billing_month expenses vs all -->
            <div style="margin-top:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <span style="font-size:11px;color:rgba(255,255,255,.45);">Share of all-time total</span>
                    <span style="font-size:11px;font-weight:600;color:#a5bbfb;" id="stat-month-pct">—</span>
                </div>
                <div style="height:4px;background:rgba(255,255,255,.12);border-radius:99px;overflow:hidden;">
                    <div id="stat-month-bar" style="height:100%;width:0%;background:#818cf8;border-radius:99px;transition:width .6s ease;"></div>
                </div>
            </div>
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.1);">
                <a href="<?= base_url('/finaldistribution') ?>"
                    style="font-size:13px;font-weight:600;color:#7f94f7;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                    View breakdown <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
                </a>
            </div>
        </div>

    </div>
</div>

<style>
    @media (max-width: 768px) {
        #dashboard-grid {
            grid-template-columns: 1fr !important;
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
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    // Format a YYYY-MM billing_month string to a human label e.g. "May 2026"
    function fmtBillingMonth(bm) {
        if (!bm) return '—';
        var parts = String(bm).split('-');
        if (parts.length < 2) return bm;
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var mo = parseInt(parts[1], 10);
        return (months[mo - 1] || parts[1]) + ' ' + parts[0];
    }

    var currentMonth = '<?= date('Y-m') ?>';

    <?php if (session()->get('role') === 'admin'): ?>
        $.get('/user/getUsers', function (res) {
            var el = document.getElementById('stat-users');
            if (el) el.textContent = (res.data || []).length;
        });
    <?php endif; ?>

    $.get('/expense/getExpenses', function (res) {
        var all = res.data || [];

        // ── Stat: total expense count (all time) ────────────────────────────
        document.getElementById('stat-expenses').textContent = all.length;

        // ── Filter by billing_month for this-month stats ─────────────────────
        // billing_month is the canonical field — do NOT use from_date for this.
        var thisMonth = all.filter(function (e) {
            return (e.billing_month || '') === currentMonth;
        });

        // ── Stat: count of expenses in current billing_month ────────────────
        document.getElementById('stat-month-count').textContent = thisMonth.length;

        // ── Stat: total amount billed this month ─────────────────────────────
        var monthTotal = thisMonth.reduce(function (sum, e) {
            return sum + parseFloat(e.amount || 0);
        }, 0);
        document.getElementById('stat-month-total').textContent = fmt(monthTotal);

        // ── Progress bar: this month's total vs all-time total ───────────────
        var allTotal = all.reduce(function (sum, e) {
            return sum + parseFloat(e.amount || 0);
        }, 0);
        var pct = allTotal > 0 ? Math.min(100, (monthTotal / allTotal) * 100) : 0;
        document.getElementById('stat-month-pct').textContent = pct.toFixed(1) + '%';
        // Defer so CSS transition fires after paint
        setTimeout(function () {
            document.getElementById('stat-month-bar').style.width = pct.toFixed(1) + '%';
        }, 100);

        // ── Recent expenses table (5 most recent, already DESC from API) ─────
        var recent = all.slice(0, 5);
        var tbody = document.getElementById('recent-expenses-body');

        if (recent.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="padding:32px 16px;text-align:center;color:#cbd5e1;font-size:14px;">No expenses recorded yet</td></tr>';
            return;
        }

        tbody.innerHTML = recent.map(function (e) {
            var paidBy = e.paid_by_name
                ? '<span style="display:inline-flex;align-items:center;gap:4px;font-size:13px;color:#334155;">'
                  + e.paid_by_name + '</span>'
                : '<span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;background:#fef9c3;color:#a16207;">Pending</span>';

            // Billing month pill — highlight if it matches the current billing month
            var bm = e.billing_month || '';
            var bmLabel = fmtBillingMonth(bm);
            var bmIsCurrentMonth = bm === currentMonth;
            var bmPill = '<span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;'
                + 'font-size:11px;font-weight:600;font-family:\'JetBrains Mono\',monospace;'
                + (bmIsCurrentMonth
                    ? 'background:#e0e7ff;color:#4338ca;">'
                    : 'background:#f1f5f9;color:#64748b;">')
                + bmLabel + '</span>';

            return '<tr style="transition:background .1s;" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'\'">'
                + '<td style="padding:12px 16px;font-size:13px;color:#334155;border-bottom:1px solid #f1f5f9;font-weight:500;">'
                +   '<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;font-size:12px;font-weight:600;background:#fce7f3;color:#be185d;">'
                +   (e.expense_type || '—') + '</span>'
                + '</td>'
                + '<td style="padding:12px 16px;font-size:13px;color:#0f172a;border-bottom:1px solid #f1f5f9;font-weight:700;font-family:\'JetBrains Mono\',monospace;white-space:nowrap;">'
                +   fmt(e.amount)
                + '</td>'
                + '<td style="padding:12px 16px;border-bottom:1px solid #f1f5f9;">' + paidBy + '</td>'
                + '<td style="padding:12px 16px;border-bottom:1px solid #f1f5f9;">' + bmPill + '</td>'
                + '</tr>';
        }).join('');

        lucide.createIcons();
    }).fail(function () {
        document.getElementById('recent-expenses-body').innerHTML =
            '<tr><td colspan="4" style="padding:32px 16px;text-align:center;color:#ef4444;font-size:13px;">Failed to load expenses.</td></tr>';
    });
</script>
<?= $this->endSection() ?>
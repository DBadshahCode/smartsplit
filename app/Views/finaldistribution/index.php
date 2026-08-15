<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Final Distribution<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
/**
 * @var array{
 *     id: int,
 *     name: string,
 *     role: string,
 *    isLoggedIn: bool
 * } $currentUser
 */

$isAdmin = $currentUser['role'] === 'admin';
?>

<!-- ── Page header ─────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">Final Distribution</h1>
        <p class="page-subtitle">Generate and view the monthly expense split for each member</p>
    </div>
</div>

<!-- ── Generate panel ─────────────────────────────────────────── -->
<div class="ss-card mb-6">
    <div class="ss-card-body" style="padding:20px 24px;">
        <div class="flex flex-wrap items-end gap-4">

            <!-- Month picker -->
            <div class="flex-1" style="min-width:200px;">
                <label class="ss-label" for="month-input">Select Month</label>
                <div class="field-icon-wrap">
                    <i data-lucide="calendar" class="field-icon"></i>
                    <input type="month" id="month-input" value="<?= date('Y-m') ?>"
                        class="ss-input pl-[38px] font-mono" onchange="loadDistribution(this.value)">
                </div>
            </div>

            <!-- View button -->
            <button onclick="loadDistribution(document.getElementById('month-input').value)"
                class="ss-btn ss-btn-ghost whitespace-nowrap">
                <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                View
            </button>

            <!-- Generate button — admin only -->
            <?php if ($isAdmin): ?>
                <button id="generateBtn" onclick="generateDistribution()" class="ss-btn ss-btn-primary whitespace-nowrap">
                    <i data-lucide="zap" class="w-[15px] h-[15px]" id="generateBtnIcon"></i>
                    <span id="generateBtnText">Generate</span>
                </button>
            <?php endif; ?>

        </div>

        <!-- Status message -->
        <div id="generate-status" class="hidden mt-3.5"></div>
    </div>
</div>

<!-- ── Summary stat cards ──────────────────────────────────────── -->
<div id="summary-cards" class="hidden mb-6">
    <div class="grid gap-3.5" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));">

        <div class="ss-card stat-card" style="padding:16px 18px;">
            <span class="stat-card-label" style="margin-bottom:10px;display:block;">Expenses</span>
            <div id="sum-expenses" class="stat-value font-mono" style="font-size:22px;">—</div>
            <div class="stat-caption">Across all members</div>
        </div>

        <div class="ss-card stat-card" style="padding:16px 18px;">
            <span class="stat-card-label" style="margin-bottom:10px;display:block;">Total Advance</span>
            <div id="sum-advance" class="stat-value font-mono text-green-700" style="font-size:22px;">—</div>
            <div class="stat-caption">Amount paid in advance</div>
        </div>

        <div class="ss-card stat-card" style="padding:16px 18px;">
            <span class="stat-card-label" style="margin-bottom:10px;display:block;">Total Due</span>
            <div id="sum-due" class="stat-value font-mono text-red-600" style="font-size:22px;">—</div>
            <div class="stat-caption">Still to be collected</div>
        </div>

        <div class="ss-card stat-card" style="padding:16px 18px;">
            <span class="stat-card-label" style="margin-bottom:10px;display:block;">Members</span>
            <div id="sum-members" class="stat-value" style="font-size:22px;">—</div>
            <div class="stat-caption">In this distribution</div>
        </div>

    </div>
</div>

<!-- ── Distribution table card ────────────────────────────────── -->
<div class="ss-card" id="distribution-card">
    <div class="ss-card-header flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-[15px] font-bold text-surface-900 m-0">Member Breakdown</h2>
            <p id="distribution-subtitle" class="text-[13px] text-surface-400 mt-[3px] mb-0">
                Select a month and click <?= $isAdmin ? 'Generate or View' : 'View' ?>
            </p>
        </div>
        <div class="flex items-start gap-2.5 flex-wrap">
            <?php if ($isAdmin): ?>
                <button id="btnExportExcel" onclick="exportExcel()" class="btn-outline-indigo hidden">
                    <i data-lucide="file-spreadsheet" class="w-[14px] h-[14px]"></i>
                    Export Excel
                </button>
            <?php endif; ?>

            <!-- Month badge + generated_at timestamp -->
            <div id="month-badge" class="hidden text-right">
                <span class="ss-badge ss-badge-indigo font-mono" id="month-badge-text"></span>
                <div id="generated-at-text" class="hidden text-[11px] text-surface-400 mt-1"></div>
            </div>
        </div>
    </div>

    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table class="w-full border-collapse" style="min-width:560px;">
            <thead>
                <tr class="bg-surface-50">
                    <th class="abs-th text-left">Member</th>
                    <th class="abs-th text-right">Expenses</th>
                    <th class="abs-th text-right">Advance</th>
                    <th class="abs-th text-right">Due</th>
                    <th class="abs-th text-right">Final Amount</th>
                </tr>
            </thead>
            <tbody id="distribution-tbody">
                <tr>
                    <td colspan="5" class="py-14 px-4 text-center">
                        <div class="flex flex-col items-center gap-2.5">
                            <div class="w-12 h-12 rounded-xl bg-surface-50 flex items-center justify-center">
                                <i data-lucide="bar-chart-2" class="w-[22px] h-[22px] text-surface-200"></i>
                            </div>
                            <span class="text-sm text-surface-400 font-medium">No data yet</span>
                            <span class="text-[13px] text-surface-300">
                                Select a month and click
                                <?= $isAdmin ? 'Generate to calculate' : 'View to load' ?>
                                the distribution
                            </span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    lucide.createIcons();

    var isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

    // Money formatting now lives globally as window.fmtMoney (app.js) —
    // aliased locally so every existing fmt(...) call below is unchanged.
    var fmt = window.fmtMoney;

    // ── generated_at: "2026-03-15 14:32:00" (UTC) → shown in visitor's own timezone ─
    function fmtGeneratedAt(raw) {
        if (!raw) return '';
        var str = (typeof raw === 'object' && raw.date) ? raw.date : String(raw);
        var d = new Date(str.replace(' ', 'T') + 'Z'); // 'Z' = treat source as UTC
        if (isNaN(d.getTime())) return str;
        return d.toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }) +
            ', ' +
            d.toLocaleTimeString('en-IN', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
    }

    // ══════════════════════════════════════════════════════════════
    // showStatus() / hideStatus() — banner now built from .status-banner
    // + a modifier class (.status-success/-error/-loading) instead of
    // inline background/border/color on every call. The icon no longer
    // needs its own explicit color either — Lucide icons render with
    // stroke="currentColor" by default, so setting `color` once on the
    // banner div already colors the icon along with the text.
    // ══════════════════════════════════════════════════════════════
    function showStatus(msg, type) {
        var el = document.getElementById('generate-status');
        var config = {
            success: {
                cls: 'status-success',
                icon: 'check-circle'
            },
            error: {
                cls: 'status-error',
                icon: 'alert-circle'
            },
            loading: {
                cls: 'status-loading',
                icon: 'loader'
            },
        };
        var c = config[type] || config.loading;
        el.classList.remove('hidden');
        el.innerHTML = '<div class="status-banner ' + c.cls + '">' +
            '<i data-lucide="' + c.icon + '" class="w-[15px] h-[15px] flex-shrink-0"></i>' +
            '<span class="text-[13px] font-medium">' + msg + '</span>' +
            '</div>';
        lucide.createIcons({
            nodes: [el]
        });
    }

    function hideStatus() {
        document.getElementById('generate-status').classList.add('hidden');
    }

    // ══════════════════════════════════════════════════════════════
    // renderTable() — the big one. Three real simplifications, not
    // just cosmetic:
    //   1. The final-amount badge used to build its own bg/fg hex pair
    //      per state. All three states (due/credit/settled) already
    //      match .ss-badge-red / .ss-badge-green / .ss-badge-gray
    //      exactly — so it just picks a class name now.
    //   2. Advance/Due color-only text swaps to a Tailwind class
    //      (text-green-700 / text-red-600 / text-surface-400) instead
    //      of inline color — same colors, exact hex matches confirmed.
    //   3. Row hover's onmouseover/onmouseout pair is gone — the table
    //      wrapper already has `.ss-table-wrap`, which already carries
    //      the `tbody tr:hover` rule from the expense-view pass.
    // ══════════════════════════════════════════════════════════════
    function renderTable(records, month) {
        var tbody = document.getElementById('distribution-tbody');
        var subtitle = document.getElementById('distribution-subtitle');
        var badge = document.getElementById('month-badge');
        var badgeTxt = document.getElementById('month-badge-text');
        var genAtEl = document.getElementById('generated-at-text');
        var cards = document.getElementById('summary-cards');

        var monthLabel = fmtMonthLabel(month);

        badge.classList.remove('hidden');
        badgeTxt.textContent = monthLabel;

        if (records.length === 0) {
            cards.classList.add('hidden');
            genAtEl.classList.add('hidden');
            subtitle.textContent = 'No records found for ' + monthLabel;
            tbody.innerHTML = '<tr>' +
                '<td colspan="5" class="py-14 px-4 text-center">' +
                '<div class="flex flex-col items-center gap-2.5">' +
                '<div class="w-12 h-12 rounded-xl bg-surface-50 flex items-center justify-center">' +
                '<i data-lucide="inbox" class="w-[22px] h-[22px] text-surface-200"></i>' +
                '</div>' +
                '<span class="text-sm text-surface-400 font-medium">No data for ' + monthLabel + '</span>' +
                '<span class="text-[13px] text-surface-300">' +
                (isAdmin ? 'Click Generate to calculate' : 'Click View to load') +
                ' the distribution for this month</span>' +
                '</div></td></tr>';
            lucide.createIcons({
                nodes: [tbody]
            });
            return;
        }

        var generatedAt = fmtGeneratedAt(records[0].generated_at || '');
        if (generatedAt) {
            genAtEl.textContent = 'Generated: ' + generatedAt;
            genAtEl.classList.remove('hidden');
        } else {
            genAtEl.classList.add('hidden');
        }

        var sumExpenses = 0;
        var sumAdvance = 0;
        var sumDue = 0;

        records.forEach(function(r) {
            sumExpenses += parseFloat(r.expenses_amount || 0);
            sumAdvance += parseFloat(r.advance_amount || 0);
            sumDue += parseFloat(r.due_amount || 0);
        });

        document.getElementById('sum-expenses').textContent = fmt(sumExpenses);
        document.getElementById('sum-advance').textContent = fmt(sumAdvance);
        document.getElementById('sum-due').textContent = fmt(sumDue);
        document.getElementById('sum-members').textContent = records.length;
        cards.classList.remove('hidden');

        subtitle.textContent = records.length + ' member' + (records.length !== 1 ? 's' : '') + ' · ' + monthLabel;

        tbody.innerHTML = records.map(function(r) {
            var colors = avatarColor(r.name);
            var bg = colors[0];
            var fg = colors[1];
            var safeName = window.escHtml(r.name);
            var final = parseFloat(r.final_amount || 0);
            var expenses = parseFloat(r.expenses_amount || 0);
            var advance = parseFloat(r.advance_amount || 0);
            var due = parseFloat(r.due_amount || 0);

            // Final amount — pick the badge class, not the colors
            var finalBadgeClass, finalLabel, finalIcon;
            if (final > 0.005) {
                finalBadgeClass = 'ss-badge-red';
                finalLabel = fmt(final) + ' due';
                finalIcon = 'arrow-up-right';
            } else if (final < -0.005) {
                finalBadgeClass = 'ss-badge-green';
                finalLabel = fmt(Math.abs(final)) + ' credit';
                finalIcon = 'arrow-down-left';
            } else {
                finalBadgeClass = 'ss-badge-gray';
                finalLabel = 'Settled';
                finalIcon = 'check';
            }

            var advanceClass = advance > 0 ? 'text-green-700' : 'text-surface-400';
            var dueClass = due > 0.005 ? 'text-red-600' : 'text-surface-400';

            return '<tr style="border-bottom:1px solid #f1f5f9;">'

                // Member
                +
                '<td class="abs-td" style="white-space:nowrap;">' +
                '<div class="flex items-center gap-2.5">' +
                '<div class="avatar-circle avatar-circle-lg" style="background:' + bg + ';color:' + fg + ';">' +
                initials(r.name) +
                '</div>' +
                '<span class="text-sm font-semibold text-surface-900">' + (safeName || '—') + '</span>' +
                '</div></td>'

                // Expenses
                +
                '<td class="abs-td text-right font-mono text-[13px] text-surface-700" style="white-space:nowrap;">' +
                fmt(expenses) +
                '</td>'

                // Advance
                +
                '<td class="abs-td text-right font-mono text-[13px] font-semibold ' + advanceClass + '" style="white-space:nowrap;">' +
                fmt(advance) +
                '</td>'

                // Due
                +
                '<td class="abs-td text-right font-mono text-[13px] font-semibold ' + dueClass + '" style="white-space:nowrap;">' +
                fmt(due) +
                '</td>'

                // Final amount badge
                +
                '<td class="abs-td text-right" style="white-space:nowrap;">' +
                '<span class="ss-badge ' + finalBadgeClass + ' font-mono" style="gap:5px;">' +
                '<i data-lucide="' + finalIcon + '" class="w-3 h-3"></i>' +
                finalLabel +
                '</span></td>'

                +
                '</tr>';
        }).join('');

        lucide.createIcons({ nodes: [tbody] });
    }

    // ══════════════════════════════════════════════════════════════
    // loadDistribution() — loading/failure states use existing classes;
    // export button toggle simplified since .btn-outline-indigo already
    // declares `display: inline-flex`, so removing `.hidden` is enough —
    // no separate inline-flex class needed, and no lucide.createIcons()
    // needed either (the button's icon already exists in the DOM from
    // the page's initial render; toggling visibility doesn't remove it).
    // ══════════════════════════════════════════════════════════════
    function loadDistribution(month) {
        if (!month) return;
        hideStatus();

        var tbody = document.getElementById('distribution-tbody');
        tbody.innerHTML = '<tr><td colspan="5" class="mini-table-empty-td">' +
            '<div class="flex flex-col items-center gap-2">' +
            '<i data-lucide="loader" class="w-5 h-5 text-surface-200"></i>' +
            '<span class="text-sm">Loading…</span>' +
            '</div></td></tr>';
        lucide.createIcons({ nodes: [tbody] });

        $.get('/finaldistribution/getDistribution/' + month, function(res) {
            renderTable(res.data || [], month);

            var exportBtn = document.getElementById('btnExportExcel');
            if (exportBtn) {
                if (isAdmin && res.data && res.data.length > 0) {
                    exportBtn.classList.remove('hidden');
                } else {
                    exportBtn.classList.add('hidden');
                }
            }
        }).fail(function() {
            showStatus('Failed to load distribution data. Please try again.', 'error');
            document.getElementById('distribution-tbody').innerHTML = '<tr>' +
                '<td colspan="5" class="py-10 px-4 text-center text-red-500 text-[13px]">' +
                'Failed to load data.</td></tr>';
        });
    }

    // ── Generate distribution (admin only) ──────────────────────────
    <?php if ($isAdmin): ?>

        function generateDistribution() {
            var month = document.getElementById('month-input').value;
            if (!month) {
                showStatus('Please select a month first.', 'error');
                return;
            }

            var btn = document.getElementById('generateBtn');
            var text = document.getElementById('generateBtnText');
            btn.disabled = true;
            btn.style.opacity = '0.75';
            text.textContent = 'Generating\u2026';
            window.setLucideIcon('generateBtnIcon', 'loader');

            showStatus('Calculating distribution for ' + fmtMonthLabel(month) + '\u2026', 'loading');

            $.post('/finaldistribution/generateDistribution/' + month, function(res) {
                if (res.status === 'success') {
                    showStatus('Distribution generated successfully for ' + fmtMonthLabel(month) + '.', 'success');
                    loadDistribution(month);
                } else {
                    showStatus('Generation failed. Please try again.', 'error');
                }
            }, 'json').fail(function() {
                showStatus('Something went wrong. Please try again.', 'error');
            }).always(function() {
                btn.disabled = false;
                btn.style.opacity = '1';
                text.textContent = 'Generate';
                window.setLucideIcon('generateBtnIcon', 'zap');
            });
        }
    <?php endif; ?>

    // ── Export Excel (admin only) ────────────────────────────────────
    <?php if ($isAdmin): ?>

        function exportExcel() {
            var month = document.getElementById('month-input').value;
            if (!month) {
                ssToast('Please select a month first.', 'error');
                return;
            }
            if (!/^\d{4}-\d{2}$/.test(month)) {
                ssToast('Invalid month selected.', 'error');
                return;
            }
            ssToast('Preparing Excel export\u2026', 'info');
            window.location.href = '/finaldistribution/exportExcel/' + month;
        }
    <?php endif; ?>

    // ── Auto-load the most recently generated month on page load ─────
    $.get('/finaldistribution/getLatestMonth', function(res) {
        var month = (res && res.month) ? res.month : document.getElementById('month-input').value;
        document.getElementById('month-input').value = month;
        loadDistribution(month);
    }).fail(function() {
        // Fallback to current calendar month if the lookup fails
        loadDistribution(document.getElementById('month-input').value);
    });
</script>
<?= $this->endSection() ?>
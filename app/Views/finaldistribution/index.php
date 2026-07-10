<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Final Distribution<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Page header ─────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">Final Distribution</h1>
        <p class="page-subtitle">Generate and view the monthly expense split for each member</p>
    </div>
</div>

<!-- ── Generate panel ─────────────────────────────────────────── -->
<div class="ss-card" style="margin-bottom:24px;">
    <div class="ss-card-body" style="padding:20px 24px;">
        <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px;">

            <!-- Month picker -->
            <div style="flex:1;min-width:200px;">
                <label class="ss-label" for="month-input">Select Month</label>
                <div style="position:relative;">
                    <i data-lucide="calendar"
                        style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                    <input type="month" id="month-input" value="<?= date('Y-m') ?>" class="ss-input"
                        style="padding-left:38px;font-family:'JetBrains Mono',monospace;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                        onchange="loadDistribution(this.value)">
                </div>
            </div>

            <!-- View button -->
            <button onclick="loadDistribution(document.getElementById('month-input').value)"
                class="ss-btn ss-btn-ghost" style="white-space:nowrap;">
                <i data-lucide="eye" style="width:15px;height:15px;"></i>
                View
            </button>

            <!-- Generate button — admin only -->
            <?php if (session()->get('role') === 'admin'): ?>
                <button id="generateBtn" onclick="generateDistribution()" class="ss-btn ss-btn-primary"
                    style="white-space:nowrap;">
                    <i data-lucide="zap" style="width:15px;height:15px;" id="generateBtnIcon"></i>
                    <span id="generateBtnText">Generate</span>
                </button>
            <?php endif; ?>

        </div>

        <!-- Status message -->
        <div id="generate-status" style="display:none;margin-top:14px;"></div>
    </div>
</div>

<!-- ── Summary stat cards ──────────────────────────────────────── -->
<div id="summary-cards" style="display:none;margin-bottom:24px;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;">

        <div class="ss-card" style="padding:16px 18px;">
            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;">Expenses</div>
            <div id="sum-expenses"
                style="font-size:22px;font-weight:700;color:#0f172a;letter-spacing:-0.02em;font-family:'JetBrains Mono',monospace;">—</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">Across all members</div>
        </div>

        <div class="ss-card" style="padding:16px 18px;">
            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;">Total Advance</div>
            <div id="sum-advance"
                style="font-size:22px;font-weight:700;color:#15803d;letter-spacing:-0.02em;font-family:'JetBrains Mono',monospace;">—</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">Amount paid in advance</div>
        </div>

        <div class="ss-card" style="padding:16px 18px;">
            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;">Total Due</div>
            <div id="sum-due"
                style="font-size:22px;font-weight:700;color:#dc2626;letter-spacing:-0.02em;font-family:'JetBrains Mono',monospace;">—</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">Still to be collected</div>
        </div>

        <div class="ss-card" style="padding:16px 18px;">
            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;">Members</div>
            <div id="sum-members"
                style="font-size:22px;font-weight:700;color:#0f172a;letter-spacing:-0.02em;">—</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">In this distribution</div>
        </div>

    </div>
</div>

<!-- ── Distribution table card ────────────────────────────────── -->
<div class="ss-card" id="distribution-card">
    <div class="ss-card-header"
        style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Member Breakdown</h2>
            <p id="distribution-subtitle" style="font-size:13px;color:#94a3b8;margin:3px 0 0;">
                Select a month and click <?= session()->get('role') === 'admin' ? 'Generate or View' : 'View' ?>
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <?php if (session()->get('role') === 'admin'): ?>
                <button id="btnExportExcel" onclick="exportExcel()" style="
                    display:none;align-items:center;gap:7px;
                    padding:7px 14px;border-radius:8px;
                    font-size:13px;font-weight:600;
                    border:1.5px solid #e0e7ff;
                    background:#f5f7ff;color:#4338ca;
                    cursor:pointer;white-space:nowrap;
                    transition:background .15s,border-color .15s;"
                    onmouseover="this.style.background='#e0e7ff';this.style.borderColor='#818cf8';"
                    onmouseout="this.style.background='#f5f7ff';this.style.borderColor='#e0e7ff';">
                    <i data-lucide="file-spreadsheet" style="width:14px;height:14px;"></i>
                    Export Excel
                </button>
            <?php endif; ?>

            <!-- Month badge + generated_at timestamp -->
            <div id="month-badge" style="display:none;text-align:right;">
                <span style="
                    display:inline-flex;align-items:center;gap:5px;
                    padding:4px 12px;border-radius:999px;
                    font-size:12px;font-weight:600;
                    background:#e0e7ff;color:#4338ca;
                    font-family:'JetBrains Mono',monospace;
                " id="month-badge-text"></span>
                <div id="generated-at-text"
                    style="font-size:11px;color:#94a3b8;margin-top:4px;display:none;">
                </div>
            </div>

        </div>
    </div>

    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table style="width:100%;border-collapse:collapse;min-width:560px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Member</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Expenses</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Advance</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Due</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Final Amount</th>
                </tr>
            </thead>
            <tbody id="distribution-tbody">
                <tr>
                    <td colspan="5" style="padding:56px 16px;text-align:center;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
                            <div style="width:48px;height:48px;border-radius:12px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">
                                <i data-lucide="bar-chart-2" style="width:22px;height:22px;color:#e2e8f0;"></i>
                            </div>
                            <span style="font-size:14px;color:#94a3b8;font-weight:500;">No data yet</span>
                            <span style="font-size:13px;color:#cbd5e1;">
                                Select a month and click
                                <?= session()->get('role') === 'admin' ? 'Generate to calculate' : 'View to load' ?>
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

    var isAdmin = <?= session()->get('role') === 'admin' ? 'true' : 'false' ?>;

    // ── Money formatter ──────────────────────────────────────────────
    function fmt(n) {
        return '₹' + parseFloat(n || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    // ── Month label: "2026-03" → "March 2026" ───────────────────────
    function fmtMonthLabel(month) {
        var parts = (month || '').split('-');
        if (parts.length < 2) return month;
        return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, 1)
            .toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });
    }

    // ── generated_at: "2026-03-15 14:32:00" → "15 Mar 2026, 2:32 PM" ─
    function fmtGeneratedAt(raw) {
        if (!raw) return '';
        // Handle both plain string and CI4 Time object serialised as {date:...}
        var str = (typeof raw === 'object' && raw.date) ? raw.date : String(raw);
        var d = new Date(str.replace(' ', 'T'));
        if (isNaN(d.getTime())) return str;
        return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
            + ', '
            + d.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    // ── Avatar helpers ───────────────────────────────────────────────
    var AVATAR_COLORS = [
        ['#ede9fe','#7c3aed'],['#fce7f3','#be185d'],['#dcfce7','#15803d'],
        ['#fef9c3','#a16207'],['#dbeafe','#1d4ed8'],['#fee2e2','#dc2626'],
        ['#e0e7ff','#4338ca'],['#f0fdf4','#166534'],
    ];
    function avatarColor(name) {
        var i = 0;
        for (var ci = 0; ci < (name || '').length; ci++) i += (name || '').charCodeAt(ci);
        return AVATAR_COLORS[i % AVATAR_COLORS.length];
    }
    function initials(name) {
        if (!name) return '?';
        var p = name.trim().split(' ');
        return (p.length >= 2 ? p[0][0] + p[p.length - 1][0] : name.slice(0, 2)).toUpperCase();
    }

    // ── Status banner below generate panel ──────────────────────────
    function showStatus(msg, type) {
        var el = document.getElementById('generate-status');
        var colors = {
            success: { bg:'#f0fdf4', border:'#bbf7d0', color:'#15803d', icon:'check-circle' },
            error:   { bg:'#fee2e2', border:'#fecaca', color:'#dc2626', icon:'alert-circle' },
            loading: { bg:'#eff6ff', border:'#bfdbfe', color:'#1d4ed8', icon:'loader' },
        };
        var c = colors[type] || colors.loading;
        el.style.display = 'block';
        el.innerHTML = '<div style="display:flex;align-items:center;gap:8px;padding:10px 14px;'
            + 'background:' + c.bg + ';border:1px solid ' + c.border + ';border-radius:8px;">'
            + '<i data-lucide="' + c.icon + '" style="width:15px;height:15px;color:' + c.color + ';flex-shrink:0;"></i>'
            + '<span style="font-size:13px;font-weight:500;color:' + c.color + ';">' + msg + '</span>'
            + '</div>';
        lucide.createIcons();
    }
    function hideStatus() {
        document.getElementById('generate-status').style.display = 'none';
    }

    // ── Render distribution table ────────────────────────────────────
    function renderTable(records, month) {
        var tbody    = document.getElementById('distribution-tbody');
        var subtitle = document.getElementById('distribution-subtitle');
        var badge    = document.getElementById('month-badge');
        var badgeTxt = document.getElementById('month-badge-text');
        var genAtEl  = document.getElementById('generated-at-text');
        var cards    = document.getElementById('summary-cards');

        var monthLabel = fmtMonthLabel(month);

        // Always show month badge once data has been requested
        badge.style.display = 'block';
        badgeTxt.textContent = monthLabel;

        if (records.length === 0) {
            cards.style.display = 'none';
            genAtEl.style.display = 'none';
            subtitle.textContent = 'No records found for ' + monthLabel;
            tbody.innerHTML = '<tr>'
                + '<td colspan="5" style="padding:56px 16px;text-align:center;">'
                + '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">'
                + '<div style="width:48px;height:48px;border-radius:12px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">'
                + '<i data-lucide="inbox" style="width:22px;height:22px;color:#e2e8f0;"></i>'
                + '</div>'
                + '<span style="font-size:14px;color:#94a3b8;font-weight:500;">No data for ' + monthLabel + '</span>'
                + '<span style="font-size:13px;color:#cbd5e1;">'
                + (isAdmin ? 'Click Generate to calculate' : 'Click View to load')
                + ' the distribution for this month</span>'
                + '</div></td></tr>';
            lucide.createIcons();
            return;
        }

        // ── generated_at — take from first record (all rows share same run) ──
        var generatedAt = fmtGeneratedAt(records[0].generated_at || '');
        if (generatedAt) {
            genAtEl.textContent = 'Generated: ' + generatedAt;
            genAtEl.style.display = 'block';
        } else {
            genAtEl.style.display = 'none';
        }

        // ── Summary card totals ──────────────────────────────────────
        var sumExpenses = 0;
        var sumAdvance  = 0;
        var sumDue      = 0;

        records.forEach(function (r) {
            sumExpenses += parseFloat(r.expenses_amount  || 0);
            sumAdvance  += parseFloat(r.advance_amount   || 0);
            sumDue      += parseFloat(r.due_amount       || 0);
        });

        document.getElementById('sum-expenses').textContent = fmt(sumExpenses);
        document.getElementById('sum-advance').textContent  = fmt(sumAdvance);
        document.getElementById('sum-due').textContent      = fmt(sumDue);
        document.getElementById('sum-members').textContent  = records.length;
        cards.style.display = 'block';

        subtitle.textContent = records.length + ' member' + (records.length !== 1 ? 's' : '') + ' · ' + monthLabel;

        // ── Table rows ───────────────────────────────────────────────
        tbody.innerHTML = records.map(function (r) {
            var colors  = avatarColor(r.name);
            var bg      = colors[0];
            var fg      = colors[1];
            var final   = parseFloat(r.final_amount || 0);
            var expenses = parseFloat(r.expenses_amount || 0);
            var advance  = parseFloat(r.advance_amount  || 0);
            var due      = parseFloat(r.due_amount      || 0);

            // Final amount badge styling
            var finalBg, finalFg, finalLabel, finalIcon;
            if (final > 0.005) {
                finalBg    = '#fee2e2'; finalFg = '#dc2626';
                finalLabel = fmt(final) + ' due';
                finalIcon  = 'arrow-up-right';
            } else if (final < -0.005) {
                finalBg    = '#dcfce7'; finalFg = '#15803d';
                finalLabel = fmt(Math.abs(final)) + ' credit';
                finalIcon  = 'arrow-down-left';
            } else {
                finalBg    = '#f1f5f9'; finalFg = '#64748b';
                finalLabel = 'Settled';
                finalIcon  = 'check';
            }

            // Advance highlight: green if they paid something, muted if zero
            var advanceColor = advance > 0 ? '#15803d' : '#94a3b8';

            // Due highlight: red if something due, muted if zero
            var dueColor = due > 0.005 ? '#dc2626' : '#94a3b8';

            return '<tr style="transition:background .1s;" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'\'">'

                // Member
                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">'
                + '<div style="display:flex;align-items:center;gap:10px;">'
                + '<div style="width:34px;height:34px;border-radius:50%;background:' + bg + ';color:' + fg + ';'
                + 'display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;">'
                + initials(r.name)
                + '</div>'
                + '<span style="font-size:14px;font-weight:600;color:#0f172a;">' + (r.name || '—') + '</span>'
                + '</div></td>'

                // Expenses
                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">'
                + '<span style="font-size:13px;color:#334155;font-family:\'JetBrains Mono\',monospace;">'
                + fmt(expenses)
                + '</span></td>'

                // Advance
                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">'
                + '<span style="font-size:13px;font-weight:600;color:' + advanceColor + ';font-family:\'JetBrains Mono\',monospace;">'
                + fmt(advance)
                + '</span></td>'

                // Due
                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">'
                + '<span style="font-size:13px;font-weight:600;color:' + dueColor + ';font-family:\'JetBrains Mono\',monospace;">'
                + fmt(due)
                + '</span></td>'

                // Final amount badge
                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">'
                + '<span style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:999px;'
                + 'font-size:12px;font-weight:700;background:' + finalBg + ';color:' + finalFg + ';'
                + 'font-family:\'JetBrains Mono\',monospace;">'
                + '<i data-lucide="' + finalIcon + '" style="width:12px;height:12px;"></i>'
                + finalLabel
                + '</span></td>'

                + '</tr>';
        }).join('');

        lucide.createIcons();
    }

    // ── Load existing distribution ───────────────────────────────────
    function loadDistribution(month) {
        if (!month) return;
        hideStatus();

        // Show loading state in table
        var tbody = document.getElementById('distribution-tbody');
        tbody.innerHTML = '<tr>'
            + '<td colspan="5" style="padding:40px 16px;text-align:center;color:#cbd5e1;">'
            + '<div style="display:flex;flex-direction:column;align-items:center;gap:8px;">'
            + '<i data-lucide="loader" style="width:20px;height:20px;color:#cbd5e1;"></i>'
            + '<span style="font-size:14px;">Loading…</span>'
            + '</div></td></tr>';
        lucide.createIcons();

        $.get('/finaldistribution/getDistribution/' + month, function (res) {
            renderTable(res.data || [], month);

            // Show/hide export button — admin only, data must exist
            var exportBtn = document.getElementById('btnExportExcel');
            if (exportBtn) {
                if (isAdmin && res.data && res.data.length > 0) {
                    exportBtn.style.display = 'inline-flex';
                    lucide.createIcons();
                } else {
                    exportBtn.style.display = 'none';
                }
            }
        }).fail(function () {
            showStatus('Failed to load distribution data. Please try again.', 'error');
            document.getElementById('distribution-tbody').innerHTML = '<tr>'
                + '<td colspan="5" style="padding:40px 16px;text-align:center;color:#ef4444;font-size:13px;">'
                + 'Failed to load data.</td></tr>';
        });
    }

    // ── Generate distribution (admin only) ──────────────────────────
    <?php if (session()->get('role') === 'admin'): ?>
    function generateDistribution() {
        var month = document.getElementById('month-input').value;
        if (!month) {
            showStatus('Please select a month first.', 'error');
            return;
        }

        var btn  = document.getElementById('generateBtn');
        var text = document.getElementById('generateBtnText');
        btn.disabled = true;
        btn.style.opacity = '0.75';
        text.textContent = 'Generating\u2026';
        window.setLucideIcon('generateBtnIcon', 'loader');

        showStatus('Calculating distribution for ' + fmtMonthLabel(month) + '\u2026', 'loading');

        $.post('/finaldistribution/generateDistribution/' + month, function (res) {
            if (res.status === 'success') {
                showStatus('Distribution generated successfully for ' + fmtMonthLabel(month) + '.', 'success');
                loadDistribution(month);
            } else {
                showStatus('Generation failed. Please try again.', 'error');
            }
        }, 'json').fail(function () {
            showStatus('Something went wrong. Please try again.', 'error');
        }).always(function () {
            btn.disabled = false;
            btn.style.opacity = '1';
            text.textContent = 'Generate';
            window.setLucideIcon('generateBtnIcon', 'zap');
        });
    }
    <?php endif; ?>

    // ── Export Excel (admin only) ────────────────────────────────────
    <?php if (session()->get('role') === 'admin'): ?>
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

    // ── Auto-load current month on page load ─────────────────────────
    loadDistribution(document.getElementById('month-input').value);
</script>
<?= $this->endSection() ?>
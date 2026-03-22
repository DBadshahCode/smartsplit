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
                    <i data-lucide="calendar" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                    <input type="month" id="month-input"
                        value="<?= date('Y-m') ?>"
                        class="ss-input" style="padding-left:38px;font-family:'JetBrains Mono',monospace;"
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
            <button id="generateBtn" onclick="generateDistribution()"
                class="ss-btn ss-btn-primary" style="white-space:nowrap;">
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
            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;">Chapati Total</div>
            <div id="sum-chapati" style="font-size:22px;font-weight:700;color:#0f172a;letter-spacing:-0.02em;font-family:'JetBrains Mono',monospace;">—</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">Across all members</div>
        </div>

        <div class="ss-card" style="padding:16px 18px;">
            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;">Other Expenses</div>
            <div id="sum-other" style="font-size:22px;font-weight:700;color:#0f172a;letter-spacing:-0.02em;font-family:'JetBrains Mono',monospace;">—</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">Across all members</div>
        </div>

        <div class="ss-card" style="padding:16px 18px;">
            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;">Total Advance</div>
            <div id="sum-advance" style="font-size:22px;font-weight:700;color:#0f172a;letter-spacing:-0.02em;font-family:'JetBrains Mono',monospace;">—</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">Amount paid in advance</div>
        </div>

        <div class="ss-card" style="padding:16px 18px;">
            <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;">Total Due</div>
            <div id="sum-due" style="font-size:22px;font-weight:700;color:#dc2626;letter-spacing:-0.02em;font-family:'JetBrains Mono',monospace;">—</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">Still to be collected</div>
        </div>

    </div>
</div>

<!-- ── Distribution table card ────────────────────────────────── -->
<div class="ss-card" id="distribution-card">
    <div class="ss-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Member Breakdown</h2>
            <p id="distribution-subtitle" style="font-size:13px;color:#94a3b8;margin:3px 0 0;">
                Select a month and click Generate or View
            </p>
        </div>
        <div id="month-badge" style="display:none;">
            <span style="
                display:inline-flex;align-items:center;gap:5px;
                padding:4px 12px;border-radius:999px;
                font-size:12px;font-weight:600;
                background:#e0e7ff;color:#4338ca;
                font-family:'JetBrains Mono',monospace;
            " id="month-badge-text"></span>
        </div>
    </div>

    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table style="width:100%;border-collapse:collapse;min-width:620px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Member</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Chapati</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Other</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Advance</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Due</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Final Amount</th>
                </tr>
            </thead>
            <tbody id="distribution-tbody">
                <tr>
                    <td colspan="6" style="padding:56px 16px;text-align:center;color:#cbd5e1;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
                            <div style="width:48px;height:48px;border-radius:12px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">
                                <i data-lucide="bar-chart-2" style="width:22px;height:22px;color:#e2e8f0;"></i>
                            </div>
                            <span style="font-size:14px;color:#94a3b8;font-weight:500;">No data yet</span>
                            <span style="font-size:13px;color:#cbd5e1;">Select a month and click <?= session()->get('role') === 'admin' ? 'Generate to calculate' : 'View to load' ?> the distribution</span>
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

// ── Helpers ──────────────────────────────────────────────────────
function fmt(n) {
    return '₹' + parseFloat(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Avatar helpers
const AVATAR_COLORS = [
    ['#ede9fe','#7c3aed'],['#fce7f3','#be185d'],['#dcfce7','#15803d'],
    ['#fef9c3','#a16207'],['#dbeafe','#1d4ed8'],['#fee2e2','#dc2626'],
    ['#e0e7ff','#4338ca'],['#f0fdf4','#166534'],
];
function avatarColor(name) {
    let i = 0;
    for (let c of (name || '')) i += c.charCodeAt(0);
    return AVATAR_COLORS[i % AVATAR_COLORS.length];
}
function initials(name) {
    if (!name) return '?';
    const parts = name.trim().split(' ');
    return (parts.length >= 2
        ? parts[0][0] + parts[parts.length - 1][0]
        : name.slice(0, 2)
    ).toUpperCase();
}

// ── Show status message below generate panel ─────────────────────
function showStatus(msg, type) {
    const el = document.getElementById('generate-status');
    const colors = {
        success: { bg: '#f0fdf4', border: '#bbf7d0', color: '#15803d', icon: 'check-circle' },
        error:   { bg: '#fee2e2', border: '#fecaca', color: '#dc2626', icon: 'alert-circle' },
        loading: { bg: '#eff6ff', border: '#bfdbfe', color: '#1d4ed8', icon: 'loader'       },
    };
    const c = colors[type] || colors.loading;
    el.style.display = 'block';
    el.innerHTML = `
        <div style="
            display:flex;align-items:center;gap:8px;
            padding:10px 14px;
            background:${c.bg};border:1px solid ${c.border};border-radius:8px;
        ">
            <i data-lucide="${c.icon}" style="width:15px;height:15px;color:${c.color};flex-shrink:0;"></i>
            <span style="font-size:13px;font-weight:500;color:${c.color};">${msg}</span>
        </div>`;
    lucide.createIcons();
}
function hideStatus() {
    document.getElementById('generate-status').style.display = 'none';
}

// ── Render distribution table ────────────────────────────────────
function renderTable(records, month) {
    const tbody    = document.getElementById('distribution-tbody');
    const subtitle = document.getElementById('distribution-subtitle');
    const badge    = document.getElementById('month-badge');
    const badgeTxt = document.getElementById('month-badge-text');
    const cards    = document.getElementById('summary-cards');

    // Format month label e.g. "2026-03" → "March 2026"
    const [y, m] = month.split('-');
    const monthLabel = new Date(y, parseInt(m) - 1, 1)
        .toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });

    badge.style.display    = 'block';
    badgeTxt.textContent   = monthLabel;

    if (records.length === 0) {
        cards.style.display = 'none';
        subtitle.textContent = 'No records found for ' + monthLabel;
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="padding:56px 16px;text-align:center;color:#cbd5e1;">
                    <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
                        <div style="width:48px;height:48px;border-radius:12px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="inbox" style="width:22px;height:22px;color:#e2e8f0;"></i>
                        </div>
                        <span style="font-size:14px;color:#94a3b8;font-weight:500;">No data for ${monthLabel}</span>
                        <span style="font-size:13px;color:#cbd5e1;">Click Generate to calculate the distribution for this month</span>
                    </div>
                </td>
            </tr>`;
        lucide.createIcons();
        return;
    }

    // Update summary cards
    let sumChapati = 0, sumOther = 0, sumAdvance = 0, sumDue = 0;
    records.forEach(function(r) {
        sumChapati += parseFloat(r.chapati_amount       || 0);
        sumOther   += parseFloat(r.other_expenses_amount || 0);
        sumAdvance += parseFloat(r.advance_amount        || 0);
        sumDue     += parseFloat(r.due_amount            || 0);
    });
    document.getElementById('sum-chapati').textContent = fmt(sumChapati);
    document.getElementById('sum-other').textContent   = fmt(sumOther);
    document.getElementById('sum-advance').textContent = fmt(sumAdvance);
    document.getElementById('sum-due').textContent     = fmt(sumDue);
    cards.style.display = 'block';

    subtitle.textContent = records.length + ' member' + (records.length !== 1 ? 's' : '') + ' · ' + monthLabel;

    tbody.innerHTML = records.map(function(r) {
        const [bg, fg] = avatarColor(r.name);
        const final    = parseFloat(r.final_amount || 0);

        // Final amount colouring: positive = owes (red), negative = in credit (green), zero = settled (grey)
        let finalBg, finalFg, finalLabel, finalIcon;
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

        return `<tr style="transition:background .1s;"
                    onmouseover="this.style.background='#f8fafc'"
                    onmouseout="this.style.background=''">

            <!-- Member -->
            <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="
                        width:34px;height:34px;border-radius:50%;
                        background:${bg};color:${fg};
                        display:flex;align-items:center;justify-content:center;
                        font-size:12px;font-weight:700;flex-shrink:0;
                    ">${initials(r.name)}</div>
                    <span style="font-size:14px;font-weight:600;color:#0f172a;">${r.name || '—'}</span>
                </div>
            </td>

            <!-- Chapati -->
            <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                <span style="font-size:13px;color:#64748b;font-family:'JetBrains Mono',monospace;">
                    ${fmt(r.chapati_amount)}
                </span>
            </td>

            <!-- Other expenses -->
            <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                <span style="font-size:13px;color:#64748b;font-family:'JetBrains Mono',monospace;">
                    ${fmt(r.other_expenses_amount)}
                </span>
            </td>

            <!-- Advance -->
            <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                <span style="font-size:13px;color:#15803d;font-weight:600;font-family:'JetBrains Mono',monospace;">
                    ${fmt(r.advance_amount)}
                </span>
            </td>

            <!-- Due -->
            <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                <span style="font-size:13px;color:#dc2626;font-weight:600;font-family:'JetBrains Mono',monospace;">
                    ${fmt(r.due_amount)}
                </span>
            </td>

            <!-- Final amount badge -->
            <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                <span style="
                    display:inline-flex;align-items:center;gap:5px;
                    padding:5px 12px;border-radius:999px;
                    font-size:12px;font-weight:700;
                    background:${finalBg};color:${finalFg};
                    font-family:'JetBrains Mono',monospace;
                ">
                    <i data-lucide="${finalIcon}" style="width:12px;height:12px;"></i>
                    ${finalLabel}
                </span>
            </td>
        </tr>`;
    }).join('');

    lucide.createIcons();
}

// ── Load existing distribution ───────────────────────────────────
function loadDistribution(month) {
    if (!month) return;
    hideStatus();

    const tbody = document.getElementById('distribution-tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" style="padding:40px 16px;text-align:center;color:#cbd5e1;">
                <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                    <i data-lucide="loader" style="width:20px;height:20px;color:#cbd5e1;"></i>
                    <span style="font-size:14px;">Loading…</span>
                </div>
            </td>
        </tr>`;
    lucide.createIcons();

    $.get('/finaldistribution/getDistribution/' + month, function(res) {
        renderTable(res.data || [], month);
    }).fail(function() {
        showStatus('Failed to load distribution data.', 'error');
    });
}

// ── Generate distribution (admin only) ──────────────────────────
<?php if (session()->get('role') === 'admin'): ?>
function generateDistribution() {
    const month = document.getElementById('month-input').value;
    if (!month) { showStatus('Please select a month first.', 'error'); return; }

    // Button loading state
    const btn  = document.getElementById('generateBtn');
    const text = document.getElementById('generateBtnText');
    const icon = document.getElementById('generateBtnIcon');
    btn.disabled      = true;
    btn.style.opacity = '0.75';
    text.textContent  = 'Generating…';
    icon.setAttribute('data-lucide', 'loader');
    lucide.createIcons();

    showStatus('Calculating distribution for ' + month + '…', 'loading');

    $.post('/finaldistribution/generateDistribution/' + month, function(res) {
        if (res.status === 'success') {
            showStatus('Distribution generated successfully for ' + month + '.', 'success');
            loadDistribution(month);
        } else {
            showStatus('Generation failed. Please try again.', 'error');
        }
    }, 'json').fail(function() {
        showStatus('Something went wrong. Please try again.', 'error');
    }).always(function() {
        btn.disabled      = false;
        btn.style.opacity = '1';
        text.textContent  = 'Generate';
        icon.setAttribute('data-lucide', 'zap');
        lucide.createIcons();
    });
}
<?php endif; // end admin-only generate function ?>

// ── Auto-load current month on page load ─────────────────────────
loadDistribution(document.getElementById('month-input').value);
</script>
<?= $this->endSection() ?>
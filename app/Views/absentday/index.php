<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Absent Days<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Page header ────────────────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">Absent Days</h1>
        <p class="page-subtitle">Track per-expense absences used in days-present splits</p>
    </div>
</div>

<!-- ── Main card ──────────────────────────────────────────────────────────── -->
<div class="ss-card">

    <!-- Toolbar -->
    <div class="ss-card-header" style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
        <i data-lucide="calendar-search" style="width:15px;height:15px;color:#94a3b8;flex-shrink:0;"></i>
        <select id="expensePicker" class="ss-input" style="flex:1;min-width:200px;max-width:480px;" onchange="loadAbsentDay()">
            <option value="">— Select an expense —</option>
        </select>
        <span id="loadingSpinner" style="display:none;font-size:0.82rem;color:#5c6af0;align-items:center;gap:5px;">
            <i data-lucide="loader-2" style="width:13px;height:13px;"></i> Loading…
        </span>
    </div>

    <!-- Info strip (shown after expense selected) -->
    <div id="expenseInfo" style="display:none;flex-wrap:wrap;gap:1.5rem;padding:0.85rem 1.4rem;background:#f8fafc;border-bottom:1px solid #f1f5f9;">
        <div>
            <p style="font-size:0.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin:0 0 0.2rem;">Expense Type</p>
            <p id="infoType" style="font-size:0.875rem;font-weight:600;color:#1e293b;margin:0;"></p>
        </div>
        <div>
            <p style="font-size:0.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin:0 0 0.2rem;">Period</p>
            <p id="infoPeriod" style="font-size:0.82rem;font-weight:600;color:#1e293b;margin:0;font-family:'JetBrains Mono',monospace;"></p>
        </div>
        <div>
            <p style="font-size:0.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin:0 0 0.2rem;">Total Days</p>
            <p id="infoTotalDays" style="font-size:0.875rem;font-weight:600;color:#5c6af0;margin:0;"></p>
        </div>
        <div>
            <p style="font-size:0.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin:0 0 0.2rem;">Amount</p>
            <p id="infoAmount" style="font-size:0.82rem;font-weight:600;color:#1e293b;margin:0;font-family:'JetBrains Mono',monospace;"></p>
        </div>
    </div>

    <!-- Card sub-header row (shown when table is visible) -->
    <div id="cardMeta" style="display:none;padding:0.85rem 1.4rem 0.5rem;display:none;align-items:baseline;gap:0.5rem;flex-wrap:wrap;">
        <span style="font-size:0.95rem;font-weight:600;color:#1e293b;">Absences</span>
        <span id="periodLabel" style="font-size:0.8rem;color:#94a3b8;"></span>
    </div>

    <!-- Empty state -->
    <div id="emptyState" style="padding:3.5rem 2rem;text-align:center;color:#94a3b8;">
        <i data-lucide="calendar-search" style="width:44px;height:44px;margin:0 auto 0.85rem;color:#cbd5e1;display:block;"></i>
        <p style="font-size:0.95rem;font-weight:600;color:#64748b;margin:0 0 0.3rem;">Select an expense to manage absences</p>
        <p style="font-size:0.82rem;margin:0;">Only expenses with <em>Days Present</em> split method appear above</p>
    </div>

    <!-- Table wrapper — horizontal scroll on small screens -->
    <div id="tableWrap" class="ss-table-wrap" style="display:none;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                    <th style="padding:0.7rem 1.25rem;font-size:0.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;text-align:left;white-space:nowrap;">Member</th>
                    <th style="padding:0.7rem 1.25rem;font-size:0.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;text-align:center;white-space:nowrap;">Days Absent</th>
                    <th style="padding:0.7rem 1.25rem;font-size:0.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;text-align:center;white-space:nowrap;">Days Present</th>
                    <?php if (session()->get('role') === 'admin'): ?>
                            <th style="padding:0.7rem 1.25rem;font-size:0.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;text-align:right;white-space:nowrap;">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="absentTableBody"></tbody>
        </table>
    </div>

</div>

<!-- ── Edit Modal ──────────────────────────────────────────────────────────── -->
<div id="editBackdrop" style="display:none;position:fixed;inset:0;background:rgba(15,22,41,.45);z-index:100;opacity:0;transition:opacity .18s;"></div>
<div id="editModal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.97);z-index:101;width:calc(100% - 2rem);max-width:440px;background:#fff;border-radius:16px;opacity:0;transition:opacity .18s,transform .18s;overflow:hidden;">

    <!-- Modal header -->
    <div style="display:flex;align-items:flex-start;gap:1rem;padding:1.5rem 1.5rem 1.1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:40px;height:40px;border-radius:10px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#7c3aed;">
            <i data-lucide="calendar-x" style="width:18px;height:18px;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <p style="font-size:1rem;font-weight:700;color:#1e293b;margin:0 0 0.2rem;">Edit Absent Days</p>
            <p style="font-size:0.8rem;color:#94a3b8;margin:0;">Update absence record for this expense period</p>
        </div>
        <button class="ss-btn ss-btn-ghost" onclick="closeEditModal()" style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="x" style="width:15px;height:15px;"></i>
        </button>
    </div>

    <!-- Modal body -->
    <div style="padding:1.25rem 1.5rem;">

        <!-- Meta summary -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.85rem;margin-bottom:1.1rem;padding:0.85rem;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9;">
            <div>
                <p style="font-size:0.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 0.15rem;">Member</p>
                <p id="editUserName" style="font-size:0.875rem;font-weight:600;color:#1e293b;margin:0;"></p>
            </div>
            <div>
                <p style="font-size:0.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 0.15rem;">Period</p>
                <p id="editPeriod" style="font-size:0.78rem;font-weight:600;color:#1e293b;margin:0;font-family:'JetBrains Mono',monospace;"></p>
            </div>
        </div>

        <!-- Days absent input -->
        <label class="ss-label" for="editDaysAbsent">
            Days Absent <span style="color:#dc2626;">*</span>
        </label>
        <input type="number" id="editDaysAbsent" class="ss-input" style="width:100%;box-sizing:border-box;" min="0" placeholder="0">
        <p id="editDaysHint" style="font-size:0.75rem;color:#94a3b8;margin:0.35rem 0 0;"></p>
    </div>

    <!-- Modal footer -->
    <div style="display:flex;gap:0.75rem;justify-content:flex-end;padding:1rem 1.5rem;border-top:1px solid #f1f5f9;background:#f8fafc;">
        <input type="hidden" id="editUserId">
        <input type="hidden" id="editExpenseId">
        <button class="ss-btn ss-btn-ghost" onclick="closeEditModal()">Cancel</button>
        <button class="ss-btn ss-btn-primary" id="btnSave" onclick="saveAbsentDay()">
            <i data-lucide="save" style="width:14px;height:14px;"></i>
            Save Changes
        </button>
    </div>

</div>

<script>
    // ── Constants ──────────────────────────────────────────────────────────────
    const isAdmin = <?= session()->get('role') === 'admin' ? 'true' : 'false' ?>;

    const AVATAR_COLORS = [
        ['#ede9fe','#7c3aed'],['#fce7f3','#be185d'],['#dcfce7','#15803d'],
        ['#fef9c3','#a16207'],['#dbeafe','#1d4ed8'],['#fee2e2','#dc2626'],
        ['#e0e7ff','#4338ca'],['#f0fdf4','#166534'],
    ];

    // ── Helpers ────────────────────────────────────────────────────────────────
    function avatarColor(name) {
        let i = 0;
        for (let c of (name || '')) i += c.charCodeAt(0);
        return AVATAR_COLORS[i % AVATAR_COLORS.length];
    }

    function initials(name) {
        if (!name) return '?';
        const p = name.trim().split(' ');
        return (p.length >= 2 ? p[0][0] + p[p.length - 1][0] : name.slice(0, 2)).toUpperCase();
    }

    function formatDate(ymd) {
        if (!ymd) return '';
        const [y, m, d] = ymd.split('-');
        return d + '/' + m + '/' + y;
    }

    function formatMoney(n) {
        return '₹' + parseFloat(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ── Expense list cache ─────────────────────────────────────────────────────
    let expenseList = [];

    // ── Load expense dropdown ──────────────────────────────────────────────────
    $(document).ready(function () {
        $.get('/absentday/getExpenses', function (res) {
            expenseList = res.data || [];
            const select = document.getElementById('expensePicker');

            if (expenseList.length === 0) {
                select.innerHTML = '<option value="">— No daysPresent expenses found —</option>';
                return;
            }

            expenseList.forEach(function (exp) {
                const label = exp.expense_type + '  (' + formatDate(exp.from_date) + ' → ' + formatDate(exp.to_date) + ')';
                const opt = document.createElement('option');
                opt.value = exp.id;
                opt.textContent = label;
                select.appendChild(opt);
            });

        }).fail(function () {
            ssToast('Failed to load expenses.', 'error');
        });
    });

    // ── Load absent days for selected expense ─────────────────────────────────
    function loadAbsentDay() {
        const expenseId = document.getElementById('expensePicker').value;

        // Reset visibility
        document.getElementById('tableWrap').style.display    = 'none';
        document.getElementById('expenseInfo').style.display  = 'none';
        document.getElementById('cardMeta').style.display     = 'none';
        document.getElementById('periodLabel').textContent    = '';

        if (!expenseId) {
            document.getElementById('emptyState').style.display = 'block';
            return;
        }

        // Populate info strip from cache
        const exp = expenseList.find(function (e) { return e.id == expenseId; });
        if (exp) {
            document.getElementById('infoType').textContent   = exp.expense_type;
            document.getElementById('infoPeriod').textContent = formatDate(exp.from_date) + ' → ' + formatDate(exp.to_date);
            document.getElementById('infoAmount').textContent = formatMoney(exp.amount);
            document.getElementById('expenseInfo').style.display = 'flex';
        }

        document.getElementById('loadingSpinner').style.display = 'inline-flex';
        document.getElementById('emptyState').style.display     = 'none';

        $.get('/absentday/getAbsentDays/' + expenseId, function (res) {
            document.getElementById('loadingSpinner').style.display = 'none';

            const data      = res.data || [];
            const totalDays = res.total_days || 0;

            document.getElementById('infoTotalDays').textContent = totalDays + ' days';
            document.getElementById('periodLabel').textContent   = totalDays + '-day period';

            if (data.length === 0) {
                document.getElementById('emptyState').style.display  = 'block';
                document.getElementById('expenseInfo').style.display = 'none';
                return;
            }

            document.getElementById('cardMeta').style.display = 'flex';
            renderTable(data, totalDays, exp);
            document.getElementById('tableWrap').style.display = 'block';

        }).fail(function () {
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('emptyState').style.display     = 'block';
            ssToast('Failed to load absent days.', 'error');
        });
    }

    // ── Render table rows ──────────────────────────────────────────────────────
    function renderTable(data, totalDays, exp) {
        const tbody = document.getElementById('absentTableBody');
        tbody.innerHTML = '';

        data.forEach(function (row) {
            const [bg, fg] = avatarColor(row.name);
            const isZero   = row.days_absent === 0;
            const period   = exp ? formatDate(exp.from_date) + ' → ' + formatDate(exp.to_date) : '';

            const tr = document.createElement('tr');
            tr.style.borderBottom  = '1px solid #f8fafc';
            tr.style.transition    = 'background .12s';
            tr.onmouseenter = function () { tr.style.background = '#f8fafc'; };
            tr.onmouseleave = function () { tr.style.background = ''; };

            // Absent pill colours
            const pillBg  = isZero ? '#dcfce7' : '#fee2e2';
            const pillFg  = isZero ? '#15803d' : '#dc2626';

            const actionCell = isAdmin
                ? `<td style="padding:0.85rem 1.25rem;text-align:right;">
                       <button class="ss-btn ss-btn-ghost" style="font-size:0.8rem;padding:0.32rem 0.75rem;"
                           onclick="openEditModal(${row.user_id}, \'${row.name}\', ${row.expense_id}, \'${period}\', ${row.days_absent}, ${totalDays})">
                           <i id="pencil-${row.user_id}" data-lucide="pencil" style="width:12px;height:12px;"></i>Edit
                       </button>
                   </td>`
                : '';

            tr.innerHTML = `
                <td style="padding:0.85rem 1.25rem;font-size:0.875rem;color:#334155;">
                    <div style="display:flex;align-items:center;gap:0.65rem;">
                        <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;flex-shrink:0;background:${bg};color:${fg};">
                            ${initials(row.name)}
                        </div>
                        <span style="font-weight:500;color:#1e293b;">${row.name}</span>
                    </div>
                </td>
                <td style="padding:0.85rem 1.25rem;text-align:center;">
                    <span style="display:inline-block;padding:0.18rem 0.7rem;border-radius:20px;font-family:'JetBrains Mono',monospace;font-size:0.82rem;font-weight:600;background:${pillBg};color:${pillFg};">
                        ${row.days_absent}
                    </span>
                </td>
                <td style="padding:0.85rem 1.25rem;text-align:center;font-family:'JetBrains Mono',monospace;font-size:0.82rem;color:#64748b;">
                    ${row.days_present} / ${totalDays}
                </td>
                ${actionCell}
            `;

            tbody.appendChild(tr);

            // Re-render pencil icon using the safe project helper (not createIcons directly)
            if (isAdmin) {
                window.setLucideIcon('pencil-' + row.user_id, 'pencil');
            }
        });
    }

    // ── Modal open / close ─────────────────────────────────────────────────────
    function openEditModal(userId, userName, expenseId, period, currentDays, totalDays) {
        document.getElementById('editUserId').value    = userId;
        document.getElementById('editExpenseId').value = expenseId;
        document.getElementById('editUserName').textContent = userName;
        document.getElementById('editPeriod').textContent   = period;

        const input = document.getElementById('editDaysAbsent');
        input.value = currentDays;
        input.max   = totalDays;
        document.getElementById('editDaysHint').textContent =
            'Max ' + totalDays + ' days in this expense period. Set 0 to clear.';

        const backdrop = document.getElementById('editBackdrop');
        const modal    = document.getElementById('editModal');
        backdrop.style.display = 'block';
        modal.style.display    = 'block';
        requestAnimationFrame(function () {
            backdrop.style.opacity    = '1';
            modal.style.opacity       = '1';
            modal.style.transform     = 'translate(-50%,-50%) scale(1)';
            input.focus();
            input.select();
        });
    }

    function closeEditModal() {
        const backdrop = document.getElementById('editBackdrop');
        const modal    = document.getElementById('editModal');
        backdrop.style.opacity = '0';
        modal.style.opacity    = '0';
        modal.style.transform  = 'translate(-50%,-50%) scale(.97)';
        setTimeout(function () {
            backdrop.style.display = 'none';
            modal.style.display    = 'none';
            document.getElementById('editDaysAbsent').value = '';
        }, 180);
    }

    // ── Save ───────────────────────────────────────────────────────────────────
    function saveAbsentDay() {
        const expenseId = document.getElementById('editExpenseId').value;
        const userId    = document.getElementById('editUserId').value;
        const days      = parseInt(document.getElementById('editDaysAbsent').value, 10);
        const maxDays   = parseInt(document.getElementById('editDaysAbsent').max, 10);

        if (isNaN(days) || days < 0) {
            ssToast('Please enter a valid number of days.', 'error');
            return;
        }
        if (days > maxDays) {
            ssToast('Days absent cannot exceed ' + maxDays + ' for this period.', 'error');
            return;
        }

        const btn = document.getElementById('btnSave');
        btn.disabled = true;

        $.ajax({
            url:  '/absentday/upsert',
            type: 'POST',
            data: { expense_id: expenseId, user_id: userId, days_absent: days },
            success: function () {
                btn.disabled = false;
                closeEditModal();
                ssToast('Absent days saved successfully.', 'success');
                loadAbsentDay();
            },
            error: function (xhr) {
                btn.disabled = false;
                const msg = xhr.responseJSON && xhr.responseJSON.error
                    ? xhr.responseJSON.error
                    : 'Failed to save. Please try again.';
                ssToast(msg, 'error');
            }
        });
    }

    // ── Escape key ─────────────────────────────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeEditModal();
    });
</script>

<?= $this->endSection() ?>
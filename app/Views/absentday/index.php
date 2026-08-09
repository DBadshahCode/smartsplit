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

<!-- ── Main card ────────────────────────────────────────────────────────── -->
<div class="ss-card">

    <!-- Toolbar -->
    <div class="ss-card-header flex items-center gap-3 flex-wrap">
        <i data-lucide="calendar-search" class="w-[15px] h-[15px] text-surface-400 flex-shrink-0"></i>
        <select id="expensePicker" class="ss-input flex-1" style="min-width:200px;max-width:480px;"
            onchange="loadAbsentDay()">
            <option value="">— Select an expense —</option>
        </select>
        <span id="loadingSpinner" class="hidden items-center gap-[5px] text-[0.82rem]" style="color:#5c6af0;">
            <i data-lucide="loader-2" class="w-[13px] h-[13px]"></i> Loading…
        </span>
    </div>

    <!-- Info strip (shown after expense selected) -->
    <div id="expenseInfo" class="info-strip">
        <div>
            <p class="info-label">Expense Type</p>
            <p id="infoType" class="info-value"></p>
        </div>
        <div>
            <p class="info-label">Period</p>
            <p id="infoPeriod" class="info-value-mono"></p>
        </div>
        <div>
            <p class="info-label">Total Days</p>
            <p id="infoTotalDays" class="info-value" style="color:#5c6af0;"></p>
        </div>
        <div>
            <p class="info-label">Amount</p>
            <p id="infoAmount" class="info-value-mono"></p>
        </div>
    </div>

    <!-- Card sub-header row (shown when table is visible) -->
    <div id="cardMeta" class="card-meta-row">
        <span class="text-[0.95rem] font-semibold text-surface-800">Absences</span>
        <span id="periodLabel" class="text-[0.8rem] text-surface-400"></span>
    </div>

    <!-- Empty state -->
    <div id="emptyState" class="empty-state">
        <i data-lucide="calendar-search" class="empty-state-icon"></i>
        <p class="empty-state-title">Select an expense to manage absences</p>
        <p class="empty-state-desc">Only expenses with <em>Days Present</em> split method appear above</p>
    </div>

    <!-- Table wrapper — horizontal scroll on small screens -->
    <div id="tableWrap" class="ss-table-wrap hidden">
        <table class="w-full border-collapse">
            <thead>
                <tr class="border-b border-surface-100 bg-surface-50">
                    <th class="abs-th text-left">Member</th>
                    <th class="abs-th text-center">Days Absent</th>
                    <th class="abs-th text-center">Days Present</th>
                    <th class="abs-th text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="absentTableBody"></tbody>
        </table>
    </div>

</div>

<!-- ── Edit Modal ──────────────────────────────────────────────────────────── -->
<div id="editBackdrop" class="modal-backdrop" style="z-index:100;"></div>

<div id="editModal" class="modal-panel modal-shell" style="z-index:101;max-width:440px;">

    <!-- Modal header -->
    <div class="modal-header items-start">
        <div class="modal-header-info items-start">
            <div class="modal-header-icon bg-violet-100" style="width:40px;height:40px;">
                <i data-lucide="calendar-x" class="w-[18px] h-[18px] text-violet-600"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="modal-header-title">Edit Absent Days</h3>
                <p class="modal-header-subtitle">Update absence record for this expense period</p>
            </div>
        </div>
        <button onclick="closeEditModal()" class="icon-btn icon-btn-muted icon-btn-sm">
            <i data-lucide="x"></i>
        </button>
    </div>

    <!-- Modal body -->
    <div class="modal-body modal-form">

        <!-- Meta summary -->
        <div class="meta-grid-2">
            <div>
                <p class="info-label">Member</p>
                <p id="editUserName" class="info-value"></p>
            </div>
            <div>
                <p class="info-label">Period</p>
                <p id="editPeriod" class="info-value-mono"></p>
            </div>
        </div>

        <!-- Days absent input -->
        <label class="ss-label" for="editDaysAbsent">
            Days Absent <span class="required-star">*</span>
        </label>
        <input type="number" id="editDaysAbsent" class="ss-input" min="0" placeholder="0">
        <p id="editDaysHint" class="text-xs text-surface-400 mt-1.5"></p>
    </div>

    <!-- Modal footer -->
    <div class="flex gap-3 justify-end p-4 border-t border-surface-100 bg-surface-50">
        <input type="hidden" id="editUserId">
        <input type="hidden" id="editExpenseId">
        <button class="ss-btn ss-btn-ghost" onclick="closeEditModal()">Cancel</button>
        <button class="ss-btn ss-btn-primary" id="btnSave" onclick="saveAbsentDay()">
            <i data-lucide="save" class="w-[14px] h-[14px]"></i>
            Save Changes
        </button>
    </div>

</div>

<script>
    // ── Constants ──────────────────────────────────────────────────────────────
    const isAdmin = <?= session()->get('role') === 'admin' ? 'true' : 'false' ?>;
    const loggedInUserId = <?= (int) session()->get('user_id') ?>;

    const AVATAR_COLORS = [
        ['#ede9fe', '#7c3aed'],
        ['#fce7f3', '#be185d'],
        ['#dcfce7', '#15803d'],
        ['#fef9c3', '#a16207'],
        ['#dbeafe', '#1d4ed8'],
        ['#fee2e2', '#dc2626'],
        ['#e0e7ff', '#4338ca'],
        ['#f0fdf4', '#166534'],
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
        return '₹' + parseFloat(n).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // ── Expense list cache ─────────────────────────────────────────────────────
    let expenseList = [];

    // ── Load expense dropdown ──────────────────────────────────────────────────
    $(document).ready(function() {
        $.get('/absentday/getExpenses', function(res) {
            expenseList = res.data || [];
            const select = document.getElementById('expensePicker');

            if (expenseList.length === 0) {
                select.innerHTML = '<option value="">— No daysPresent expenses found —</option>';
                return;
            }

            expenseList.forEach(function(exp) {
                const label = exp.expense_type + '  (' + formatDate(exp.from_date) + ' → ' + formatDate(exp.to_date) + ')';
                const opt = document.createElement('option');
                opt.value = exp.id;
                opt.textContent = label;
                select.appendChild(opt);
            });

        }).fail(function() {
            ssToast('Failed to load expenses.', 'error');
        });
    });

    // ── Load absent days for selected expense ─────────────────────────────────
    function loadAbsentDay() {
        const expenseId = document.getElementById('expensePicker').value;

        // Reset visibility
        document.getElementById('tableWrap').style.display = 'none';
        document.getElementById('expenseInfo').style.display = 'none';
        document.getElementById('cardMeta').style.display = 'none';
        document.getElementById('periodLabel').textContent = '';

        if (!expenseId) {
            document.getElementById('emptyState').style.display = 'block';
            return;
        }

        // Populate info strip from cache
        const exp = expenseList.find(function(e) {
            return e.id == expenseId;
        });
        if (exp) {
            document.getElementById('infoType').textContent = exp.expense_type;
            document.getElementById('infoPeriod').textContent = formatDate(exp.from_date) + ' → ' + formatDate(exp.to_date);
            document.getElementById('infoAmount').textContent = formatMoney(exp.amount);
            document.getElementById('expenseInfo').style.display = 'flex';
        }

        document.getElementById('loadingSpinner').style.display = 'inline-flex';
        document.getElementById('emptyState').style.display = 'none';

        $.get('/absentday/getAbsentDays/' + expenseId, function(res) {
            document.getElementById('loadingSpinner').style.display = 'none';

            const data = res.data || [];
            const totalDays = res.total_days || 0;

            document.getElementById('infoTotalDays').textContent = totalDays + ' days';
            document.getElementById('periodLabel').textContent = totalDays + '-day period';

            if (data.length === 0) {
                document.getElementById('emptyState').style.display = 'block';
                document.getElementById('expenseInfo').style.display = 'none';
                return;
            }

            document.getElementById('cardMeta').style.display = 'flex';
            renderTable(data, totalDays, exp);
            document.getElementById('tableWrap').style.display = 'block';

        }).fail(function() {
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
            ssToast('Failed to load absent days.', 'error');
        });
    }

    // ══════════════════════════════════════════════════════════════
    // renderTable() — pills/avatar/cells now use classes from batch 1.
    // Two things removed as genuinely dead weight:
    //   1. onmouseenter/onmouseleave — #tableWrap already has the
    //      `.ss-table-wrap` class, which already got a
    //      `tbody tr:hover { background:#f8fafc }` rule (and its own
    //      transition) added to main.php during the expense-view pass.
    //      The per-row JS handlers were re-implementing that from scratch.
    //   2. `canEdit` was computed twice — once for the ternary, once
    //      again for the setLucideIcon check with the identical
    //      expression. Computed once now, reused for both.
    // ══════════════════════════════════════════════════════════════
    function renderTable(data, totalDays, exp) {
        const tbody = document.getElementById('absentTableBody');
        tbody.innerHTML = '';

        data.forEach(function(row) {
            const [bg, fg] = avatarColor(row.name);
            const isZero = row.days_absent === 0;
            const period = exp ? formatDate(exp.from_date) + ' → ' + formatDate(exp.to_date) : '';
            const canEdit = isAdmin || row.user_id === loggedInUserId;

            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f8fafc';

            const pillClass = isZero ? 'abs-pill-ok' : 'abs-pill-absent';

            const actionCell = canEdit ?
                `<td class="abs-td text-right">
                   <button class="row-action-btn row-action-edit"
                       onclick="openEditModal(${row.user_id}, \'${row.name}\', ${row.expense_id}, \'${period}\', ${row.days_absent}, ${totalDays})">
                       <i id="pencil-${row.user_id}" data-lucide="pencil" class="w-3 h-3"></i>Edit
                   </button>
               </td>` :
                '<td></td>';

            tr.innerHTML = `
            <td class="abs-td text-sm text-surface-700">
                <div class="flex items-center gap-2.5">
                    <div class="avatar-circle avatar-circle-lg" style="background:${bg};color:${fg};">
                        ${initials(row.name)}
                    </div>
                    <span class="font-medium text-surface-900">${row.name}</span>
                </div>
            </td>
            <td class="abs-td text-center">
                <span class="abs-pill ${pillClass}">
                    ${row.days_absent}
                </span>
            </td>
            <td class="abs-td text-center font-mono text-[0.82rem] text-surface-500">
                ${row.days_present} / ${totalDays}
            </td>
            ${actionCell}
        `;

            tbody.appendChild(tr);

            // Re-render pencil icon using the safe project helper (not createIcons directly)
            if (canEdit) {
                window.setLucideIcon('pencil-' + row.user_id, 'pencil');
            }
        });
    }

    // ── Modal open / close ─────────────────────────────────────────────────────
    function openEditModal(userId, userName, expenseId, period, currentDays, totalDays) {
        document.getElementById('editUserId').value = userId;
        document.getElementById('editExpenseId').value = expenseId;
        document.getElementById('editUserName').textContent = userName;
        document.getElementById('editPeriod').textContent = period;

        const input = document.getElementById('editDaysAbsent');
        input.value = currentDays;
        input.max = totalDays;
        document.getElementById('editDaysHint').textContent =
            'Max ' + totalDays + ' days in this expense period. Set 0 to clear.';

        const backdrop = document.getElementById('editBackdrop');
        const modal = document.getElementById('editModal');
        backdrop.style.display = 'block';
        modal.style.display = 'flex';
        requestAnimationFrame(function() {
            backdrop.style.opacity = '1';
            modal.style.opacity = '1';
            modal.style.transform = 'translate(-50%,-50%) scale(1)';
            input.focus();
            input.select();
        });
    }

    function closeEditModal() {
        const backdrop = document.getElementById('editBackdrop');
        const modal = document.getElementById('editModal');
        backdrop.style.opacity = '0';
        modal.style.opacity = '0';
        modal.style.transform = 'translate(-50%,-50%) scale(.97)';
        setTimeout(function() {
            backdrop.style.display = 'none';
            modal.style.display = 'none';
            document.getElementById('editDaysAbsent').value = '';
        }, 180);
    }

    // ── Save ───────────────────────────────────────────────────────────────────
    function saveAbsentDay() {
        const expenseId = document.getElementById('editExpenseId').value;
        const userId = document.getElementById('editUserId').value;
        const days = parseInt(document.getElementById('editDaysAbsent').value, 10);
        const maxDays = parseInt(document.getElementById('editDaysAbsent').max, 10);

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
            url: '/absentday/upsert',
            type: 'POST',
            data: {
                expense_id: expenseId,
                user_id: userId,
                days_absent: days
            },
            success: function() {
                btn.disabled = false;
                closeEditModal();
                ssToast('Absent days saved successfully.', 'success');
                loadAbsentDay();
            },
            error: function(xhr) {
                btn.disabled = false;
                const msg = xhr.responseJSON && xhr.responseJSON.error ?
                    xhr.responseJSON.error :
                    'Failed to save. Please try again.';
                ssToast(msg, 'error');
            }
        });
    }

    // ── Escape key ─────────────────────────────────────────────────────────────
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeEditModal();
    });
</script>

<?= $this->endSection() ?>
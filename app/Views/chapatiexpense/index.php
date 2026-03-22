<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Chapati Expenses<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Page header ─────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">Chapati Expenses</h1>
        <p class="page-subtitle">Manage monthly chapati billing periods and totals</p>
    </div>
    <button onclick="openAddModal()" class="ss-btn ss-btn-primary">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        Add Period
    </button>
</div>

<!-- ── Info strip ──────────────────────────────────────────────── -->
<div style="
    display:flex;align-items:flex-start;gap:10px;
    padding:12px 16px;margin-bottom:24px;
    background:#fefce8;border:1px solid #fef08a;border-radius:10px;
">
    <i data-lucide="info" style="width:16px;height:16px;color:#a16207;flex-shrink:0;margin-top:1px;"></i>
    <p style="font-size:13px;color:#854d0e;margin:0;line-height:1.6;">
        Each chapati period covers a date range. Absences are recorded per user per period,
        and extra items are tracked separately. The final distribution uses these records
        to calculate each person's share.
    </p>
</div>

<!-- ── Chapati expenses table card ────────────────────────────── -->
<div class="ss-card">
    <div class="ss-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">All Chapati Periods</h2>
            <p style="font-size:13px;color:#94a3b8;margin:3px 0 0;">
                <span id="chapati-count">—</span> periods recorded
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:13px;color:#64748b;">Grand Total:</span>
            <span id="chapati-total" style="font-size:15px;font-weight:700;color:#0f172a;font-family:'JetBrains Mono',monospace;">—</span>
        </div>
    </div>

    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table style="width:100%;border-collapse:collapse;min-width:520px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Type</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Period</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Days</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Total Amount</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody id="chapati-tbody">
                <tr>
                    <td colspan="5" style="padding:48px 16px;text-align:center;color:#cbd5e1;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i data-lucide="loader" style="width:20px;height:20px;color:#cbd5e1;"></i>
                            <span style="font-size:14px;">Loading…</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
     ADD CHAPATI EXPENSE MODAL
════════════════════════════════════════════════════════════════ -->

<!-- Backdrop -->
<div id="modal-backdrop" onclick="closeAddModal()" style="
    display:none;position:fixed;inset:0;
    background:rgba(15,23,42,.45);z-index:100;
    backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);
"></div>

<!-- Modal -->
<div id="add-chapati-modal" style="
    display:none;position:fixed;
    top:50%;left:50%;
    transform:translate(-50%,-50%) scale(0.97);
    width:calc(100% - 32px);max-width:480px;
    background:#fff;border-radius:16px;
    box-shadow:0 20px 60px rgba(0,0,0,.15);
    z-index:101;opacity:0;
    transition:transform .2s ease, opacity .2s ease;
">
    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid #f1f5f9;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:8px;background:#fef9c3;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="utensils" style="width:16px;height:16px;color:#a16207;"></i>
            </div>
            <div>
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Add Chapati Period</h3>
                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0;">Set the billing period and total amount</p>
            </div>
        </div>
        <button onclick="closeAddModal()" style="
            width:32px;height:32px;border-radius:8px;
            background:#f1f5f9;border:none;cursor:pointer;
            display:flex;align-items:center;justify-content:center;
            color:#64748b;transition:background .15s;
        " onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
            <i data-lucide="x" style="width:16px;height:16px;"></i>
        </button>
    </div>

    <!-- Body -->
    <form id="addChapatiExpenseForm" style="padding:20px 24px 24px;">

        <!-- Expense Type -->
        <div style="margin-bottom:16px;">
            <label class="ss-label" for="ce-type">Expense Type <span style="color:#ef4444;">*</span></label>
            <div style="position:relative;">
                <i data-lucide="tag" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                <select id="ce-type" name="expense_type_id" required
                    class="ss-input" style="padding-left:38px;cursor:pointer;appearance:none;-webkit-appearance:none;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    <option value="">— Select type —</option>
                    <?php foreach ($expenseTypes as $type): ?>
                        <option value="<?= $type->id ?>"><?= esc($type->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <i data-lucide="chevron-down" style="position:absolute;right:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
            </div>
        </div>

        <!-- Date range — side by side -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
            <div>
                <label class="ss-label" for="ce-from">From Date <span style="color:#ef4444;">*</span></label>
                <div style="position:relative;">
                    <i data-lucide="calendar" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                    <input type="date" id="ce-from" name="from_date" required
                        class="ss-input" style="padding-left:38px;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                        onchange="updateDaysPreview()">
                </div>
            </div>
            <div>
                <label class="ss-label" for="ce-to">To Date <span style="color:#ef4444;">*</span></label>
                <div style="position:relative;">
                    <i data-lucide="calendar" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                    <input type="date" id="ce-to" name="to_date" required
                        class="ss-input" style="padding-left:38px;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                        onchange="updateDaysPreview()">
                </div>
            </div>
        </div>

        <!-- Days preview -->
        <div id="days-preview" style="display:none;margin-bottom:16px;">
            <div style="
                display:flex;align-items:center;gap:8px;
                padding:10px 14px;background:#f0fdf4;
                border:1px solid #bbf7d0;border-radius:8px;
            ">
                <i data-lucide="check-circle" style="width:15px;height:15px;color:#15803d;flex-shrink:0;"></i>
                <span style="font-size:13px;color:#15803d;font-weight:500;">
                    Period spans <strong id="days-count">0</strong> days
                </span>
            </div>
        </div>

        <!-- Date validation error -->
        <div id="date-error" style="display:none;margin-bottom:16px;">
            <div style="
                display:flex;align-items:center;gap:8px;
                padding:10px 14px;background:#fee2e2;
                border:1px solid #fecaca;border-radius:8px;
            ">
                <i data-lucide="alert-circle" style="width:15px;height:15px;color:#dc2626;flex-shrink:0;"></i>
                <span style="font-size:13px;color:#dc2626;font-weight:500;">To date must be on or after from date.</span>
            </div>
        </div>

        <!-- Total Amount -->
        <div style="margin-bottom:24px;">
            <label class="ss-label" for="ce-amount">Total Amount <span style="color:#ef4444;">*</span></label>
            <div style="position:relative;">
                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:15px;font-weight:600;color:#94a3b8;pointer-events:none;font-family:'JetBrains Mono',monospace;">₹</span>
                <input type="number" id="ce-amount" name="total_amount"
                    placeholder="0.00" min="0" step="0.01" required
                    class="ss-input" style="padding-left:30px;font-family:'JetBrains Mono',monospace;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            </div>
        </div>

        <!-- Actions -->
        <div style="display:flex;gap:10px;">
            <button type="button" onclick="closeAddModal()" class="ss-btn ss-btn-ghost" style="flex:1;">
                Cancel
            </button>
            <button type="submit" id="addChapatiBtn" class="ss-btn ss-btn-primary" style="flex:2;">
                <i data-lucide="plus" style="width:15px;height:15px;" id="addChapatiBtnIcon"></i>
                <span id="addChapatiBtnText">Save Period</span>
            </button>
        </div>

    </form>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
lucide.createIcons();

// ── Helpers ──────────────────────────────────────────────────────
function fmt(n) {
    return '₹' + parseFloat(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtDate(d) {
    if (!d) return '—';
    const raw = typeof d === 'object' ? (d.date || '') : String(d);
    return raw.split(' ')[0] || '—';
}
function daysBetween(from, to) {
    const d1 = new Date(from);
    const d2 = new Date(to);
    return Math.round((d2 - d1) / 86400000) + 1;
}

// ── Days preview on date change ──────────────────────────────────
function updateDaysPreview() {
    const from    = document.getElementById('ce-from').value;
    const to      = document.getElementById('ce-to').value;
    const preview = document.getElementById('days-preview');
    const errBox  = document.getElementById('date-error');

    if (!from || !to) { preview.style.display = 'none'; errBox.style.display = 'none'; return; }

    const days = daysBetween(from, to);
    if (days < 1) {
        preview.style.display = 'none';
        errBox.style.display  = 'block';
        return;
    }
    errBox.style.display  = 'none';
    preview.style.display = 'block';
    document.getElementById('days-count').textContent = days;
}

// ── Load & render chapati expenses ───────────────────────────────
function loadChapati() {
    $.get('/chapatiexpense/getChapatiExpenses', function(res) {
        const records = res.data || [];
        document.getElementById('chapati-count').textContent = records.length;

        const total = records.reduce(function(s, r) { return s + parseFloat(r.total_amount || 0); }, 0);
        document.getElementById('chapati-total').textContent = fmt(total);

        const tbody = document.getElementById('chapati-tbody');

        if (records.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="padding:48px 16px;text-align:center;color:#cbd5e1;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i data-lucide="utensils" style="width:24px;height:24px;color:#e2e8f0;"></i>
                            <span style="font-size:14px;">No chapati periods recorded yet</span>
                        </div>
                    </td>
                </tr>`;
            lucide.createIcons();
            return;
        }

        tbody.innerHTML = records.map(function(r) {
            const fromDate = fmtDate(r.from_date);
            const toDate   = fmtDate(r.to_date);
            const period   = fromDate + ' → ' + toDate;

            // Calculate days for display
            let daysLabel = '—';
            if (r.from_date && r.to_date) {
                const f = typeof r.from_date === 'object' ? r.from_date.date : r.from_date;
                const t = typeof r.to_date   === 'object' ? r.to_date.date   : r.to_date;
                const d = daysBetween(f.split(' ')[0], t.split(' ')[0]);
                daysLabel = d > 0 ? d + ' day' + (d > 1 ? 's' : '') : '—';
            }

            return `<tr style="transition:background .1s;"
                        onmouseover="this.style.background='#f8fafc'"
                        onmouseout="this.style.background=''">

                <!-- Type -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="
                        display:inline-flex;align-items:center;gap:6px;
                        padding:4px 10px;border-radius:999px;
                        font-size:12px;font-weight:600;
                        background:#fef9c3;color:#a16207;
                    ">
                        <i data-lucide="utensils" style="width:11px;height:11px;"></i>
                        ${r.expense_type || '—'}
                    </span>
                </td>

                <!-- Period -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="font-size:13px;color:#334155;font-weight:500;">${period}</span>
                </td>

                <!-- Days -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="
                        display:inline-flex;align-items:center;gap:5px;
                        padding:3px 10px;border-radius:999px;
                        font-size:12px;font-weight:600;
                        background:#dbeafe;color:#1d4ed8;
                    ">
                        <i data-lucide="calendar-days" style="width:11px;height:11px;"></i>
                        ${daysLabel}
                    </span>
                </td>

                <!-- Amount -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="font-size:14px;font-weight:700;color:#0f172a;font-family:'JetBrains Mono',monospace;">
                        ${fmt(r.total_amount)}
                    </span>
                </td>

                <!-- Actions -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                    <button class="deleteChapatiBtn"
                        data-id="${r.id}"
                        style="
                            display:inline-flex;align-items:center;gap:5px;
                            padding:6px 12px;border-radius:6px;
                            background:#fee2e2;color:#dc2626;
                            border:none;cursor:pointer;
                            font-size:12px;font-weight:600;
                            font-family:'DM Sans',sans-serif;
                            min-height:32px;transition:background .15s;
                        "
                        onmouseover="this.style.background='#fecaca'"
                        onmouseout="this.style.background='#fee2e2'">
                        <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                        Delete
                    </button>
                </td>
            </tr>`;
        }).join('');

        lucide.createIcons();

        // Delete handler
        document.querySelectorAll('.deleteChapatiBtn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (!confirm('Delete this chapati period? Linked absences and extra expenses will also be removed.')) return;
                $.ajax({
                    url:  '/chapatiexpense/deleteChapatiExpense/' + id,
                    type: 'DELETE',
                    success: function() {
                        ssToast('Chapati period deleted.', 'success');
                        loadChapati();
                    },
                    error: function() {
                        ssToast('Failed to delete. Make sure the delete method exists in the controller.', 'error');
                    }
                });
            });
        });
    });
}
loadChapati();


// ── Modal open / close ───────────────────────────────────────────
function openAddModal() {
    const backdrop = document.getElementById('modal-backdrop');
    const modal    = document.getElementById('add-chapati-modal');
    backdrop.style.display = 'block';
    modal.style.display    = 'block';
    requestAnimationFrame(function() {
        modal.style.opacity   = '1';
        modal.style.transform = 'translate(-50%,-50%) scale(1)';
    });
    document.getElementById('ce-type').focus();
}
function closeAddModal() {
    const modal    = document.getElementById('add-chapati-modal');
    const backdrop = document.getElementById('modal-backdrop');
    modal.style.opacity   = '0';
    modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
    setTimeout(function() {
        modal.style.display    = 'none';
        backdrop.style.display = 'none';
        document.getElementById('addChapatiExpenseForm').reset();
        document.getElementById('days-preview').style.display = 'none';
        document.getElementById('date-error').style.display   = 'none';
        resetAddBtn();
    }, 180);
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAddModal();
});


// ── Submit button helpers ────────────────────────────────────────
function setAddBtnLoading() {
    const btn  = document.getElementById('addChapatiBtn');
    const text = document.getElementById('addChapatiBtnText');
    const icon = document.getElementById('addChapatiBtnIcon');
    btn.disabled      = true;
    btn.style.opacity = '0.75';
    text.textContent  = 'Saving…';
    icon.setAttribute('data-lucide', 'loader');
    lucide.createIcons();
}
function resetAddBtn() {
    const btn  = document.getElementById('addChapatiBtn');
    const text = document.getElementById('addChapatiBtnText');
    const icon = document.getElementById('addChapatiBtnIcon');
    if (!btn) return;
    btn.disabled      = false;
    btn.style.opacity = '1';
    text.textContent  = 'Save Period';
    icon.setAttribute('data-lucide', 'plus');
    lucide.createIcons();
}


// ── Form submit ──────────────────────────────────────────────────
document.getElementById('addChapatiExpenseForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate date range
    const from = document.getElementById('ce-from').value;
    const to   = document.getElementById('ce-to').value;
    if (from && to && daysBetween(from, to) < 1) {
        document.getElementById('date-error').style.display = 'block';
        return;
    }

    setAddBtnLoading();

    $.post('/chapatiexpense/addChapatiExpense', $(this).serialize(), function(res) {
        if (res.status === 'success') {
            ssToast('Chapati period saved successfully!', 'success');
            closeAddModal();
            loadChapati();
        } else {
            ssToast('Failed to save chapati period.', 'error');
            resetAddBtn();
        }
    }, 'json').fail(function() {
        ssToast('Something went wrong.', 'error');
        resetAddBtn();
    });
});
</script>
<?= $this->endSection() ?>
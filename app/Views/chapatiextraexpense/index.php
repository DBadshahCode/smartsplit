<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Chapati Extra Expenses<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Page header ─────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">Chapati Extra Expenses</h1>
        <p class="page-subtitle">Track one-off items charged within a chapati period</p>
    </div>
    <button onclick="openAddModal()" class="ss-btn ss-btn-primary">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        Add Extra
    </button>
</div>

<!-- ── Info strip ──────────────────────────────────────────────── -->
<div style="
    display:flex;align-items:flex-start;gap:10px;
    padding:12px 16px;margin-bottom:24px;
    background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;
">
    <i data-lucide="info" style="width:16px;height:16px;color:#c2410c;flex-shrink:0;margin-top:1px;"></i>
    <p style="font-size:13px;color:#9a3412;margin:0;line-height:1.6;">
        Extra expenses are additional one-off items (e.g. spices, oil) billed within a chapati period.
        They are split equally among the selected roommates and added to each person's chapati share.
    </p>
</div>

<!-- ── Extra expenses table card ──────────────────────────────── -->
<div class="ss-card">
    <div class="ss-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">All Extra Expenses</h2>
            <p style="font-size:13px;color:#94a3b8;margin:3px 0 0;">
                <span id="extra-count">—</span> items recorded
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:13px;color:#64748b;">Total:</span>
            <span id="extra-total" style="font-size:15px;font-weight:700;color:#0f172a;font-family:'JetBrains Mono',monospace;">—</span>
        </div>
    </div>

    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table style="width:100%;border-collapse:collapse;min-width:520px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Item</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Period</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Amount</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Shared By</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody id="extra-tbody">
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
     ADD EXTRA EXPENSE MODAL
════════════════════════════════════════════════════════════════ -->

<!-- Backdrop -->
<div id="modal-backdrop" onclick="closeAddModal()" style="
    display:none;position:fixed;inset:0;
    background:rgba(15,23,42,.45);z-index:100;
    backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);
"></div>

<!-- Modal — scrollable for mobile -->
<div id="add-extra-modal" style="
    display:none;position:fixed;
    top:50%;left:50%;
    transform:translate(-50%,-50%) scale(0.97);
    width:calc(100% - 32px);max-width:480px;
    max-height:90vh;
    background:#fff;border-radius:16px;
    box-shadow:0 20px 60px rgba(0,0,0,.15);
    z-index:101;opacity:0;
    transition:transform .2s ease, opacity .2s ease;
    flex-direction:column;
">
    <!-- Header — fixed -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid #f1f5f9;flex-shrink:0;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:8px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid #fed7aa;">
                <i data-lucide="plus-circle" style="width:16px;height:16px;color:#c2410c;"></i>
            </div>
            <div>
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Add Extra Expense</h3>
                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0;">Record a one-off item for a chapati period</p>
            </div>
        </div>
        <button onclick="closeAddModal()" style="
            width:32px;height:32px;border-radius:8px;
            background:#f1f5f9;border:none;cursor:pointer;
            display:flex;align-items:center;justify-content:center;
            color:#64748b;transition:background .15s;flex-shrink:0;
        " onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
            <i data-lucide="x" style="width:16px;height:16px;"></i>
        </button>
    </div>

    <!-- Body — scrollable -->
    <div style="overflow-y:auto;-webkit-overflow-scrolling:touch;flex:1;">
        <form id="extraExpenseForm" style="padding:20px 24px 24px;">

            <!-- Chapati Period -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="ex-period">Chapati Period <span style="color:#ef4444;">*</span></label>
                <div style="position:relative;">
                    <i data-lucide="utensils" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                    <select id="ex-period" name="chapati_expense_id" required
                        class="ss-input" style="padding-left:38px;cursor:pointer;appearance:none;-webkit-appearance:none;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                        <option value="">— Select period —</option>
                        <?php foreach ($chapatiExpenses as $exp): ?>
                            <?php
                                $from = is_object($exp->from_date)
                                    ? substr($exp->from_date->toDateString(), 0, 10)
                                    : substr((string) $exp->from_date, 0, 10);
                                $to = is_object($exp->to_date)
                                    ? substr($exp->to_date->toDateString(), 0, 10)
                                    : substr((string) $exp->to_date, 0, 10);
                            ?>
                            <option value="<?= $exp->id ?>">
                                <?= $from ?> → <?= $to ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="chevron-down" style="position:absolute;right:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                </div>
            </div>

            <!-- Item name -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="ex-item">Item Name <span style="color:#ef4444;">*</span></label>
                <div style="position:relative;">
                    <i data-lucide="shopping-bag" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                    <input type="text" id="ex-item" name="item"
                        placeholder="e.g. Cooking oil, Spices"
                        required autocomplete="off"
                        class="ss-input" style="padding-left:38px;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                </div>
            </div>

            <!-- Amount -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="ex-amount">Amount <span style="color:#ef4444;">*</span></label>
                <div style="position:relative;">
                    <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:15px;font-weight:600;color:#94a3b8;pointer-events:none;font-family:'JetBrains Mono',monospace;">₹</span>
                    <input type="number" id="ex-amount" name="amount"
                        placeholder="0.00" min="0" step="0.01" required
                        class="ss-input" style="padding-left:30px;font-family:'JetBrains Mono',monospace;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                </div>
            </div>

            <!-- Per-person preview -->
            <div id="per-person-preview" style="display:none;margin-bottom:16px;">
                <div style="
                    display:flex;align-items:center;justify-content:space-between;
                    padding:10px 14px;background:#f0fdf4;
                    border:1px solid #bbf7d0;border-radius:8px;
                ">
                    <span style="font-size:13px;color:#15803d;font-weight:500;">
                        <i data-lucide="split-square-horizontal" style="width:13px;height:13px;display:inline;vertical-align:middle;"></i>
                        Each person pays
                    </span>
                    <span id="per-person-amount" style="font-size:14px;font-weight:700;color:#15803d;font-family:'JetBrains Mono',monospace;">—</span>
                </div>
            </div>

            <!-- Involved Roommates -->
            <div style="margin-bottom:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <label class="ss-label" style="margin-bottom:0;">Shared By <span style="color:#ef4444;">*</span></label>
                    <div style="display:flex;gap:6px;">
                        <button type="button" onclick="selectAllExtra()" class="ss-btn ss-btn-ghost" style="padding:4px 10px;font-size:12px;min-height:28px;">
                            All
                        </button>
                        <button type="button" onclick="deselectAllExtra()" class="ss-btn ss-btn-ghost" style="padding:4px 10px;font-size:12px;min-height:28px;">
                            None
                        </button>
                    </div>
                </div>

                <div id="extra-users-list" style="
                    border:1px solid #e2e8f0;border-radius:8px;
                    overflow:hidden;max-height:180px;overflow-y:auto;
                    -webkit-overflow-scrolling:touch;
                ">
                    <?php foreach ($users as $user): ?>
                    <label style="
                        display:flex;align-items:center;gap:10px;
                        padding:10px 14px;cursor:pointer;
                        border-bottom:1px solid #f1f5f9;
                        transition:background .1s;
                    " onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <input type="checkbox"
                            name="involved_users[]"
                            value="<?= $user->id ?>"
                            class="extra-user-cb"
                            style="width:16px;height:16px;accent-color:#5c6af0;cursor:pointer;flex-shrink:0;"
                            onchange="updatePerPersonPreview()">
                        <span style="font-size:14px;font-weight:500;color:#334155;"><?= esc($user->name) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <p id="extra-involved-error" style="display:none;font-size:12px;color:#ef4444;margin-top:6px;">
                    Please select at least one roommate.
                </p>
            </div>

            <!-- Actions -->
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closeAddModal()" class="ss-btn ss-btn-ghost" style="flex:1;">
                    Cancel
                </button>
                <button type="submit" id="addExtraBtn" class="ss-btn ss-btn-primary" style="flex:2;">
                    <i data-lucide="plus" style="width:15px;height:15px;" id="addExtraBtnIcon"></i>
                    <span id="addExtraBtnText">Save Extra</span>
                </button>
            </div>

        </form>
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
function fmtDate(d) {
    if (!d) return '—';
    const raw = typeof d === 'object' ? (d.date || '') : String(d);
    return raw.split(' ')[0] || '—';
}

// ── Per-person split preview ─────────────────────────────────────
function updatePerPersonPreview() {
    const amount   = parseFloat(document.getElementById('ex-amount').value) || 0;
    const checked  = document.querySelectorAll('.extra-user-cb:checked').length;
    const preview  = document.getElementById('per-person-preview');
    const perEl    = document.getElementById('per-person-amount');

    if (amount > 0 && checked > 0) {
        perEl.textContent   = fmt(amount / checked);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

// Also update preview when amount changes
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('ex-amount');
    if (amountInput) amountInput.addEventListener('input', updatePerPersonPreview);
});

// ── Involved users helpers ───────────────────────────────────────
function selectAllExtra() {
    document.querySelectorAll('.extra-user-cb').forEach(function(cb) { cb.checked = true; });
    updatePerPersonPreview();
}
function deselectAllExtra() {
    document.querySelectorAll('.extra-user-cb').forEach(function(cb) { cb.checked = false; });
    updatePerPersonPreview();
}

// ── Load & render extra expenses ─────────────────────────────────
function loadExtras() {
    $.get('<?= base_url('chapatiextraexpense/getExtraExpenses') ?>', function(res) {
        const records = res.data || [];
        document.getElementById('extra-count').textContent = records.length;

        const total = records.reduce(function(s, r) { return s + parseFloat(r.amount || 0); }, 0);
        document.getElementById('extra-total').textContent = fmt(total);

        const tbody = document.getElementById('extra-tbody');

        if (records.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="padding:48px 16px;text-align:center;color:#cbd5e1;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i data-lucide="plus-circle" style="width:24px;height:24px;color:#e2e8f0;"></i>
                            <span style="font-size:14px;">No extra expenses recorded yet</span>
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
            const involved = parseInt(r.total_involved) || 0;
            const perPerson = involved > 0
                ? fmt(parseFloat(r.amount || 0) / involved)
                : '—';

            return `<tr style="transition:background .1s;"
                        onmouseover="this.style.background='#f8fafc'"
                        onmouseout="this.style.background=''">

                <!-- Item -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:30px;height:30px;border-radius:8px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid #fed7aa;">
                            <i data-lucide="shopping-bag" style="width:13px;height:13px;color:#c2410c;"></i>
                        </div>
                        <span style="font-size:14px;font-weight:600;color:#0f172a;">${r.item || '—'}</span>
                    </div>
                </td>

                <!-- Period -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="
                        display:inline-flex;align-items:center;gap:6px;
                        padding:4px 10px;border-radius:999px;
                        font-size:12px;font-weight:600;
                        background:#fef9c3;color:#a16207;
                    ">
                        <i data-lucide="utensils" style="width:11px;height:11px;"></i>
                        ${period}
                    </span>
                </td>

                <!-- Amount + per-person -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <div style="display:flex;flex-direction:column;gap:2px;">
                        <span style="font-size:14px;font-weight:700;color:#0f172a;font-family:'JetBrains Mono',monospace;">
                            ${fmt(r.amount)}
                        </span>
                        <span style="font-size:11px;color:#94a3b8;font-family:'JetBrains Mono',monospace;">
                            ${perPerson} / person
                        </span>
                    </div>
                </td>

                <!-- Shared by count -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="
                        display:inline-flex;align-items:center;gap:5px;
                        padding:3px 10px;border-radius:999px;
                        font-size:12px;font-weight:600;
                        background:#dbeafe;color:#1d4ed8;
                    ">
                        <i data-lucide="users" style="width:11px;height:11px;"></i>
                        ${involved} member${involved !== 1 ? 's' : ''}
                    </span>
                </td>

                <!-- Delete -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                    <button class="deleteExtraBtn"
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
        document.querySelectorAll('.deleteExtraBtn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (!confirm('Delete this extra expense?')) return;
                $.ajax({
                    url:  '<?= base_url('chapatiextraexpense/delete') ?>/' + id,
                    type: 'DELETE',
                    success: function() {
                        ssToast('Extra expense deleted.', 'success');
                        loadExtras();
                    },
                    error: function() {
                        ssToast('Failed to delete extra expense.', 'error');
                    }
                });
            });
        });
    });
}
loadExtras();


// ── Modal open / close ───────────────────────────────────────────
function openAddModal() {
    const backdrop = document.getElementById('modal-backdrop');
    const modal    = document.getElementById('add-extra-modal');
    backdrop.style.display = 'block';
    modal.style.display    = 'flex';
    requestAnimationFrame(function() {
        modal.style.opacity   = '1';
        modal.style.transform = 'translate(-50%,-50%) scale(1)';
    });
    document.getElementById('ex-period').focus();
}
function closeAddModal() {
    const modal    = document.getElementById('add-extra-modal');
    const backdrop = document.getElementById('modal-backdrop');
    modal.style.opacity   = '0';
    modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
    setTimeout(function() {
        modal.style.display    = 'none';
        backdrop.style.display = 'none';
        document.getElementById('extraExpenseForm').reset();
        deselectAllExtra();
        document.getElementById('per-person-preview').style.display   = 'none';
        document.getElementById('extra-involved-error').style.display = 'none';
        resetAddBtn();
    }, 180);
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAddModal();
});


// ── Submit button helpers ────────────────────────────────────────
function setAddBtnLoading() {
    const btn  = document.getElementById('addExtraBtn');
    const text = document.getElementById('addExtraBtnText');
    const icon = document.getElementById('addExtraBtnIcon');
    btn.disabled      = true;
    btn.style.opacity = '0.75';
    text.textContent  = 'Saving…';
    icon.setAttribute('data-lucide', 'loader');
    lucide.createIcons();
}
function resetAddBtn() {
    const btn  = document.getElementById('addExtraBtn');
    const text = document.getElementById('addExtraBtnText');
    const icon = document.getElementById('addExtraBtnIcon');
    if (!btn) return;
    btn.disabled      = false;
    btn.style.opacity = '1';
    text.textContent  = 'Save Extra';
    icon.setAttribute('data-lucide', 'plus');
    lucide.createIcons();
}


// ── Form submit ──────────────────────────────────────────────────
document.getElementById('extraExpenseForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate at least one roommate selected
    const checked = document.querySelectorAll('.extra-user-cb:checked');
    if (checked.length === 0) {
        document.getElementById('extra-involved-error').style.display = 'block';
        document.getElementById('extra-users-list').style.borderColor = '#ef4444';
        setTimeout(function() {
            document.getElementById('extra-users-list').style.borderColor = '#e2e8f0';
        }, 2000);
        return;
    }
    document.getElementById('extra-involved-error').style.display = 'none';

    setAddBtnLoading();

    $.post(
        '<?= base_url('chapatiextraexpense/addExtraExpense') ?>',
        $(this).serialize(),
        function(res) {
            // Controller returns {status: true}, not {status: 'success'}
            if (res.status) {
                ssToast('Extra expense saved successfully!', 'success');
                closeAddModal();
                loadExtras();
            } else {
                ssToast('Failed to save extra expense.', 'error');
                resetAddBtn();
            }
        },
        'json'
    ).fail(function() {
        ssToast('Something went wrong.', 'error');
        resetAddBtn();
    });
});
</script>
<?= $this->endSection() ?>
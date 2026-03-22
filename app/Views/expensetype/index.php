<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Expense Types<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Page header ─────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">Expense Types</h1>
        <p class="page-subtitle">Define how each expense category is split between roommates</p>
    </div>
    <button onclick="openAddModal()" class="ss-btn ss-btn-primary">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        Add Type
    </button>
</div>

<!-- ── Split method info pills ─────────────────────────────────── -->
<div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:24px;">

    <div style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
        <div style="width:8px;height:8px;border-radius:50%;background:#5c6af0;flex-shrink:0;"></div>
        <span style="font-size:12px;font-weight:600;color:#475569;">Equal</span>
        <span style="font-size:12px;color:#94a3b8;">— split evenly among all involved</span>
    </div>

    <div style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
        <div style="width:8px;height:8px;border-radius:50%;background:#f59e0b;flex-shrink:0;"></div>
        <span style="font-size:12px;font-weight:600;color:#475569;">Days Present</span>
        <span style="font-size:12px;color:#94a3b8;">— proportional to attendance</span>
    </div>

    <div style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
        <div style="width:8px;height:8px;border-radius:50%;background:#94a3b8;flex-shrink:0;"></div>
        <span style="font-size:12px;font-weight:600;color:#475569;">Custom</span>
        <span style="font-size:12px;color:#94a3b8;">— admin-defined distribution</span>
    </div>

</div>

<!-- ── Expense types table card ────────────────────────────────── -->
<div class="ss-card">
    <div class="ss-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">All Expense Types</h2>
            <p style="font-size:13px;color:#94a3b8;margin:3px 0 0;">
                <span id="type-count">—</span> types configured
            </p>
        </div>
    </div>

    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table style="width:100%;border-collapse:collapse;min-width:520px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Name</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Description</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Split Method</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Status</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody id="types-tbody">
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
     ADD EXPENSE TYPE MODAL
════════════════════════════════════════════════════════════════ -->

<!-- Backdrop -->
<div id="modal-backdrop" onclick="closeAddModal()" style="
    display:none;position:fixed;inset:0;
    background:rgba(15,23,42,.45);z-index:100;
    backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);
"></div>

<!-- Modal -->
<div id="add-type-modal" style="
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
            <div style="width:34px;height:34px;border-radius:8px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="tag" style="width:16px;height:16px;color:#4338ca;"></i>
            </div>
            <div>
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Add Expense Type</h3>
                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0;">Configure name, description and split method</p>
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
    <form id="addExpenseTypeForm" style="padding:20px 24px 24px;">

        <!-- Name -->
        <div style="margin-bottom:16px;">
            <label class="ss-label" for="et-name">Name <span style="color:#ef4444;">*</span></label>
            <div style="position:relative;">
                <i data-lucide="tag" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                <input type="text" id="et-name" name="name"
                    placeholder="e.g. Electricity Bill"
                    required autocomplete="off"
                    class="ss-input" style="padding-left:38px;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom:16px;">
            <label class="ss-label" for="et-desc">
                Description
                <span style="font-size:11px;font-weight:400;color:#94a3b8;margin-left:4px;">optional</span>
            </label>
            <div style="position:relative;">
                <i data-lucide="align-left" style="position:absolute;left:13px;top:13px;width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                <textarea id="et-desc" name="description"
                    placeholder="Brief description of this expense category…"
                    rows="2"
                    class="ss-input" style="padding-left:38px;resize:vertical;min-height:44px;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"></textarea>
            </div>
        </div>

        <!-- Split Method -->
        <div style="margin-bottom:16px;">
            <label class="ss-label" for="et-split">Split Method <span style="color:#ef4444;">*</span></label>

            <!-- Custom radio-style selector -->
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;" id="split-selector">

                <label id="opt-equal" style="
                    display:flex;flex-direction:column;align-items:center;gap:6px;
                    padding:12px 8px;border:2px solid #e2e8f0;border-radius:10px;
                    cursor:pointer;transition:all .15s;text-align:center;
                " onclick="selectSplit('equal')">
                    <div style="width:28px;height:28px;border-radius:8px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="users" style="width:14px;height:14px;color:#4338ca;"></i>
                    </div>
                    <span style="font-size:12px;font-weight:600;color:#334155;">Equal</span>
                    <span style="font-size:10px;color:#94a3b8;line-height:1.3;">Split evenly</span>
                </label>

                <label id="opt-daysPresent" style="
                    display:flex;flex-direction:column;align-items:center;gap:6px;
                    padding:12px 8px;border:2px solid #e2e8f0;border-radius:10px;
                    cursor:pointer;transition:all .15s;text-align:center;
                " onclick="selectSplit('daysPresent')">
                    <div style="width:28px;height:28px;border-radius:8px;background:#fef9c3;display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="calendar-days" style="width:14px;height:14px;color:#a16207;"></i>
                    </div>
                    <span style="font-size:12px;font-weight:600;color:#334155;">Days Present</span>
                    <span style="font-size:10px;color:#94a3b8;line-height:1.3;">By attendance</span>
                </label>

                <label id="opt-custom" style="
                    display:flex;flex-direction:column;align-items:center;gap:6px;
                    padding:12px 8px;border:2px solid #e2e8f0;border-radius:10px;
                    cursor:pointer;transition:all .15s;text-align:center;
                " onclick="selectSplit('custom')">
                    <div style="width:28px;height:28px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="sliders-horizontal" style="width:14px;height:14px;color:#64748b;"></i>
                    </div>
                    <span style="font-size:12px;font-weight:600;color:#334155;">Custom</span>
                    <span style="font-size:10px;color:#94a3b8;line-height:1.3;">Admin defined</span>
                </label>

            </div>
            <!-- Hidden input carries the actual value -->
            <input type="hidden" id="et-split" name="split_method" value="" required>
            <p id="split-error" style="display:none;font-size:12px;color:#ef4444;margin-top:6px;">
                Please select a split method.
            </p>
        </div>

        <!-- Status -->
        <div style="margin-bottom:24px;">
            <label class="ss-label">Status</label>
            <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;">
                    <input type="radio" name="is_active" value="1" checked
                        style="width:16px;height:16px;accent-color:#5c6af0;cursor:pointer;">
                    <span style="font-size:13px;font-weight:500;color:#334155;">Active</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;">
                    <input type="radio" name="is_active" value="0"
                        style="width:16px;height:16px;accent-color:#5c6af0;cursor:pointer;">
                    <span style="font-size:13px;font-weight:500;color:#334155;">Inactive</span>
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div style="display:flex;gap:10px;">
            <button type="button" onclick="closeAddModal()" class="ss-btn ss-btn-ghost" style="flex:1;">
                Cancel
            </button>
            <button type="submit" id="addTypeBtn" class="ss-btn ss-btn-primary" style="flex:2;">
                <i data-lucide="plus" style="width:15px;height:15px;" id="addTypeBtnIcon"></i>
                <span id="addTypeBtnText">Save Type</span>
            </button>
        </div>

    </form>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
lucide.createIcons();

// ── Split method config ──────────────────────────────────────────
const SPLIT_CONFIG = {
    equal:       { label: 'Equal',       bg: '#e0e7ff', fg: '#4338ca', dot: '#5c6af0' },
    daysPresent: { label: 'Days Present', bg: '#fef9c3', fg: '#a16207', dot: '#f59e0b' },
    custom:      { label: 'Custom',      bg: '#f1f5f9', fg: '#64748b', dot: '#94a3b8' },
};

// ── Load & render types ──────────────────────────────────────────
function loadTypes() {
    $.get('<?= base_url('expensetype/getExpenseTypes') ?>', function(res) {
        const types = res.data || [];
        document.getElementById('type-count').textContent = types.length;
        const tbody = document.getElementById('types-tbody');

        if (types.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="padding:48px 16px;text-align:center;color:#cbd5e1;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i data-lucide="tag" style="width:24px;height:24px;color:#e2e8f0;"></i>
                            <span style="font-size:14px;">No expense types yet</span>
                        </div>
                    </td>
                </tr>`;
            lucide.createIcons();
            return;
        }

        tbody.innerHTML = types.map(function(t) {
            const cfg      = SPLIT_CONFIG[t.split_method] || { label: t.split_method, bg: '#f1f5f9', fg: '#64748b', dot: '#94a3b8' };
            const isActive = t.is_active == 1 || t.is_active === true;

            return `<tr style="transition:background .1s;"
                        onmouseover="this.style.background='#f8fafc'"
                        onmouseout="this.style.background=''">

                <!-- Name -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="font-size:14px;font-weight:600;color:#0f172a;">${t.name || '—'}</span>
                </td>

                <!-- Description -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;max-width:220px;">
                    <span style="font-size:13px;color:#64748b;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${t.description || '<span style="color:#cbd5e1;font-style:italic;">No description</span>'}
                    </span>
                </td>

                <!-- Split method badge -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="
                        display:inline-flex;align-items:center;gap:6px;
                        padding:4px 10px;border-radius:999px;
                        font-size:12px;font-weight:600;
                        background:${cfg.bg};color:${cfg.fg};
                    ">
                        <span style="width:6px;height:6px;border-radius:50%;background:${cfg.dot};flex-shrink:0;"></span>
                        ${cfg.label}
                    </span>
                </td>

                <!-- Status badge -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="
                        display:inline-flex;align-items:center;gap:5px;
                        padding:3px 10px;border-radius:999px;
                        font-size:12px;font-weight:600;
                        background:${isActive ? '#dcfce7' : '#fee2e2'};
                        color:${isActive ? '#15803d' : '#dc2626'};
                    ">
                        <i data-lucide="${isActive ? 'check-circle' : 'x-circle'}" style="width:11px;height:11px;"></i>
                        ${isActive ? 'Active' : 'Inactive'}
                    </span>
                </td>

                <!-- Delete -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                    <button class="deleteTypeBtn"
                        data-id="${t.id}" data-name="${t.name}"
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
        document.querySelectorAll('.deleteTypeBtn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id   = this.dataset.id;
                const name = this.dataset.name;
                if (!confirm(`Delete expense type "${name}"? This cannot be undone.`)) return;
                $.ajax({
                    url:  '<?= base_url('expensetype/deleteExpenseType') ?>/' + id,
                    type: 'DELETE',
                    success: function(res) {
                        ssToast('Expense type deleted.', 'success');
                        loadTypes();
                    },
                    error: function() {
                        ssToast('Failed to delete expense type.', 'error');
                    }
                });
            });
        });
    });
}
loadTypes();


// ── Split method selector ────────────────────────────────────────
let selectedSplit = '';

function selectSplit(value) {
    selectedSplit = value;
    document.getElementById('et-split').value = value;
    document.getElementById('split-error').style.display = 'none';

    ['equal', 'daysPresent', 'custom'].forEach(function(v) {
        const el = document.getElementById('opt-' + v);
        if (!el) return;
        if (v === value) {
            el.style.borderColor  = '#5c6af0';
            el.style.background   = '#f0f4ff';
            el.style.boxShadow    = '0 0 0 3px rgba(92,106,240,.12)';
        } else {
            el.style.borderColor  = '#e2e8f0';
            el.style.background   = '#fff';
            el.style.boxShadow    = 'none';
        }
    });
}


// ── Modal open / close ───────────────────────────────────────────
function openAddModal() {
    const backdrop = document.getElementById('modal-backdrop');
    const modal    = document.getElementById('add-type-modal');
    backdrop.style.display = 'block';
    modal.style.display    = 'block';
    requestAnimationFrame(function() {
        modal.style.opacity   = '1';
        modal.style.transform = 'translate(-50%,-50%) scale(1)';
    });
    document.getElementById('et-name').focus();
}

function closeAddModal() {
    const modal    = document.getElementById('add-type-modal');
    const backdrop = document.getElementById('modal-backdrop');
    modal.style.opacity   = '0';
    modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
    setTimeout(function() {
        modal.style.display    = 'none';
        backdrop.style.display = 'none';
        document.getElementById('addExpenseTypeForm').reset();
        selectSplit('');
        selectedSplit = '';
        resetAddBtn();
    }, 180);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAddModal();
});


// ── Submit button helpers ────────────────────────────────────────
function setAddBtnLoading() {
    const btn  = document.getElementById('addTypeBtn');
    const text = document.getElementById('addTypeBtnText');
    const icon = document.getElementById('addTypeBtnIcon');
    btn.disabled      = true;
    btn.style.opacity = '0.75';
    text.textContent  = 'Saving…';
    icon.setAttribute('data-lucide', 'loader');
    lucide.createIcons();
}
function resetAddBtn() {
    const btn  = document.getElementById('addTypeBtn');
    const text = document.getElementById('addTypeBtnText');
    const icon = document.getElementById('addTypeBtnIcon');
    if (!btn) return;
    btn.disabled      = false;
    btn.style.opacity = '1';
    text.textContent  = 'Save Type';
    icon.setAttribute('data-lucide', 'plus');
    lucide.createIcons();
}


// ── Form submit ──────────────────────────────────────────────────
document.getElementById('addExpenseTypeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate split method selected
    if (!selectedSplit) {
        document.getElementById('split-error').style.display = 'block';
        document.getElementById('split-selector').style.animation = 'none';
        // Shake the selector
        const sel = document.getElementById('split-selector');
        sel.style.transform = 'translateX(-6px)';
        setTimeout(function() { sel.style.transform = 'translateX(6px)'; }, 80);
        setTimeout(function() { sel.style.transform = 'translateX(0)';   }, 160);
        return;
    }

    setAddBtnLoading();

    $.post(
        '<?= base_url('expensetype/addExpenseType') ?>',
        $(this).serialize(),
        function(res) {
            if (res.status === 'success') {
                ssToast('Expense type saved successfully!', 'success');
                closeAddModal();
                loadTypes();
            } else {
                ssToast('Failed to save expense type.', 'error');
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
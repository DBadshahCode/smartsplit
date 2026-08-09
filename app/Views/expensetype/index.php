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
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add Type
    </button>
</div>

<!-- ── Split method legend ─────────────────────────────────────── -->
<div class="flex flex-wrap gap-2.5 mb-6">

    <div class="legend-pill">
        <div class="legend-dot" style="background:#5c6af0;"></div>
        <span class="legend-label">Equal</span>
        <span class="legend-desc">— split evenly among all involved</span>
    </div>

    <div class="legend-pill">
        <div class="legend-dot" style="background:#f59e0b;"></div>
        <span class="legend-label">Days Present</span>
        <span class="legend-desc">— proportional to attendance</span>
    </div>

    <div class="legend-pill">
        <div class="legend-dot" style="background:#94a3b8;"></div>
        <span class="legend-label">Custom</span>
        <span class="legend-desc">— admin-defined distribution</span>
    </div>

</div>

<!-- ── Expense types table card ────────────────────────────────── -->
<div class="ss-card">
    <div class="ss-card-header flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-[15px] font-bold text-surface-900 m-0">All Expense Types</h2>
            <p class="text-[13px] text-surface-400 mt-[3px] mb-0">
                <span id="type-count">—</span> types configured
            </p>
        </div>
    </div>

    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table class="w-full border-collapse" style="min-width:520px;">
            <thead>
                <tr class="bg-surface-50">
                    <th class="abs-th text-left">Name</th>
                    <th class="abs-th text-left">Description</th>
                    <th class="abs-th text-left">Split Method</th>
                    <th class="abs-th text-left">Status</th>
                    <th class="abs-th text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="types-tbody">
                <tr>
                    <td colspan="5" class="mini-table-empty-td">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="loader" class="w-5 h-5 text-surface-200"></i>
                            <span class="text-sm">Loading…</span>
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
<div id="modal-backdrop" onclick="closeAddModal()" class="modal-backdrop" style="z-index:100;"></div>

<div id="add-type-modal" class="modal-panel modal-shell" style="z-index:101;max-width:480px;">
    <div class="modal-header">
        <div class="modal-header-info">
            <div class="modal-header-icon bg-indigo-100">
                <i data-lucide="tag" class="w-4 h-4 text-indigo-700"></i>
            </div>
            <div>
                <h3 class="modal-header-title">Add Expense Type</h3>
                <p class="modal-header-subtitle">Configure name, description and split method</p>
            </div>
        </div>
        <button onclick="closeAddModal()" class="icon-btn icon-btn-muted icon-btn-sm">
            <i data-lucide="x"></i>
        </button>
    </div>

    <div class="modal-body">
        <form id="addExpenseTypeForm" class="modal-form">

            <!-- Name -->
            <div class="field-group">
                <label class="ss-label" for="et-name">Name <span class="required-star">*</span></label>
                <div class="field-icon-wrap">
                    <i data-lucide="tag" class="field-icon"></i>
                    <input type="text" id="et-name" name="name" placeholder="e.g. Regular Expense" required
                        autocomplete="off" class="ss-input pl-[38px]">
                </div>
            </div>

            <!-- Description -->
            <div class="field-group">
                <label class="ss-label" for="et-desc">
                    Description
                    <span class="text-[11px] font-normal text-surface-400 ml-1">optional</span>
                </label>
                <div class="field-icon-wrap">
                    <i data-lucide="align-left" class="field-icon" style="top:13px;transform:none;"></i>
                    <textarea id="et-desc" name="description" placeholder="Brief description of this expense category…"
                        rows="2" class="ss-input pl-[38px]" style="resize:vertical;min-height:44px;"></textarea>
                </div>
            </div>

            <!-- Split Method -->
            <div class="field-group">
                <label class="ss-label" for="et-split">Split Method <span class="required-star">*</span></label>

                <!-- Custom radio-style selector -->
                <div class="grid grid-cols-3 gap-2" id="split-selector">

                    <label id="opt-equal" class="split-option" onclick="selectSplit('equal')">
                        <div class="split-option-icon bg-indigo-100">
                            <i data-lucide="users" class="w-[14px] h-[14px] text-indigo-700"></i>
                        </div>
                        <span class="split-option-label">Equal</span>
                        <span class="split-option-hint">Split evenly</span>
                    </label>

                    <label id="opt-daysPresent" class="split-option" onclick="selectSplit('daysPresent')">
                        <div class="split-option-icon bg-yellow-100">
                            <i data-lucide="calendar-days" class="w-[14px] h-[14px] text-yellow-700"></i>
                        </div>
                        <span class="split-option-label">Days Present</span>
                        <span class="split-option-hint">By attendance</span>
                    </label>

                    <label id="opt-custom" class="split-option" onclick="selectSplit('custom')">
                        <div class="split-option-icon bg-surface-100">
                            <i data-lucide="sliders-horizontal" class="w-[14px] h-[14px] text-surface-500"></i>
                        </div>
                        <span class="split-option-label">Custom</span>
                        <span class="split-option-hint">Admin defined</span>
                    </label>

                </div>
                <!-- Hidden input carries the actual value -->
                <input type="hidden" id="et-split" name="split_method" value="" required>
                <p id="split-error" class="field-hint hidden">
                    Please select a split method.
                </p>
            </div>

            <!-- Status -->
            <div class="field-group-lg">
                <label class="ss-label">Status</label>
                <div class="radio-pill-row">
                    <label class="radio-pill">
                        <input type="radio" name="is_active" value="1" checked>
                        <span>Active</span>
                    </label>
                    <label class="radio-pill">
                        <input type="radio" name="is_active" value="0">
                        <span>Inactive</span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2.5">
                <button type="button" onclick="closeAddModal()" class="ss-btn ss-btn-ghost flex-1">
                    Cancel
                </button>
                <button type="submit" id="addTypeBtn" class="ss-btn ss-btn-primary flex-[2]">
                    <i data-lucide="plus" class="w-[15px] h-[15px]" id="addTypeBtnIcon"></i>
                    <span id="addTypeBtnText">Save Type</span>
                </button>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    lucide.createIcons();

    // ══════════════════════════════════════════════════════════════
    // SPLIT_CONFIG — now maps to a badge CLASS instead of raw hex.
    // 'equal' and 'daysPresent'/'custom' map onto badge colors that
    // already exist in app.css (.ss-badge-indigo, .ss-badge-gray) or
    // a new one added below (.ss-badge-yellow) — see the small CSS
    // addendum at the bottom of this message.
    // ══════════════════════════════════════════════════════════════
    const SPLIT_CONFIG = {
        equal: {
            label: 'Equal',
            badgeClass: 'ss-badge-indigo',
            dot: '#5c6af0'
        },
        daysPresent: {
            label: 'Days Present',
            badgeClass: 'ss-badge-yellow',
            dot: '#f59e0b'
        },
        custom: {
            label: 'Custom',
            badgeClass: 'ss-badge-gray',
            dot: '#94a3b8'
        },
    };

    // ══════════════════════════════════════════════════════════════
    // loadTypes() — cells use .abs-td (already used for the header via
    // .abs-th in batch 1), badges use .ss-badge-* classes, and the
    // Delete button reuses .row-action-btn/.row-action-delete from the
    // expense-view pass instead of its own one-off inline style +
    // onmouseover/onmouseout. Row hover also no longer needs its own
    // handler — #types-tbody's wrapper already has `.ss-table-wrap`,
    // which already carries a `tbody tr:hover` rule in app.css.
    // ══════════════════════════════════════════════════════════════
    function loadTypes() {
        $.get('<?= base_url('expensetype/getExpenseTypes') ?>', function(res) {
            const types = res.data || [];
            document.getElementById('type-count').textContent = types.length;
            const tbody = document.getElementById('types-tbody');

            if (types.length === 0) {
                tbody.innerHTML = `
            <tr>
                <td colspan="5" class="mini-table-empty-td">
                    <div class="flex flex-col items-center gap-2">
                        <i data-lucide="tag" class="w-6 h-6 text-surface-200"></i>
                        <span class="text-sm">No expense types yet</span>
                    </div>
                </td>
            </tr>`;
                lucide.createIcons();
                return;
            }

            tbody.innerHTML = types.map(function(t) {
                const cfg = SPLIT_CONFIG[t.split_method] || {
                    label: t.split_method,
                    badgeClass: 'ss-badge-gray',
                    dot: '#94a3b8'
                };
                const isActive = t.is_active == 1 || t.is_active === true;

                return `<tr style="border-bottom:1px solid #f1f5f9;">
 
            <!-- Name -->
            <td class="abs-td" style="white-space:nowrap;">
                <span class="text-sm font-semibold text-surface-900">${t.name || '—'}</span>
            </td>
 
            <!-- Description -->
            <td class="abs-td">
                <span class="dt-cell-truncate text-[13px] text-surface-500" style="max-width:220px;">
                    ${t.description || '<span class="dt-empty">No description</span>'}
                </span>
            </td>
 
            <!-- Split method badge -->
            <td class="abs-td" style="white-space:nowrap;">
                <span class="ss-badge ${cfg.badgeClass}" style="gap:6px;">
                    <span style="width:6px;height:6px;border-radius:50%;background:${cfg.dot};flex-shrink:0;"></span>
                    ${cfg.label}
                </span>
            </td>
 
            <!-- Status badge -->
            <td class="abs-td" style="white-space:nowrap;">
                <span class="ss-badge ${isActive ? 'ss-badge-green' : 'ss-badge-red'}" style="gap:5px;">
                    <i data-lucide="${isActive ? 'check-circle' : 'x-circle'}" class="w-[11px] h-[11px]"></i>
                    ${isActive ? 'Active' : 'Inactive'}
                </span>
            </td>
 
            <!-- Delete -->
            <td class="abs-td text-right" style="white-space:nowrap;">
                <button class="deleteTypeBtn row-action-btn row-action-delete" data-id="${t.id}" data-name="${t.name}">
                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                    Delete
                </button>
            </td>
        </tr>`;
            }).join('');

            lucide.createIcons();

            // Delete handler
            document.querySelectorAll('.deleteTypeBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    ssConfirm({
                        title: 'Delete Expense Type',
                        message: `Are you sure you want to delete expense type "${name}"? This cannot be undone.`,
                        confirmText: 'Delete',
                        onConfirm: function() {
                            $.ajax({
                                url: '/expensetype/deleteExpenseType/' + id,
                                type: 'DELETE',
                                success: function() {
                                    ssToast('Expense type deleted.', 'success');
                                    loadTypes();
                                },
                                error: function() {
                                    ssToast('Failed to delete expense type.', 'error');
                                }
                            });
                        }
                    });
                });
            });
        });
    }
    loadTypes();


    // ══════════════════════════════════════════════════════════════
    // selectSplit() — toggles the `.selected` modifier class instead
    // of writing borderColor/background/boxShadow on each option by hand.
    // ══════════════════════════════════════════════════════════════
    let selectedSplit = '';

    function selectSplit(value) {
        selectedSplit = value;
        document.getElementById('et-split').value = value;
        document.getElementById('split-error').classList.add('hidden');

        ['equal', 'daysPresent', 'custom'].forEach(function(v) {
            const el = document.getElementById('opt-' + v);
            if (!el) return;
            el.classList.toggle('selected', v === value);
        });
    }


    // ── Modal open / close ───────────────────────────────────────────
    function openAddModal() {
        const backdrop = document.getElementById('modal-backdrop');
        const modal = document.getElementById('add-type-modal');
        backdrop.style.display = 'block';
        modal.style.display = 'flex';
        requestAnimationFrame(function() {
            modal.style.opacity = '1';
            modal.style.transform = 'translate(-50%,-50%) scale(1)';
        });
        document.getElementById('et-name').focus();
    }

    function closeAddModal() {
        const modal = document.getElementById('add-type-modal');
        const backdrop = document.getElementById('modal-backdrop');
        modal.style.opacity = '0';
        modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
        setTimeout(function() {
            modal.style.display = 'none';
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


    // ══════════════════════════════════════════════════════════════
    // setAddBtnLoading() / resetAddBtn() — THE ACTUAL BUG FIX.
    // These were calling icon.setAttribute('data-lucide', ...) followed
    // by lucide.createIcons() directly. Per your own project notes, that
    // does NOT re-render an icon that Lucide has already swapped from
    // <i> to <svg> — so the button's icon was silently never changing
    // between "plus" and "loader" on submit. Fixed to use the safe
    // project helper.
    // ══════════════════════════════════════════════════════════════
    function setAddBtnLoading() {
        const btn = document.getElementById('addTypeBtn');
        const text = document.getElementById('addTypeBtnText');
        btn.disabled = true;
        btn.style.opacity = '0.75';
        text.textContent = 'Saving…';
        window.setLucideIcon('addTypeBtnIcon', 'loader');
    }

    function resetAddBtn() {
        const btn = document.getElementById('addTypeBtn');
        const text = document.getElementById('addTypeBtnText');
        if (!btn) return;
        btn.disabled = false;
        btn.style.opacity = '1';
        text.textContent = 'Save Type';
        window.setLucideIcon('addTypeBtnIcon', 'plus');
    }


    // ══════════════════════════════════════════════════════════════
    // Form submit — only the split-method validation branch changed
    // (classList instead of style.display). The $.post(...) success/
    // fail handling below it is untouched.
    // ══════════════════════════════════════════════════════════════
    document.getElementById('addExpenseTypeForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!selectedSplit) {
            document.getElementById('split-error').classList.remove('hidden');
            const sel = document.getElementById('split-selector');
            sel.style.transform = 'translateX(-6px)';
            setTimeout(function() {
                sel.style.transform = 'translateX(6px)';
            }, 80);
            setTimeout(function() {
                sel.style.transform = 'translateX(0)';
            }, 160);
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
<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Expenses<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
/**
 * @var array{
 *     id: int,
 *     name: string,
 *     role: string,
 *    isLoggedIn: bool
 * } $currentUser
 * @var array $expenseTypes
 * @var array $users
 */

$isAdmin = $currentUser['role'] === 'admin';
?>

<!-- ── Page header ─────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">Expenses</h1>
        <p class="page-subtitle">Track and manage all shared expenses</p>
    </div>
    <button onclick="openAddModal()" class="ss-btn ss-btn-primary">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add Expense
    </button>
</div>

<!-- ── Expenses table card ─────────────────────────────────────── -->
<div class="ss-card">
    <div class="ss-card-header flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-[15px] font-bold text-surface-900 m-0">All Expenses</h2>
            <p class="text-[13px] text-surface-400 mt-[3px] mb-0">
                <span id="expense-count">—</span> expenses recorded
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="text-[13px] text-surface-500">Total:</span>
                <span id="expense-total" class="text-[15px] font-bold text-surface-900 font-mono">—</span>
            </div>
            <!-- View toggle -->
            <div class="flex items-center gap-0.5 bg-surface-100 rounded-lg p-[3px]">
                <button id="btn-view-list" onclick="setView('list')" title="List view"
                    class="w-8 h-8 rounded-md border-none cursor-pointer flex items-center justify-center transition-all duration-150"
                    style="background:#fff;color:#5c6af0;">
                    <i data-lucide="list" class="w-[15px] h-[15px]"></i>
                </button>
                <button id="btn-view-grid" onclick="setView('grid')" title="Grid view"
                    class="w-8 h-8 rounded-md border-none cursor-pointer flex items-center justify-center transition-all duration-150"
                    style="background:transparent;color:#94a3b8;">
                    <i data-lucide="layout-grid" class="w-[15px] h-[15px]"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ── Bulk actions bar (admin only) ── -->
    <?php if ($isAdmin): ?>
        <div id="bulk-actions-bar"
            class="hidden items-center justify-between gap-3 px-5 py-2.5 bg-indigo-50 border-b border-indigo-100 flex-wrap">
            <div class="flex items-center gap-2.5 flex-wrap">
                <i data-lucide="check-square" class="w-[15px] h-[15px] text-indigo-700"></i>
                <span class="text-[13px] font-semibold text-indigo-700">
                    <span id="bulk-selected-count">0</span> selected
                </span>
                <button onclick="clearSelection()"
                    class="bg-transparent border-none text-brand-500 text-xs font-semibold cursor-pointer underline p-0">
                    Clear
                </button>
                <span id="select-all-matching-link" class="hidden">
                    <button onclick="selectAllMatching()"
                        class="bg-transparent border-none text-brand-500 text-xs font-semibold cursor-pointer underline p-0">
                        Select all <span id="select-all-matching-count">0</span> expenses
                    </button>
                </span>
            </div>
            <button onclick="bulkDeleteExpenses()" class="ss-btn ss-btn-danger"
                style="padding:7px 14px;font-size:12px;min-height:auto;">
                <i data-lucide="trash-2" class="w-[13px] h-[13px]"></i>
                Delete Selected
            </button>
        </div>
    <?php endif; ?>

    <!-- ── List view ── -->
    <div id="expenses-list-wrap" class="ss-table-wrap" style="border:none;border-radius:0;">
        <table class="w-full border-collapse" style="min-width:680px;">
            <thead>
                <tr>
                    <?php if ($isAdmin): ?>
                        <th data-ss-static="1" class="w-9 px-4 py-3.5 border-b border-surface-100">
                            <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll(this)"
                                class="w-4 h-4 cursor-pointer">
                        </th>
                    <?php endif; ?>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="expenses-tbody"></tbody>
        </table>
    </div>

    <!-- ── Grid view ── -->
    <div id="expenses-grid-wrap" class="hidden p-3">
        <div id="expenses-grid" class="grid gap-3" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));">
        </div>
        <div id="expenses-grid-empty" class="hidden py-10 px-4 text-center">
            <div class="flex flex-col items-center gap-2">
                <div class="w-12 h-12 rounded-xl bg-surface-50 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-[22px] h-[22px] text-surface-200"></i>
                </div>
                <span class="text-sm text-surface-400 font-medium">No expenses found</span>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
     ADD EXPENSE MODAL
════════════════════════════════════════════════════════════════ -->
<div id="modal-backdrop" onclick="closeAddModal()" class="modal-backdrop" style="z-index:100;"></div>

<div id="add-expense-modal" class="modal-panel modal-shell" style="z-index:101;">
    <div class="modal-header">
        <div class="modal-header-info">
            <div class="modal-header-icon bg-pink-100">
                <i data-lucide="receipt" class="w-4 h-4 text-pink-700"></i>
            </div>
            <div>
                <h3 class="modal-header-title">Add New Expense</h3>
                <p class="modal-header-subtitle">Fill in the expense details below</p>
            </div>
        </div>
        <button onclick="closeAddModal()" class="icon-btn icon-btn-muted icon-btn-sm">
            <i data-lucide="x"></i>
        </button>
    </div>

    <div class="modal-body">
        <form id="addExpenseForm" class="modal-form">

            <!-- Expense Type -->
            <div class="field-group">
                <label class="ss-label" for="exp-type">Expense Type <span class="required-star">*</span></label>
                <div class="field-icon-wrap">
                    <i data-lucide="tag" class="field-icon"></i>
                    <select id="exp-type" name="expense_type_id" required class="ss-input ss-input-icon"
                        onchange="onAddTypeChange(this.value)">
                        <option value="">— Select type —</option>
                        <?php foreach ($expenseTypes as $type): ?>
                            <option value="<?= $type->id ?>"><?= esc($type->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="chevron-down" class="field-icon-trail"></i>
                </div>
            </div>

            <!-- Description -->
            <div class="field-group">
                <label class="ss-label" for="exp-description">Description</label>
                <div class="field-icon-wrap">
                    <i data-lucide="edit-3" class="field-icon"></i>
                    <input type="text" id="exp-description" name="description" placeholder="e.g. Rent of Jan 2026"
                        class="ss-input pl-[38px]">
                </div>
            </div>

            <!-- Amount -->
            <div class="field-group">
                <label class="ss-label" for="exp-amount">Amount <span class="required-star">*</span></label>
                <div class="field-icon-wrap">
                    <span class="field-prefix">₹</span>
                    <input type="number" id="exp-amount" name="amount" placeholder="0.00" min="0" step="0.01" required
                        class="ss-input ss-input-amount">
                </div>
            </div>

            <!-- Billing Month -->
            <div class="field-group">
                <label class="ss-label" for="exp-billing-month">
                    Billing Month <span class="required-star">*</span>
                </label>
                <div class="field-icon-wrap">
                    <i data-lucide="calendar-range" class="field-icon"></i>
                    <input type="month" id="exp-billing-month" name="billing_month" value="<?= date('Y-m') ?>" required
                        class="ss-input pl-[38px]">
                </div>
            </div>

            <!-- Date range -->
            <div id="add-date-range-wrap" class="field-group">
                <div id="add-date-range-hint" class="hint-box hint-warn">
                    <i data-lucide="info" class="w-[13px] h-[13px] flex-shrink-0"></i>
                    <span id="add-date-range-hint-text">Select an expense type to see if dates are required.</span>
                </div>
                <label class="ss-label" for="exp-from">
                    Date Range <span id="add-from-required-star" class="required-star hidden">*</span>
                </label>
                <div id="add-daterange-box" class="daterange-box">
                    <i data-lucide="calendar" class="field-icon"></i>
                    <input type="date" id="exp-from" name="from_date" value="<?= date('Y-m-d') ?>"
                        class="daterange-input">
                    <span class="daterange-sep">&#8594;</span>
                    <input type="date" id="exp-to" name="to_date" value="<?= date('Y-m-d') ?>" class="daterange-input">
                </div>
                <p id="add-date-error" class="field-hint hidden">
                    From Date and To Date are required for this expense type.
                </p>
            </div>

            <!-- Paid By -->
            <div class="field-group">
                <label class="ss-label">
                    Paid By
                    <span class="text-[11px] font-normal text-surface-400 ml-1">optional — can be set later</span>
                </label>
                <?php if ($isAdmin): ?>
                    <div class="field-icon-wrap">
                        <i data-lucide="user" class="field-icon"></i>
                        <select name="paid_by" class="ss-input ss-input-icon">
                            <option value="">— Not paid yet —</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user->id ?>"><?= esc($user->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down" class="field-icon-trail"></i>
                    </div>
                <?php else: ?>
                    <div class="radio-pill-group">
                        <div class="radio-pill-row">
                            <label class="radio-pill">
                                <input type="radio" name="paid_by_option" value="me" id="pbo-me"
                                    onchange="togglePaidBy(this.value)">
                                <span>Paid by me</span>
                            </label>
                            <label class="radio-pill">
                                <input type="radio" name="paid_by_option" value="none" id="pbo-none" checked
                                    onchange="togglePaidBy(this.value)">
                                <span>Not paid yet</span>
                            </label>
                        </div>
                        <input type="hidden" name="paid_by" id="paid-by-value" value="">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Involved Roommates -->
            <div class="field-group-lg">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="ss-label mb-0">Involved Roommates <span class="required-star">*</span></label>
                    <div class="flex gap-1.5">
                        <button type="button" onclick="selectAllUsers()" class="ss-btn ss-btn-ghost"
                            style="padding:4px 10px;font-size:12px;min-height:28px;">All</button>
                        <button type="button" onclick="deselectAllUsers()" class="ss-btn ss-btn-ghost"
                            style="padding:4px 10px;font-size:12px;min-height:28px;">None</button>
                    </div>
                </div>
                <div id="involved-users-list" class="check-list">
                    <?php foreach ($users as $user): ?>
                        <label class="check-list-item">
                            <input type="checkbox" name="involved_users[]" value="<?= $user->id ?>">
                            <span><?= esc($user->name) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p id="involved-error" class="field-hint hidden">
                    Please select at least one roommate.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex gap-2.5">
                <button type="button" onclick="closeAddModal()" class="ss-btn ss-btn-ghost flex-1">Cancel</button>
                <button type="submit" id="addExpenseBtn" class="ss-btn ss-btn-primary flex-[2]">
                    <i data-lucide="plus" class="w-[15px] h-[15px]" id="addExpenseBtnIcon"></i>
                    <span id="addExpenseBtnText">Save Expense</span>
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
     EDIT EXPENSE MODAL
════════════════════════════════════════════════════════════════ -->
<div id="edit-modal-backdrop" onclick="closeEditModal()" class="modal-backdrop" style="z-index:100;"></div>

<div id="edit-expense-modal" class="modal-panel modal-shell" style="z-index:101;">
    <div class="modal-header">
        <div class="modal-header-info">
            <div class="modal-header-icon bg-indigo-100">
                <i data-lucide="pencil" class="w-4 h-4 text-indigo-700"></i>
            </div>
            <div>
                <h3 class="modal-header-title">Edit Expense</h3>
                <p class="modal-header-subtitle">Update the expense details below</p>
            </div>
        </div>
        <button onclick="closeEditModal()" class="icon-btn icon-btn-muted icon-btn-sm">
            <i data-lucide="x"></i>
        </button>
    </div>

    <div class="modal-body">

        <div id="edit-permission-denied" class="hidden px-6 py-5">
            <div class="flex flex-col items-center justify-center gap-3 py-8 px-6 text-center">
                <div class="w-14 h-14 rounded-xl bg-red-100 flex items-center justify-center">
                    <i data-lucide="lock" class="w-6 h-6 text-red-600"></i>
                </div>
                <div>
                    <h4 class="text-[15px] font-bold text-surface-900 mb-1.5">Cannot Edit This Expense</h4>
                    <p class="text-[13px] text-surface-500 m-0 leading-relaxed">
                        You can only edit expenses that you paid for or expenses with no assigned payer.
                    </p>
                </div>
            </div>
        </div>

        <form id="editExpenseForm" class="modal-form">
            <input type="hidden" id="edit-expense-id" name="expense_id" value="">

            <!-- Expense Type -->
            <div class="field-group">
                <label class="ss-label" for="edit-exp-type">Expense Type <span class="required-star">*</span></label>
                <div class="field-icon-wrap">
                    <i data-lucide="tag" class="field-icon"></i>
                    <select id="edit-exp-type" name="expense_type_id" required class="ss-input ss-input-icon"
                        onchange="onEditTypeChange(this.value)">
                        <option value="">— Select type —</option>
                        <?php foreach ($expenseTypes as $type): ?>
                            <option value="<?= $type->id ?>"><?= esc($type->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="chevron-down" class="field-icon-trail"></i>
                </div>
            </div>

            <!-- Description -->
            <div class="field-group">
                <label class="ss-label" for="edit-exp-description">Description</label>
                <input type="text" id="edit-exp-description" name="description" placeholder="e.g. Rent"
                    class="ss-input">
            </div>

            <!-- Amount -->
            <div class="field-group">
                <label class="ss-label" for="edit-exp-amount">Amount <span class="required-star">*</span></label>
                <div class="field-icon-wrap">
                    <span class="field-prefix">₹</span>
                    <input type="number" id="edit-exp-amount" name="amount" placeholder="0.00" min="0" step="0.01"
                        required class="ss-input ss-input-amount">
                </div>
            </div>

            <!-- Billing Month -->
            <div class="field-group">
                <label class="ss-label" for="edit-exp-billing-month">
                    Billing Month <span class="required-star">*</span>
                </label>
                <div class="field-icon-wrap">
                    <i data-lucide="calendar-range" class="field-icon"></i>
                    <input type="month" id="edit-exp-billing-month" name="billing_month" required
                        class="ss-input pl-[38px]">
                </div>
            </div>

            <!-- Date range -->
            <div id="edit-date-range-wrap" class="field-group">
                <div id="edit-date-range-hint" class="hint-box hint-warn">
                    <i data-lucide="info" class="w-[13px] h-[13px] flex-shrink-0"></i>
                    <span id="edit-date-range-hint-text">Select an expense type to see if dates are required.</span>
                </div>
                <label class="ss-label" for="edit-exp-from">
                    Date Range <span id="edit-from-required-star" class="required-star hidden">*</span>
                </label>
                <div id="edit-daterange-box" class="daterange-box">
                    <i data-lucide="calendar" class="field-icon"></i>
                    <input type="date" id="edit-exp-from" name="from_date" class="daterange-input">
                    <span class="daterange-sep">&#8594;</span>
                    <input type="date" id="edit-exp-to" name="to_date" class="daterange-input">
                </div>
                <p id="edit-date-error" class="field-hint hidden">
                    From Date and To Date are required for this expense type.
                </p>
            </div>

            <!-- Paid By -->
            <div class="field-group">
                <label class="ss-label">
                    Paid By
                    <span class="text-[11px] font-normal text-surface-400 ml-1">optional</span>
                </label>
                <?php if ($isAdmin): ?>
                    <div class="field-icon-wrap">
                        <i data-lucide="user" class="field-icon"></i>
                        <select id="edit-paid-by" name="paid_by" class="ss-input ss-input-icon">
                            <option value="">— Not paid yet —</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user->id ?>"><?= esc($user->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down" class="field-icon-trail"></i>
                    </div>
                <?php else: ?>
                    <div class="radio-pill-group">
                        <div class="radio-pill-row">
                            <label class="radio-pill">
                                <input type="radio" name="edit_paid_by_option" value="me" id="edit-pbo-me"
                                    onchange="toggleEditPaidBy(this.value)">
                                <span>Paid by me</span>
                            </label>
                            <label class="radio-pill">
                                <input type="radio" name="edit_paid_by_option" value="none" id="edit-pbo-none"
                                    onchange="toggleEditPaidBy(this.value)">
                                <span>Not paid yet</span>
                            </label>
                        </div>
                        <input type="hidden" name="paid_by" id="edit-paid-by-value" value="">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Involved Roommates -->
            <div class="field-group-lg">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="ss-label mb-0">Involved Roommates <span class="required-star">*</span></label>
                    <div class="flex gap-1.5">
                        <button type="button" onclick="editSelectAllUsers()" class="ss-btn ss-btn-ghost"
                            style="padding:4px 10px;font-size:12px;min-height:28px;">All</button>
                        <button type="button" onclick="editDeselectAllUsers()" class="ss-btn ss-btn-ghost"
                            style="padding:4px 10px;font-size:12px;min-height:28px;">None</button>
                    </div>
                </div>
                <div id="edit-involved-users-list" class="check-list">
                    <?php foreach ($users as $user): ?>
                        <label class="check-list-item">
                            <input type="checkbox" name="involved_users[]" value="<?= $user->id ?>"
                                class="edit-involved-cb">
                            <span><?= esc($user->name) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p id="edit-involved-error" class="field-hint hidden">
                    Please select at least one roommate.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex gap-2.5">
                <button type="button" onclick="closeEditModal()" class="ss-btn ss-btn-ghost flex-1">Cancel</button>
                <button type="submit" id="editExpenseBtn" class="ss-btn ss-btn-primary flex-[2]">
                    <i data-lucide="save" class="w-[15px] h-[15px]" id="editExpenseBtnIcon"></i>
                    <span id="editExpenseBtnText">Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    lucide.createIcons();

    // ── Disable scroll-to-change on number inputs (Amount fields) ─────
    // Browsers change a focused <input type="number">'s value when the
    // mouse wheel scrolls over it. Blurring on wheel stops that while
    // still letting the page scroll normally underneath the cursor.
    document.addEventListener('wheel', function(e) {
        var el = document.activeElement;
        if (el && el.tagName === 'INPUT' && el.type === 'number' && el === e.target) {
            el.blur();
        }
    }, {
        passive: true
    });

    // ── Role flag — bulk delete is admin-only ─────────────────────────
    var isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

    // ── Expense type → split_method map ─────────────────────────────
    var splitMethodMap = {
        <?php foreach ($expenseTypes as $type): ?> '<?= (int) $type->id ?>': '<?= esc($type->split_method, 'js') ?>',
        <?php endforeach; ?>
    };

    // Money formatting now lives globally as window.fmtMoney (app.js) —
    // aliased locally so every existing fmt(...) call below is unchanged.
    var fmt = window.fmtMoney;

    function fmtDate(d) {
        if (!d) return '—';
        var raw = (typeof d === 'object' && d.date) ? d.date : String(d);
        return raw.substring(0, 10) || '—';
    }

    // ── Bulk selection (admin only) ───────────────────────────────────
    // Selection persists across search/sort/page changes within this
    // page load (cleared on full page reload, like any client state).
    var _selectedIds = new Set();

    function toggleRowSelect(cb) {
        if (!isAdmin) return;
        var id = cb.dataset.id;
        if (cb.checked) {
            _selectedIds.add(id);
        } else {
            _selectedIds.delete(id);
        }
        updateBulkBar();
        syncSelectAllCheckbox();
    }

    function toggleSelectAll(cb) {
        if (!isAdmin) return;
        // Selects/deselects only the rows on the currently rendered page.
        // Use "Select all N expenses" to grab everything loaded.
        document.querySelectorAll('.row-checkbox').forEach(function(rowCb) {
            rowCb.checked = cb.checked;
            var id = rowCb.dataset.id;
            if (cb.checked) {
                _selectedIds.add(id);
            } else {
                _selectedIds.delete(id);
            }
        });
        updateBulkBar();
    }

    function syncSelectAllCheckbox() {
        var boxes = document.querySelectorAll('.row-checkbox');
        var selectAll = document.getElementById('select-all-checkbox');
        if (!selectAll) return;
        if (!boxes.length) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
            return;
        }
        var checkedCount = 0;
        boxes.forEach(function(b) {
            if (b.checked) checkedCount++;
        });
        selectAll.checked = checkedCount === boxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
    }

    // "Select all N expenses" — expands selection beyond the current
    // page to every expense currently loaded in the table (_lastData).
    // Note: _lastData is the full unfiltered set from the last load, so
    // this selects everything loaded, not just rows matching an active
    // search term.
    function selectAllMatching() {
        if (!isAdmin) return;
        _lastData.forEach(function(e) {
            _selectedIds.add(String(e.id));
        });
        document.querySelectorAll('.row-checkbox').forEach(function(cb) {
            cb.checked = _selectedIds.has(cb.dataset.id);
        });
        syncSelectAllCheckbox();
        updateBulkBar();
    }

    function updateBulkBar() {
        if (!isAdmin) return;
        var bar = document.getElementById('bulk-actions-bar');
        var countEl = document.getElementById('bulk-selected-count');
        if (!bar || !countEl) return;
        countEl.textContent = _selectedIds.size;
        bar.style.display = _selectedIds.size > 0 ? 'flex' : 'none';

        var linkWrap = document.getElementById('select-all-matching-link');
        var linkCount = document.getElementById('select-all-matching-count');
        if (linkWrap && linkCount) {
            if (_lastData.length > 0 && _selectedIds.size > 0 && _selectedIds.size < _lastData.length) {
                linkCount.textContent = _lastData.length;
                linkWrap.style.display = 'inline-block';
            } else {
                linkWrap.style.display = 'none';
            }
        }
    }

    function clearSelection() {
        _selectedIds.clear();
        document.querySelectorAll('.row-checkbox').forEach(function(cb) {
            cb.checked = false;
        });
        var selectAll = document.getElementById('select-all-checkbox');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        updateBulkBar();
    }

    function bulkDeleteExpenses() {
        if (!isAdmin) return;
        var ids = Array.from(_selectedIds);
        if (!ids.length) return;

        ssConfirm({
            title: 'Delete Expenses',
            message: 'Delete ' + ids.length + ' selected expense' + (ids.length > 1 ? 's' : '') + '? This cannot be undone.',
            confirmText: 'Delete',
            onConfirm: function() {
                $.ajax({
                    url: '/expense/bulkDeleteExpenses',
                    type: 'POST',
                    data: {
                        'ids[]': ids
                    },
                    success: function(res) {
                        var count = (res && res.deleted) ? res.deleted : ids.length;
                        ssToast(count + ' expense' + (count > 1 ? 's' : '') + ' deleted.', 'success');
                        _selectedIds.clear();
                        updateBulkBar();
                        _expenseTable.reload();
                    },
                    error: function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.error) ?
                            xhr.responseJSON.error :
                            'Failed to delete selected expenses.';
                        ssToast(msg, 'error');
                    }
                });
            }
        });
    }

    // ── View toggle ──────────────────────────────────────────────────
    var _currentView = 'list';
    var _lastData = [];

    function setView(mode) {
        _currentView = mode;
        var listWrap = document.getElementById('expenses-list-wrap');
        var gridWrap = document.getElementById('expenses-grid-wrap');
        var btnList = document.getElementById('btn-view-list');
        var btnGrid = document.getElementById('btn-view-grid');

        if (mode === 'grid') {
            listWrap.style.display = 'none';
            gridWrap.style.display = 'block';
            btnGrid.style.background = '#fff';
            btnGrid.style.color = '#5c6af0';
            btnList.style.background = 'transparent';
            btnList.style.color = '#94a3b8';
            renderGrid(_lastData);
        } else {
            listWrap.style.display = 'block';
            gridWrap.style.display = 'none';
            btnList.style.background = '#fff';
            btnList.style.color = '#5c6af0';
            btnGrid.style.background = 'transparent';
            btnGrid.style.color = '#94a3b8';
        }
    }

    // ── Grid card actions ──────────────────────────────────────────
    // Cards use direct Edit/Delete icon buttons (see renderGrid) instead
    // of a dropdown menu, so mobile users can act in a single tap.
    function deleteFromCard(id) {
        ssConfirm({
            title: 'Delete Expense',
            message: 'Delete this expense? This cannot be undone.',
            confirmText: 'Delete',
            onConfirm: function() {
                $.ajax({
                    url: '/expense/deleteExpense/' + id,
                    type: 'DELETE',
                    success: function() {
                        ssToast('Expense deleted.', 'success');
                        _selectedIds.delete(String(id));
                        updateBulkBar();
                        _expenseTable.reload();
                    },
                    error: function() {
                        ssToast('Failed to delete expense.', 'error');
                    }
                });
            }
        });
    }

    // ══════════════════════════════════════════════════════════════
    // renderGrid() — every inline style="..." string is now a class
    // from batch 1/5a (.expense-card, .card-icon-btn-*, .detail-row,
    // .ss-badge variants). Hover states (border, button bg) are CSS
    // :hover rules now, so the onmouseover/onmouseout pairs are gone.
    // ══════════════════════════════════════════════════════════════
    function renderGrid(data) {
        var grid = document.getElementById('expenses-grid');
        var emptyEl = document.getElementById('expenses-grid-empty');

        if (!data || data.length === 0) {
            grid.innerHTML = '';
            emptyEl.classList.remove('hidden');
            return;
        }
        emptyEl.classList.add('hidden');

        // Small helper: one label/value row inside the card details block
        function detailRow(label, valueHtml) {
            return '<div class="detail-row">' +
                '<span class="detail-label">' + label + '</span>' +
                '<span class="detail-value">' + valueHtml + '</span>' +
                '</div>';
        }

        grid.innerHTML = data.map(function(e) {
            var billingMonth = window.escHtml(e.billing_month || '—');
            var safeType = window.escHtml(e.expense_type || '—');

            var fromDate = fmtDate(e.from_date);
            var toDate = fmtDate(e.to_date);
            var period = (!e.from_date && !e.to_date) ?
                '<span class="dt-empty">Not set</span>' :
                (fromDate === toDate ? fromDate : fromDate + ' \u2192 ' + toDate);

            var paidByHtml = e.paid_by_name ?
                '<span class="inline-flex items-center gap-1">' +
                '<i data-lucide="user" class="w-[11px] h-[11px] text-surface-400"></i>' +
                window.escHtml(e.paid_by_name) + '</span>' :
                '<span class="ss-badge ss-badge-amber gap-1">' +
                '<i data-lucide="clock" class="w-[10px] h-[10px]"></i>Pending</span>';

            var involvedNames = window.escHtml((e.involved_names || '').trim());
            var involvedNamesHtml = involvedNames ?
                '<div class="mt-0.5 text-xs leading-relaxed text-surface-500">' + involvedNames + '</div>' :
                '';

            var descriptionHtml = e.description ?
                '<div class="expense-card-desc">' + window.escHtml(e.description) + '</div>' :
                '<div class="expense-card-desc-empty">No description</div>';

            return '<div class="expense-card">'

                +
                '<div class="expense-card-top">' +
                '<span class="ss-badge ss-badge-pink gap-[5px] mt-0.5">' +
                '<i data-lucide="tag" class="w-[11px] h-[11px]"></i>' +
                safeType +
                '</span>'
            '<div class="expense-card-actions">' +
            '<button onclick="openEditModal(\'' + e.id + '\')" title="Edit" class="card-icon-btn card-icon-btn-edit">' +
                '<i data-lucide="pencil" class="w-[15px] h-[15px]"></i>' +
                '</button>' +
                '<button onclick="deleteFromCard(\'' + e.id + '\')" title="Delete" class="card-icon-btn card-icon-btn-delete">' +
                '<i data-lucide="trash-2" class="w-[15px] h-[15px]"></i>' +
                '</button>' +
                '</div>' +
                '</div>'

                // Amount
                +
                '<div class="expense-card-amount">' +
                fmt(e.amount) +
                '</div>'

                // Description
                +
                descriptionHtml

                // Detail rows — every remaining column, clearly labelled
                +
                '<div class="expense-card-details">'

                +
                detailRow('Billing Month', '<span class="ss-badge ss-badge-indigo gap-[5px] font-mono">' +
                    '<i data-lucide="calendar-range" class="w-[10px] h-[10px]"></i>' +
                    billingMonth + '</span>')

                +
                detailRow('Period', period)

                +
                detailRow('Paid By', paidByHtml)

                +
                detailRow('Involved', '<span class="ss-badge ss-badge-blue gap-1">' +
                    '<i data-lucide="users" class="w-[10px] h-[10px]"></i>' +
                    (e.total_involved || 0) + '</span>') +
                involvedNamesHtml

                +
                '</div>'

                +
                '</div>';
        }).join('');

        lucide.createIcons({
            nodes: [grid]
        });
    }

    // ══════════════════════════════════════════════════════════════
    // 2) updateDateRangeUI — swap CSS classes instead of writing
    //    background/border/color inline for every state change.
    // ══════════════════════════════════════════════════════════════
    function updateDateRangeUI(prefix, splitMethod) {
        var isDaysPresent = splitMethod === 'daysPresent';
        var hintEl = document.getElementById(prefix + '-date-range-hint');
        var hintText = document.getElementById(prefix + '-date-range-hint-text');
        var starFrom = document.getElementById(prefix + '-from-required-star');
        var starTo = document.getElementById(prefix + '-to-required-star');
        var errEl = document.getElementById(prefix + '-date-error');

        hintEl.classList.remove('hint-warn', 'hint-required', 'hint-optional');

        if (isDaysPresent) {
            hintEl.classList.add('hint-required');
            hintText.textContent = 'Required for this type — absent days are calculated from the date range.';
            if (starFrom) starFrom.classList.remove('hidden');
            if (starTo) starTo.classList.remove('hidden');
        } else if (splitMethod === '') {
            hintEl.classList.add('hint-warn');
            hintText.textContent = 'Select an expense type to see if dates are required.';
            if (starFrom) starFrom.classList.add('hidden');
            if (starTo) starTo.classList.add('hidden');
        } else {
            hintEl.classList.add('hint-optional');
            hintText.textContent = 'Optional for this expense type.';
            if (starFrom) starFrom.classList.add('hidden');
            if (starTo) starTo.classList.add('hidden');
        }
        if (errEl) errEl.classList.add('hidden');
    }

    function onAddTypeChange(typeId) {
        var method = splitMethodMap[String(typeId)] || '';
        updateDateRangeUI('add', typeId ? method : '');
    }

    function onEditTypeChange(typeId) {
        var method = splitMethodMap[String(typeId)] || '';
        updateDateRangeUI('edit', typeId ? method : '');
    }

    // ══════════════════════════════════════════════════════════════
    // 3) validateDates — error border now toggles `.has-error` on the
    //    daterange-box instead of writing borderColor directly, and no
    //    longer calls the deleted setDateRangeBoxFocus().
    // ══════════════════════════════════════════════════════════════
    function validateDates(prefix) {
        var typeId = document.getElementById(
            prefix === 'add' ? 'exp-type' : 'edit-exp-type'
        ).value;
        var method = splitMethodMap[String(typeId)] || '';
        if (method !== 'daysPresent') return true;
        var fromId = prefix === 'add' ? 'exp-from' : 'edit-exp-from';
        var toId = prefix === 'add' ? 'exp-to' : 'edit-exp-to';
        var fromVal = document.getElementById(fromId).value;
        var toVal = document.getElementById(toId).value;
        if (!fromVal || !toVal) {
            document.getElementById(prefix + '-date-error').classList.remove('hidden');
            var box = document.getElementById(prefix + '-daterange-box');
            if (box) {
                box.classList.add('has-error');
                setTimeout(function() {
                    box.classList.remove('has-error');
                }, 2000);
            }
            return false;
        }
        document.getElementById(prefix + '-date-error').classList.add('hidden');
        return true;
    }

    // ── SS.Table init ────────────────────────────────────────────────
    var _expenseTable = SS.Table({
        tbodyId: 'expenses-tbody',
        url: '/expense/getExpenses',
        countId: 'expense-count',
        searchPlaceholder: 'Search by type, description, paid by\u2026',
        pageSize: 15,
        colSpan: <?= $isAdmin ? 9 : 8 ?>,

        cols: [{
                label: 'Type',
                key: 'expense_type'
            },
            {
                label: 'Description',
                key: 'description'
            },
            {
                label: 'Amount',
                key: 'amount'
            },
            {
                label: 'Billing Month',
                key: 'billing_month'
            },
            {
                label: 'Period',
                key: 'from_date'
            },
            {
                label: 'Paid By',
                key: 'paid_by_name'
            },
            {
                label: 'Involved',
                key: 'total_involved',
                align: 'center'
            },
            {
                label: 'Actions',
                key: null,
                sortable: false,
                align: 'right'
            },
        ],

        onLoad: function(data) {
            _lastData = data;
            var total = data.reduce(function(s, e) {
                return s + parseFloat(e.amount || 0);
            }, 0);
            document.getElementById('expense-total').textContent = fmt(total);
            // Keep grid in sync whenever data reloads
            if (_currentView === 'grid') renderGrid(data);
        },

        // ══════════════════════════════════════════════════════════════
        // rowFn() — table-row renderer for the list view (SS.Table).
        // Same treatment: .dt-cell for every <td>, .ss-badge variants for
        // pills, .action-menu-* (3-dot menu, shared with the Users view)
        // for Edit/Delete. Row hover comes from
        // `.ss-table-wrap tbody tr:hover` in main.php (batch 5a), so the
        // onmouseover/onmouseout pair on <tr> is gone.
        // ══════════════════════════════════════════════════════════════
        rowFn: function(e) {
            var fromDate = fmtDate(e.from_date);
            var toDate = fmtDate(e.to_date);
            var period = (!e.from_date && !e.to_date) ?
                '<span class="dt-empty">—</span>' :
                (fromDate === toDate ? fromDate : fromDate + ' \u2192 ' + toDate);

            var billingMonth = window.escHtml(e.billing_month || '—');
            var safeType = window.escHtml(e.expense_type || '—');
            var safeDescription = e.description ?
                window.escHtml(e.description) :
                '<span class="dt-empty">No description</span>';
            var safePaidBy = window.escHtml(e.paid_by_name || '');
            var safeInvolvedNames = window.escHtml(e.involved_names || '');

            return '<tr>'

                +
                (isAdmin ?
                    '<td class="dt-cell">' +
                    '<input type="checkbox" class="row-checkbox w-4 h-4 cursor-pointer" data-id="' + e.id + '">' +
                    '</td>' :
                    '')

                +
                '<td class="dt-cell dt-cell-nowrap">' +
                '<span class="ss-badge ss-badge-pink gap-[5px]">' +
                '<i data-lucide="tag" class="w-[11px] h-[11px]"></i>' +
                safeType +
                '</span></td>'

                +
                '<td class="dt-cell" style="max-width:200px;">' +
                '<span class="dt-cell-truncate text-[13px] text-surface-500">' +
                safeDescription +
                '</span></td>'

                +
                '<td class="dt-cell dt-cell-nowrap">' +
                '<span class="text-sm font-bold text-surface-900 font-mono">' +
                fmt(e.amount) + '</span></td>'

                +
                '<td class="dt-cell dt-cell-nowrap">' +
                '<span class="ss-badge ss-badge-indigo gap-[5px] font-mono">' +
                '<i data-lucide="calendar-range" class="w-[11px] h-[11px]"></i>' +
                billingMonth + '</span></td>'

                +
                '<td class="dt-cell dt-cell-nowrap">' +
                '<span class="text-[13px] text-surface-500">' + period + '</span></td>'

                +
                '<td class="dt-cell dt-cell-nowrap">' +
                (e.paid_by_name ?
                    '<span class="inline-flex items-center gap-[5px] text-[13px] text-surface-700">' +
                    '<i data-lucide="user" class="w-3 h-3 text-surface-400"></i>' +
                    safePaidBy + '</span>' :
                    '<span class="ss-badge ss-badge-amber gap-[5px]">' +
                    '<i data-lucide="clock" class="w-[11px] h-[11px]"></i>Pending</span>') +
                '</td>'

                +
                '<td class="dt-cell dt-cell-nowrap dt-cell-center">' +
                '<span class="ss-involved-badge ss-badge ss-badge-blue gap-[5px]" data-names="' + safeInvolvedNames + '" style="cursor:default;">' +
                '<i data-lucide="users" class="w-[11px] h-[11px]"></i>' +
                (e.total_involved || 0) + '</span></td>'

                +
                '<td class="dt-cell dt-cell-right dt-cell-nowrap">' +
                '<div class="action-menu-wrap">' +
                '<button type="button" class="action-menu-trigger" aria-label="Actions">' +
                '<i data-lucide="more-vertical" class="w-4 h-4"></i>' +
                '</button>' +
                '<div class="action-menu-dropdown">' +
                '<button class="action-menu-item editExpenseBtn" data-id="' + e.id + '">' +
                '<i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit</button>' +
                '<div class="action-menu-divider"></div>' +
                '<button class="action-menu-item action-menu-danger deleteExpenseBtn" data-id="' + e.id + '">' +
                '<i data-lucide="trash-2" class="w-3.5 h-3.5"></i>Delete</button>' +
                '</div></div></td>'

                +
                '</tr>';
        },

        onRender: function(data) {
            // Wire the 3-dot action menu for this render's rows.
            // initActionMenus() lives in app.js (loaded globally from
            // layout/main) — no per-view definition needed.
            initActionMenus(document.getElementById('expenses-tbody'));

            // Wire list-view action buttons
            document.querySelectorAll('.deleteExpenseBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = this.dataset.id;
                    ssConfirm({
                        title: 'Delete Expense',
                        message: 'Delete this expense? This cannot be undone.',
                        confirmText: 'Delete',
                        onConfirm: function() {
                            $.ajax({
                                url: '/expense/deleteExpense/' + id,
                                type: 'DELETE',
                                success: function() {
                                    ssToast('Expense deleted.', 'success');
                                    _selectedIds.delete(id);
                                    updateBulkBar();
                                    _expenseTable.reload();
                                },
                                error: function() {
                                    ssToast('Failed to delete expense.', 'error');
                                }
                            });
                        }
                    });
                });
            });

            document.querySelectorAll('.editExpenseBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openEditModal(this.dataset.id);
                });
            });

            // Wire row checkboxes — restore checked state from _selectedIds
            // (selection persists across search/sort/page changes)
            if (isAdmin) {
                document.querySelectorAll('.row-checkbox').forEach(function(cb) {
                    cb.checked = _selectedIds.has(cb.dataset.id);
                    cb.addEventListener('change', function() {
                        toggleRowSelect(this);
                    });
                });
                syncSelectAllCheckbox();
                updateBulkBar();
            }

            // Keep grid in sync if currently in grid view
            if (_currentView === 'grid') renderGrid(data);
        },
    });


    // ── Add modal ────────────────────────────────────────────────────
    function selectAllUsers() {
        document.querySelectorAll('#involved-users-list input[type="checkbox"]')
            .forEach(function(cb) {
                cb.checked = true;
            });
    }

    function deselectAllUsers() {
        document.querySelectorAll('#involved-users-list input[type="checkbox"]')
            .forEach(function(cb) {
                cb.checked = false;
            });
    }

    var currentUserId = '<?= (int) $currentUser['id'] ?>';

    function togglePaidBy(val) {
        var input = document.getElementById('paid-by-value');
        if (input) input.value = val === 'me' ? currentUserId : '';
    }

    function openAddModal() {
        window.ssModalOpen({
            modalId: 'add-expense-modal',
            backdropId: 'modal-backdrop',
            display: 'flex',
            focusId: 'exp-type'
        });
        updateDateRangeUI('add', '');
    }

    // ══════════════════════════════════════════════════════════════
    // 5) closeAddModal — error paragraphs toggle via classList('hidden')
    //    to match the markup's Tailwind `hidden` class (batch 3).
    // ══════════════════════════════════════════════════════════════
    function closeAddModal() {
        window.ssModalClose({
            modalId: 'add-expense-modal',
            backdropId: 'modal-backdrop',
            onClosed: function() {
                document.getElementById('addExpenseForm').reset();
                deselectAllUsers();
                document.getElementById('involved-error').classList.add('hidden');
                document.getElementById('add-date-error').classList.add('hidden');
                updateDateRangeUI('add', '');
                var pboNone = document.getElementById('pbo-none');
                var pbInput = document.getElementById('paid-by-value');
                if (pboNone) pboNone.checked = true;
                if (pbInput) pbInput.value = '';
                resetAddBtn();
            }
        });
    }

    function setAddBtnLoading() {
        var btn = document.getElementById('addExpenseBtn');
        var text = document.getElementById('addExpenseBtnText');
        btn.disabled = true;
        btn.style.opacity = '0.75';
        text.textContent = 'Saving\u2026';
        window.setLucideIcon('addExpenseBtnIcon', 'loader');
    }

    function resetAddBtn() {
        var btn = document.getElementById('addExpenseBtn');
        var text = document.getElementById('addExpenseBtnText');
        if (!btn) return;
        btn.disabled = false;
        btn.style.opacity = '1';
        text.textContent = 'Save Expense';
        window.setLucideIcon('addExpenseBtnIcon', 'plus');
    }

    // ══════════════════════════════════════════════════════════════
    // 4) Add form submit handler — involved-users error now toggles
    //    `.has-error` on the check-list instead of borderColor.
    // ══════════════════════════════════════════════════════════════
    document.getElementById('addExpenseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var checked = document.querySelectorAll('#involved-users-list input[type="checkbox"]:checked');
        if (checked.length === 0) {
            document.getElementById('involved-error').classList.remove('hidden');
            var list = document.getElementById('involved-users-list');
            list.classList.add('has-error');
            setTimeout(function() {
                list.classList.remove('has-error');
            }, 2000);
            return;
        }
        document.getElementById('involved-error').classList.add('hidden');
        if (!validateDates('add')) return;
        setAddBtnLoading();
        $.post('/expense/addExpense', $(this).serialize(), function(res) {
            if (res.status === 'success') {
                ssToast('Expense added successfully!', 'success');
                closeAddModal();
                _expenseTable.reload();
            } else {
                ssToast('Failed to save expense.', 'error');
                resetAddBtn();
            }
        }, 'json').fail(function() {
            ssToast('Something went wrong.', 'error');
            resetAddBtn();
        });
    });


    // ── Edit modal ───────────────────────────────────────────────────
    var _editingId = null;

    // NEW
    function openEditModal(id) {
        _editingId = id;
        window.ssModalOpen({
            modalId: 'edit-expense-modal',
            backdropId: 'edit-modal-backdrop',
            display: 'flex'
        });

        $.get('/expense/getExpense/' + id, function(res) {
            var d = res.data;
            var canEdit = d.can_edit || false;
            var permDiv = document.getElementById('edit-permission-denied');
            var formDiv = document.getElementById('editExpenseForm');

            if (!canEdit) {
                if (permDiv) permDiv.style.display = 'block';
                if (formDiv) formDiv.style.display = 'none';
                lucide.createIcons();
                return;
            }
            if (permDiv) permDiv.style.display = 'none';
            if (formDiv) formDiv.style.display = 'block';

            document.getElementById('edit-expense-id').value = d.id;
            document.getElementById('edit-exp-description').value = d.description || '';
            document.getElementById('edit-exp-type').value = d.expense_type_id;
            document.getElementById('edit-exp-amount').value = d.amount;
            document.getElementById('edit-exp-billing-month').value = d.billing_month || '';
            document.getElementById('edit-exp-from').value = (d.from_date || '').substring(0, 10);
            document.getElementById('edit-exp-to').value = (d.to_date || '').substring(0, 10);

            var method = splitMethodMap[String(d.expense_type_id)] || '';
            updateDateRangeUI('edit', d.expense_type_id ? method : '');

            var isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
            if (isAdmin) {
                var sel = document.getElementById('edit-paid-by');
                if (sel) sel.value = d.paid_by || '';
            } else {
                var meRadio = document.getElementById('edit-pbo-me');
                var noneRadio = document.getElementById('edit-pbo-none');
                var hiddenPb = document.getElementById('edit-paid-by-value');
                var myId = '<?= (int) $currentUser['id'] ?>';
                if (d.paid_by && String(d.paid_by) === myId) {
                    if (meRadio) meRadio.checked = true;
                    if (hiddenPb) hiddenPb.value = myId;
                } else {
                    if (noneRadio) noneRadio.checked = true;
                    if (hiddenPb) hiddenPb.value = '';
                }
            }

            var involvedIds = d.involved_ids || [];
            document.querySelectorAll('.edit-involved-cb').forEach(function(cb) {
                cb.checked = involvedIds.indexOf(parseInt(cb.value, 10)) !== -1;
            });

            lucide.createIcons();
            document.getElementById('edit-exp-type').focus();
        }).fail(function() {
            ssToast('Failed to load expense data.', 'error');
            closeEditModal();
        });
    }

    function closeEditModal() {
        window.ssModalClose({
            modalId: 'edit-expense-modal',
            backdropId: 'edit-modal-backdrop',
            onClosed: function() {
                document.getElementById('editExpenseForm').reset();
                document.getElementById('edit-involved-error').classList.add('hidden');
                document.getElementById('edit-date-error').classList.add('hidden');
                updateDateRangeUI('edit', '');
                resetEditBtn();
                _editingId = null;
            }
        });
    }

    function editSelectAllUsers() {
        document.querySelectorAll('.edit-involved-cb').forEach(function(cb) {
            cb.checked = true;
        });
    }

    function editDeselectAllUsers() {
        document.querySelectorAll('.edit-involved-cb').forEach(function(cb) {
            cb.checked = false;
        });
    }

    function toggleEditPaidBy(val) {
        var input = document.getElementById('edit-paid-by-value');
        if (input) input.value = val === 'me' ? '<?= (int) $currentUser['id'] ?>' : '';
    }

    function setEditBtnLoading() {
        var btn = document.getElementById('editExpenseBtn');
        var text = document.getElementById('editExpenseBtnText');
        btn.disabled = true;
        btn.style.opacity = '0.75';
        text.textContent = 'Saving\u2026';
        window.setLucideIcon('editExpenseBtnIcon', 'loader');
    }

    function resetEditBtn() {
        var btn = document.getElementById('editExpenseBtn');
        var text = document.getElementById('editExpenseBtnText');
        if (!btn) return;
        btn.disabled = false;
        btn.style.opacity = '1';
        text.textContent = 'Save Changes';
        window.setLucideIcon('editExpenseBtnIcon', 'save');
    }

    // ══════════════════════════════════════════════════════════════
    // 6) Edit form submit handler + closeEditModal — same treatment,
    //    mirrored for the edit-* ids.
    // ══════════════════════════════════════════════════════════════
    document.getElementById('editExpenseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var checked = document.querySelectorAll('.edit-involved-cb:checked');
        if (checked.length === 0) {
            document.getElementById('edit-involved-error').classList.remove('hidden');
            var list = document.getElementById('edit-involved-users-list');
            list.classList.add('has-error');
            setTimeout(function() {
                list.classList.remove('has-error');
            }, 2000);
            return;
        }
        document.getElementById('edit-involved-error').classList.add('hidden');
        if (!validateDates('edit')) return;
        setEditBtnLoading();
        $.post('/expense/updateExpense/' + _editingId, $(this).serialize(), function(res) {
            if (res.status === 'success') {
                ssToast('Expense updated successfully!', 'success');
                closeEditModal();
                _expenseTable.reload();
            } else {
                ssToast('Failed to update expense.', 'error');
                resetEditBtn();
            }
        }, 'json').fail(function() {
            ssToast('Something went wrong.', 'error');
            resetEditBtn();
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        closeAddModal();
        closeEditModal();
    });


    // ── Involved-users tooltip ───────────────────────────────────────
    (function() {
        var tip = document.createElement('div');
        tip.style.cssText = [
            'position:fixed', 'z-index:9999', 'background:#1e293b', 'color:#f8fafc',
            'font-size:12px', 'font-family:"DM Sans",sans-serif', 'font-weight:500',
            'line-height:1.5', 'padding:8px 12px', 'border-radius:8px',
            'pointer-events:none', 'max-width:220px', 'word-break:break-word',
            'display:none', 'opacity:0', 'transition:opacity .12s',
        ].join(';');
        document.body.appendChild(tip);

        var showTimer;
        document.addEventListener('mouseover', function(ev) {
            var badge = ev.target.closest('.ss-involved-badge');
            if (!badge) return;
            var names = (badge.getAttribute('data-names') || '').trim();
            if (!names) return;
            var list = names.split(',').map(function(n) {
                return '\u2022 ' + window.escHtml(n.trim());
            }).join('<br>');
            tip.innerHTML = '<div style="margin-bottom:4px;font-weight:700;font-size:11px;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;">Involved</div>' + list;
            clearTimeout(showTimer);
            showTimer = setTimeout(function() {
                var rect = badge.getBoundingClientRect();
                var tipW = tip.offsetWidth || 180;
                var tipH = tip.offsetHeight || 60;
                var left = rect.left + rect.width / 2 - tipW / 2;
                var top = rect.top - tipH - 8;
                if (left < 8) left = 8;
                if (left + tipW > window.innerWidth - 8) left = window.innerWidth - tipW - 8;
                if (top < 8) top = rect.bottom + 8;
                tip.style.left = left + 'px';
                tip.style.top = top + 'px';
                tip.style.display = 'block';
                requestAnimationFrame(function() {
                    tip.style.opacity = '1';
                });
            }, 80);
        });
        document.addEventListener('mouseout', function(ev) {
            if (!ev.target.closest('.ss-involved-badge')) return;
            clearTimeout(showTimer);
            tip.style.opacity = '0';
            setTimeout(function() {
                tip.style.display = 'none';
            }, 120);
        });
    })();
</script>
<?= $this->endSection() ?>
<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Expenses<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Page header ─────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">Expenses</h1>
        <p class="page-subtitle">Track and manage all shared expenses</p>
    </div>
    <button onclick="openAddModal()" class="ss-btn ss-btn-primary">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        Add Expense
    </button>
</div>

<!-- ── Expenses table card ─────────────────────────────────────── -->
<div class="ss-card">
    <div class="ss-card-header"
        style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">All Expenses</h2>
            <p style="font-size:13px;color:#94a3b8;margin:3px 0 0;">
                <span id="expense-count">—</span> expenses recorded
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:13px;color:#64748b;">Total:</span>
                <span id="expense-total"
                    style="font-size:15px;font-weight:700;color:#0f172a;font-family:'JetBrains Mono',monospace;">—</span>
            </div>
            <!-- View toggle -->
            <div style="display:flex;align-items:center;gap:2px;background:#f1f5f9;border-radius:8px;padding:3px;">
                <button id="btn-view-list" onclick="setView('list')" title="List view" style="
                    width:32px;height:32px;border-radius:6px;border:none;cursor:pointer;
                    display:flex;align-items:center;justify-content:center;
                    background:#fff;color:#5c6af0;transition:all .15s;">
                    <i data-lucide="list" style="width:15px;height:15px;"></i>
                </button>
                <button id="btn-view-grid" onclick="setView('grid')" title="Grid view" style="
                    width:32px;height:32px;border-radius:6px;border:none;cursor:pointer;
                    display:flex;align-items:center;justify-content:center;
                    background:transparent;color:#94a3b8;transition:all .15s;">
                    <i data-lucide="layout-grid" style="width:15px;height:15px;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ── Bulk actions bar (admin only) ── -->
    <?php if ($currentUser['role'] === 'admin'): ?>
    <div id="bulk-actions-bar" style="
        display:none;align-items:center;justify-content:space-between;gap:12px;
        padding:10px 20px;background:#eef2ff;border-bottom:1px solid #e0e7ff;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <i data-lucide="check-square" style="width:15px;height:15px;color:#4338ca;"></i>
            <span style="font-size:13px;font-weight:600;color:#4338ca;">
                <span id="bulk-selected-count">0</span> selected
            </span>
            <button onclick="clearSelection()" style="
                background:none;border:none;color:#5c6af0;font-size:12px;font-weight:600;
                cursor:pointer;text-decoration:underline;padding:0;font-family:'DM Sans',sans-serif;">
                Clear
            </button>
            <span id="select-all-matching-link" style="display:none;">
                <button onclick="selectAllMatching()" style="
                    background:none;border:none;color:#5c6af0;font-size:12px;font-weight:600;
                    cursor:pointer;text-decoration:underline;padding:0;font-family:'DM Sans',sans-serif;">
                    Select all <span id="select-all-matching-count">0</span> expenses
                </button>
            </span>
        </div>
        <button onclick="bulkDeleteExpenses()" style="
            display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:7px;
            background:#fee2e2;color:#dc2626;border:none;cursor:pointer;
            font-size:12px;font-weight:600;font-family:'DM Sans',sans-serif;transition:background .15s;"
            onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
            <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
            Delete Selected
        </button>
    </div>
    <?php endif; ?>

    <!-- ── List view ── -->
    <div id="expenses-list-wrap" class="ss-table-wrap" style="border:none;border-radius:0;">
        <table style="width:100%;border-collapse:collapse;min-width:680px;">
            <thead>
                <tr>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                    <th style="width:36px;padding:13px 16px;border-bottom:1px solid #f1f5f9;">
                        <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll(this)"
                            style="width:16px;height:16px;cursor:pointer;">
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
    <div id="expenses-grid-wrap" style="display:none;padding:16px;">
        <div id="expenses-grid" style="
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
            gap:12px;">
        </div>
        <div id="expenses-grid-empty" style="display:none;padding:40px 16px;text-align:center;">
            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                <div
                    style="width:48px;height:48px;border-radius:12px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="receipt" style="width:22px;height:22px;color:#e2e8f0;"></i>
                </div>
                <span style="font-size:14px;color:#94a3b8;font-weight:500;">No expenses found</span>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
     ADD EXPENSE MODAL
════════════════════════════════════════════════════════════════ -->
<div id="modal-backdrop" onclick="closeAddModal()" style="
    display:none;position:fixed;inset:0;
    background:rgba(15,23,42,.45);z-index:100;
    backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);
"></div>

<div id="add-expense-modal" style="
    display:none;position:fixed;
    top:50%;left:50%;
    transform:translate(-50%,-50%) scale(0.97);
    width:calc(100% - 32px);max-width:500px;
    max-height:90vh;
    background:#fff;border-radius:16px;
    box-shadow:0 20px 60px rgba(0,0,0,.15);
    z-index:101;opacity:0;
    transition:transform .2s ease, opacity .2s ease;
    flex-direction:column;
">
    <div
        style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid #f1f5f9;flex-shrink:0;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div
                style="width:34px;height:34px;border-radius:8px;background:#fce7f3;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="receipt" style="width:16px;height:16px;color:#be185d;"></i>
            </div>
            <div>
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Add New Expense</h3>
                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0;">Fill in the expense details below</p>
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

    <div style="overflow-y:auto;-webkit-overflow-scrolling:touch;flex:1;">
        <form id="addExpenseForm" style="padding:20px 24px 24px;">

            <!-- Expense Type -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="exp-type">Expense Type <span style="color:#ef4444;">*</span></label>
                <div style="position:relative;">
                    <i data-lucide="tag"
                        style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                    <select id="exp-type" name="expense_type_id" required class="ss-input"
                        style="padding-left:38px;cursor:pointer;appearance:none;-webkit-appearance:none;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                        onchange="onAddTypeChange(this.value)">
                        <option value="">— Select type —</option>
                        <?php foreach ($expenseTypes as $type): ?>
                            <option value="<?= $type->id ?>"><?= esc($type->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="chevron-down"
                        style="position:absolute;right:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                </div>
            </div>

            <!-- Description -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="exp-description">Description</label>
                <div style="position:relative;">
                    <i data-lucide="edit-3"
                        style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                    <input type="text" id="exp-description" name="description" placeholder="e.g. Rent of Jan 2026" class="ss-input"
                        style="padding-left:38px;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                </div>
            </div>

            <!-- Amount -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="exp-amount">Amount <span style="color:#ef4444;">*</span></label>
                <div style="position:relative;">
                    <span
                        style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:15px;font-weight:600;color:#94a3b8;pointer-events:none;font-family:'JetBrains Mono',monospace;">₹</span>
                    <input type="number" id="exp-amount" name="amount" placeholder="0.00" min="0" step="0.01" required
                        class="ss-input" style="padding-left:30px;font-family:'JetBrains Mono',monospace;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                </div>
            </div>

            <!-- Billing Month -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="exp-billing-month">
                    Billing Month <span style="color:#ef4444;">*</span>
                </label>
                <div style="position:relative;">
                    <i data-lucide="calendar-range"
                        style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                    <input type="month" id="exp-billing-month" name="billing_month" value="<?= date('Y-m') ?>" required
                        class="ss-input" style="padding-left:38px;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                </div>
            </div>

            <!-- Date range -->
            <div id="add-date-range-wrap" style="margin-bottom:16px;">
                <div id="add-date-range-hint" style="
                    display:flex;align-items:center;gap:6px;
                    padding:8px 12px;margin-bottom:10px;
                    background:#fef9c3;border:1px solid #fde68a;border-radius:8px;
                    font-size:12px;color:#92400e;">
                    <i data-lucide="info" style="width:13px;height:13px;flex-shrink:0;"></i>
                    <span id="add-date-range-hint-text">Select an expense type to see if dates are required.</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="ss-label" for="exp-from">
                            From Date <span id="add-from-required-star" style="color:#ef4444;display:none;">*</span>
                        </label>
                        <div style="position:relative;">
                            <i data-lucide="calendar"
                                style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                            <input type="date" id="exp-from" name="from_date" value="<?= date('Y-m-d') ?>"
                                class="ss-input" style="padding-left:38px;"
                                onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                                onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                        </div>
                    </div>
                    <div>
                        <label class="ss-label" for="exp-to">
                            To Date <span id="add-to-required-star" style="color:#ef4444;display:none;">*</span>
                        </label>
                        <div style="position:relative;">
                            <i data-lucide="calendar"
                                style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                            <input type="date" id="exp-to" name="to_date" value="<?= date('Y-m-d') ?>" class="ss-input"
                                style="padding-left:38px;"
                                onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                                onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                        </div>
                    </div>
                </div>
                <p id="add-date-error" style="display:none;font-size:12px;color:#ef4444;margin-top:6px;">
                    From Date and To Date are required for this expense type.
                </p>
            </div>

            <!-- Paid By -->
            <div style="margin-bottom:16px;">
                <label class="ss-label">
                    Paid By
                    <span style="font-size:11px;font-weight:400;color:#94a3b8;margin-left:4px;">optional — can be set
                        later</span>
                </label>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <div style="position:relative;">
                        <i data-lucide="user"
                            style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                        <select name="paid_by" class="ss-input"
                            style="padding-left:38px;cursor:pointer;appearance:none;-webkit-appearance:none;"
                            onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                            onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                            <option value="">— Not paid yet —</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user->id ?>"><?= esc($user->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down"
                            style="position:absolute;right:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div
                            style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;">
                                <input type="radio" name="paid_by_option" value="me" id="pbo-me"
                                    style="width:16px;height:16px;accent-color:#5c6af0;cursor:pointer;"
                                    onchange="togglePaidBy(this.value)">
                                <span style="font-size:13px;font-weight:500;color:#334155;">Paid by me</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;">
                                <input type="radio" name="paid_by_option" value="none" id="pbo-none" checked
                                    style="width:16px;height:16px;accent-color:#5c6af0;cursor:pointer;"
                                    onchange="togglePaidBy(this.value)">
                                <span style="font-size:13px;font-weight:500;color:#334155;">Not paid yet</span>
                            </label>
                        </div>
                        <input type="hidden" name="paid_by" id="paid-by-value" value="">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Involved Roommates -->
            <div style="margin-bottom:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <label class="ss-label" style="margin-bottom:0;">Involved Roommates <span
                            style="color:#ef4444;">*</span></label>
                    <div style="display:flex;gap:6px;">
                        <button type="button" onclick="selectAllUsers()" class="ss-btn ss-btn-ghost"
                            style="padding:4px 10px;font-size:12px;min-height:28px;">All</button>
                        <button type="button" onclick="deselectAllUsers()" class="ss-btn ss-btn-ghost"
                            style="padding:4px 10px;font-size:12px;min-height:28px;">None</button>
                    </div>
                </div>
                <div id="involved-users-list"
                    style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;max-height:180px;overflow-y:auto;-webkit-overflow-scrolling:touch;">
                    <?php foreach ($users as $user): ?>
                        <label
                            style="display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;transition:background .1s;"
                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                            <input type="checkbox" name="involved_users[]" value="<?= $user->id ?>"
                                style="width:16px;height:16px;accent-color:#5c6af0;cursor:pointer;flex-shrink:0;">
                            <span style="font-size:14px;font-weight:500;color:#334155;"><?= esc($user->name) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p id="involved-error" style="display:none;font-size:12px;color:#ef4444;margin-top:6px;">
                    Please select at least one roommate.
                </p>
            </div>

            <!-- Actions -->
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closeAddModal()" class="ss-btn ss-btn-ghost"
                    style="flex:1;">Cancel</button>
                <button type="submit" id="addExpenseBtn" class="ss-btn ss-btn-primary" style="flex:2;">
                    <i data-lucide="plus" style="width:15px;height:15px;" id="addExpenseBtnIcon"></i>
                    <span id="addExpenseBtnText">Save Expense</span>
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
     EDIT EXPENSE MODAL
════════════════════════════════════════════════════════════════ -->
<div id="edit-modal-backdrop" onclick="closeEditModal()" style="
    display:none;position:fixed;inset:0;
    background:rgba(15,23,42,.45);z-index:100;
    backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);
"></div>

<div id="edit-expense-modal" style="
    display:none;position:fixed;
    top:50%;left:50%;
    transform:translate(-50%,-50%) scale(0.97);
    width:calc(100% - 32px);max-width:500px;
    max-height:90vh;
    background:#fff;border-radius:16px;
    box-shadow:0 20px 60px rgba(0,0,0,.15);
    z-index:101;opacity:0;
    transition:transform .2s ease, opacity .2s ease;
    flex-direction:column;
">
    <div
        style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid #f1f5f9;flex-shrink:0;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div
                style="width:34px;height:34px;border-radius:8px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="pencil" style="width:16px;height:16px;color:#4338ca;"></i>
            </div>
            <div>
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Edit Expense</h3>
                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0;">Update the expense details below</p>
            </div>
        </div>
        <button onclick="closeEditModal()" style="
            width:32px;height:32px;border-radius:8px;
            background:#f1f5f9;border:none;cursor:pointer;
            display:flex;align-items:center;justify-content:center;
            color:#64748b;transition:background .15s;flex-shrink:0;
        " onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
            <i data-lucide="x" style="width:16px;height:16px;"></i>
        </button>
    </div>

    <div style="overflow-y:auto;-webkit-overflow-scrolling:touch;flex:1;">

        <div id="edit-permission-denied" style="display:none;padding:20px 24px;">
            <div
                style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;padding:32px 24px;text-align:center;">
                <div
                    style="width:56px;height:56px;border-radius:12px;background:#fee2e2;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="lock" style="width:24px;height:24px;color:#dc2626;"></i>
                </div>
                <div>
                    <h4 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 6px;">Cannot Edit This Expense
                    </h4>
                    <p style="font-size:13px;color:#64748b;margin:0;line-height:1.6;">
                        You can only edit expenses that you paid for or expenses with no assigned payer.
                    </p>
                </div>
            </div>
        </div>

        <form id="editExpenseForm" style="display:block;padding:20px 24px 24px;">
            <input type="hidden" id="edit-expense-id" name="expense_id" value="">

            <!-- Expense Type -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="edit-exp-type">Expense Type <span style="color:#ef4444;">*</span></label>
                <div style="position:relative;">
                    <i data-lucide="tag"
                        style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                    <select id="edit-exp-type" name="expense_type_id" required class="ss-input"
                        style="padding-left:38px;cursor:pointer;appearance:none;-webkit-appearance:none;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                        onchange="onEditTypeChange(this.value)">
                        <option value="">— Select type —</option>
                        <?php foreach ($expenseTypes as $type): ?>
                            <option value="<?= $type->id ?>"><?= esc($type->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="chevron-down"
                        style="position:absolute;right:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                </div>
            </div>

            <!-- Description -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="edit-exp-description">Description</label>
                <input type="text" id="edit-exp-description" name="description" placeholder="e.g. Rent" class="ss-input"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            </div>

            <!-- Amount -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="edit-exp-amount">Amount <span style="color:#ef4444;">*</span></label>
                <div style="position:relative;">
                    <span
                        style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:15px;font-weight:600;color:#94a3b8;pointer-events:none;font-family:'JetBrains Mono',monospace;">₹</span>
                    <input type="number" id="edit-exp-amount" name="amount" placeholder="0.00" min="0" step="0.01"
                        required class="ss-input" style="padding-left:30px;font-family:'JetBrains Mono',monospace;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                </div>
            </div>

            <!-- Billing Month -->
            <div style="margin-bottom:16px;">
                <label class="ss-label" for="edit-exp-billing-month">
                    Billing Month <span style="color:#ef4444;">*</span>
                </label>
                <div style="position:relative;">
                    <i data-lucide="calendar-range"
                        style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                    <input type="month" id="edit-exp-billing-month" name="billing_month" required class="ss-input"
                        style="padding-left:38px;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                </div>
            </div>

            <!-- Date range -->
            <div id="edit-date-range-wrap" style="margin-bottom:16px;">
                <div id="edit-date-range-hint" style="
                    display:flex;align-items:center;gap:6px;
                    padding:8px 12px;margin-bottom:10px;
                    background:#fef9c3;border:1px solid #fde68a;border-radius:8px;
                    font-size:12px;color:#92400e;">
                    <i data-lucide="info" style="width:13px;height:13px;flex-shrink:0;"></i>
                    <span id="edit-date-range-hint-text">Select an expense type to see if dates are required.</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="ss-label" for="edit-exp-from">
                            From Date <span id="edit-from-required-star" style="color:#ef4444;display:none;">*</span>
                        </label>
                        <div style="position:relative;">
                            <i data-lucide="calendar"
                                style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                            <input type="date" id="edit-exp-from" name="from_date" class="ss-input"
                                style="padding-left:38px;"
                                onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                                onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                        </div>
                    </div>
                    <div>
                        <label class="ss-label" for="edit-exp-to">
                            To Date <span id="edit-to-required-star" style="color:#ef4444;display:none;">*</span>
                        </label>
                        <div style="position:relative;">
                            <i data-lucide="calendar"
                                style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                            <input type="date" id="edit-exp-to" name="to_date" class="ss-input"
                                style="padding-left:38px;"
                                onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                                onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                        </div>
                    </div>
                </div>
                <p id="edit-date-error" style="display:none;font-size:12px;color:#ef4444;margin-top:6px;">
                    From Date and To Date are required for this expense type.
                </p>
            </div>

            <!-- Paid By -->
            <div style="margin-bottom:16px;">
                <label class="ss-label">
                    Paid By
                    <span style="font-size:11px;font-weight:400;color:#94a3b8;margin-left:4px;">optional</span>
                </label>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <div style="position:relative;">
                        <i data-lucide="user"
                            style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                        <select id="edit-paid-by" name="paid_by" class="ss-input"
                            style="padding-left:38px;cursor:pointer;appearance:none;-webkit-appearance:none;"
                            onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                            onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                            <option value="">— Not paid yet —</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user->id ?>"><?= esc($user->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down"
                            style="position:absolute;right:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div
                            style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;">
                                <input type="radio" name="edit_paid_by_option" value="me" id="edit-pbo-me"
                                    style="width:16px;height:16px;accent-color:#5c6af0;cursor:pointer;"
                                    onchange="toggleEditPaidBy(this.value)">
                                <span style="font-size:13px;font-weight:500;color:#334155;">Paid by me</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;">
                                <input type="radio" name="edit_paid_by_option" value="none" id="edit-pbo-none"
                                    style="width:16px;height:16px;accent-color:#5c6af0;cursor:pointer;"
                                    onchange="toggleEditPaidBy(this.value)">
                                <span style="font-size:13px;font-weight:500;color:#334155;">Not paid yet</span>
                            </label>
                        </div>
                        <input type="hidden" name="paid_by" id="edit-paid-by-value" value="">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Involved Roommates -->
            <div style="margin-bottom:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <label class="ss-label" style="margin-bottom:0;">Involved Roommates <span
                            style="color:#ef4444;">*</span></label>
                    <div style="display:flex;gap:6px;">
                        <button type="button" onclick="editSelectAllUsers()" class="ss-btn ss-btn-ghost"
                            style="padding:4px 10px;font-size:12px;min-height:28px;">All</button>
                        <button type="button" onclick="editDeselectAllUsers()" class="ss-btn ss-btn-ghost"
                            style="padding:4px 10px;font-size:12px;min-height:28px;">None</button>
                    </div>
                </div>
                <div id="edit-involved-users-list"
                    style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;max-height:180px;overflow-y:auto;-webkit-overflow-scrolling:touch;">
                    <?php foreach ($users as $user): ?>
                        <label
                            style="display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;transition:background .1s;"
                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                            <input type="checkbox" name="involved_users[]" value="<?= $user->id ?>" class="edit-involved-cb"
                                style="width:16px;height:16px;accent-color:#5c6af0;cursor:pointer;flex-shrink:0;">
                            <span style="font-size:14px;font-weight:500;color:#334155;"><?= esc($user->name) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p id="edit-involved-error" style="display:none;font-size:12px;color:#ef4444;margin-top:6px;">
                    Please select at least one roommate.
                </p>
            </div>

            <!-- Actions -->
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closeEditModal()" class="ss-btn ss-btn-ghost"
                    style="flex:1;">Cancel</button>
                <button type="submit" id="editExpenseBtn" class="ss-btn ss-btn-primary" style="flex:2;">
                    <i data-lucide="save" style="width:15px;height:15px;" id="editExpenseBtnIcon"></i>
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

    // ── Role flag — bulk delete is admin-only ─────────────────────────
    var IS_ADMIN = <?= $currentUser['role'] === 'admin' ? 'true' : 'false' ?>;

    // ── Expense type → split_method map ─────────────────────────────
    var SPLIT_METHOD_MAP = {
        <?php foreach ($expenseTypes as $type): ?>
                '<?= (int) $type->id ?>': '<?= esc($type->split_method, 'js') ?>',
        <?php endforeach; ?>
    };

    // ── Money formatter ──────────────────────────────────────────────
    function fmt(n) {
        return '₹' + parseFloat(n || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }
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
        if (!IS_ADMIN) return;
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
        if (!IS_ADMIN) return;
        // Selects/deselects only the rows on the currently rendered page.
        // Use "Select all N expenses" to grab everything loaded.
        document.querySelectorAll('.row-checkbox').forEach(function (rowCb) {
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
        boxes.forEach(function (b) { if (b.checked) checkedCount++; });
        selectAll.checked = checkedCount === boxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
    }

    // "Select all N expenses" — expands selection beyond the current
    // page to every expense currently loaded in the table (_lastData).
    // Note: _lastData is the full unfiltered set from the last load, so
    // this selects everything loaded, not just rows matching an active
    // search term.
    function selectAllMatching() {
        if (!IS_ADMIN) return;
        _lastData.forEach(function (e) {
            _selectedIds.add(String(e.id));
        });
        document.querySelectorAll('.row-checkbox').forEach(function (cb) {
            cb.checked = _selectedIds.has(cb.dataset.id);
        });
        syncSelectAllCheckbox();
        updateBulkBar();
    }

    function updateBulkBar() {
        if (!IS_ADMIN) return;
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
        document.querySelectorAll('.row-checkbox').forEach(function (cb) { cb.checked = false; });
        var selectAll = document.getElementById('select-all-checkbox');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        updateBulkBar();
    }

    function bulkDeleteExpenses() {
        if (!IS_ADMIN) return;
        var ids = Array.from(_selectedIds);
        if (!ids.length) return;

        ssConfirm({
            title: 'Delete Expenses',
            message: 'Delete ' + ids.length + ' selected expense' + (ids.length > 1 ? 's' : '') + '? This cannot be undone.',
            confirmText: 'Delete',
            onConfirm: function () {
                $.ajax({
                    url: '/expense/bulkDeleteExpenses',
                    type: 'POST',
                    data: { 'ids[]': ids },
                    success: function (res) {
                        var count = (res && res.deleted) ? res.deleted : ids.length;
                        ssToast(count + ' expense' + (count > 1 ? 's' : '') + ' deleted.', 'success');
                        _selectedIds.clear();
                        updateBulkBar();
                        _expenseTable.reload();
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.error)
                            ? xhr.responseJSON.error
                            : 'Failed to delete selected expenses.';
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

    // ── Grid card dropdown — one shared instance ─────────────────────
    var _activeDropdown = null;

    function openCardMenu(btn, id) {
        // Close any open dropdown first
        closeCardMenu();

        var drop = document.createElement('div');
        drop.id = 'card-dropdown-' + id;
        drop.style.cssText = [
            'position:absolute', 'top:100%', 'right:0', 'margin-top:4px',
            'background:#fff', 'border:1px solid #e2e8f0', 'border-radius:10px',
            'box-shadow:0 8px 24px rgba(0,0,0,.10)', 'z-index:200',
            'min-width:140px', 'padding:4px 0', 'overflow:hidden',
        ].join(';');

        drop.innerHTML = ''
            + '<button onclick="openEditModal(\'' + id + '\');closeCardMenu();" style="'
            + 'width:100%;display:flex;align-items:center;gap:8px;padding:10px 14px;'
            + 'font-size:13px;font-weight:500;color:#4338ca;border:none;background:transparent;'
            + 'cursor:pointer;font-family:\'DM Sans\',sans-serif;text-align:left;transition:background .1s;"'
            + ' onmouseover="this.style.background=\'#f5f7ff\'" onmouseout="this.style.background=\'transparent\'">'
            + '<i data-lucide="pencil" style="width:13px;height:13px;flex-shrink:0;"></i>Edit'
            + '</button>'
            + '<button onclick="deleteFromCard(\'' + id + '\');closeCardMenu();" style="'
            + 'width:100%;display:flex;align-items:center;gap:8px;padding:10px 14px;'
            + 'font-size:13px;font-weight:500;color:#dc2626;border:none;background:transparent;'
            + 'cursor:pointer;font-family:\'DM Sans\',sans-serif;text-align:left;transition:background .1s;"'
            + ' onmouseover="this.style.background=\'#fef2f2\'" onmouseout="this.style.background=\'transparent\'">'
            + '<i data-lucide="trash-2" style="width:13px;height:13px;flex-shrink:0;"></i>Delete'
            + '</button>';

        btn.parentNode.style.position = 'relative';
        btn.parentNode.appendChild(drop);
        lucide.createIcons({ nodes: [drop] });
        _activeDropdown = drop;
    }

    function closeCardMenu() {
        if (_activeDropdown) {
            _activeDropdown.remove();
            _activeDropdown = null;
        }
    }

    // Close dropdown on outside click
    document.addEventListener('click', function (e) {
        if (_activeDropdown && !_activeDropdown.contains(e.target) && !e.target.closest('.card-menu-btn')) {
            closeCardMenu();
        }
    });

    function deleteFromCard(id) {
        ssConfirm({
            title: 'Delete Expense',
            message: 'Delete this expense? This cannot be undone.',
            confirmText: 'Delete',
            onConfirm: function () {
                $.ajax({
                    url: '/expense/deleteExpense/' + id,
                    type: 'DELETE',
                    success: function () {
                        ssToast('Expense deleted.', 'success');
                        _selectedIds.delete(String(id));
                        updateBulkBar();
                        _expenseTable.reload();
                    },
                    error: function () {
                        ssToast('Failed to delete expense.', 'error');
                    }
                });
            }
        });
    }

    // ── Render grid cards ────────────────────────────────────────────
    function renderGrid(data) {
        var grid = document.getElementById('expenses-grid');
        var emptyEl = document.getElementById('expenses-grid-empty');

        if (!data || data.length === 0) {
            grid.innerHTML = '';
            emptyEl.style.display = 'block';
            return;
        }
        emptyEl.style.display = 'none';

        grid.innerHTML = data.map(function (e) {
            var billingMonth = e.billing_month || '—';

            var paidByHtml = e.paid_by_name
                ? '<span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#334155;">'
                + '<i data-lucide="user" style="width:11px;height:11px;color:#94a3b8;"></i>'
                + e.paid_by_name + '</span>'
                : '<span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;'
                + 'font-size:11px;font-weight:600;background:#fef9c3;color:#a16207;">'
                + '<i data-lucide="clock" style="width:10px;height:10px;"></i>Pending</span>';

            return '<div style="'
                + 'background:#fff;border:1px solid #e2e8f0;border-radius:12px;'
                + 'padding:16px;display:flex;flex-direction:column;gap:12px;'
                + 'transition:border-color .15s;"'
                + ' onmouseover="this.style.borderColor=\'#c7d2fe\'" onmouseout="this.style.borderColor=\'#e2e8f0\'">'

                // Top row: type badge + three-dot menu
                + '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">'
                + '<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;'
                + 'font-size:12px;font-weight:600;background:#fce7f3;color:#be185d;">'
                + '<i data-lucide="tag" style="width:11px;height:11px;"></i>'
                + (e.expense_type || '—')
                + '</span>'
                + '<div style="position:relative;">'
                + '<button class="card-menu-btn" onclick="openCardMenu(this,\'' + e.id + '\')" style="'
                + 'width:30px;height:30px;border-radius:7px;border:1px solid #e2e8f0;'
                + 'background:#f8fafc;cursor:pointer;display:flex;align-items:center;'
                + 'justify-content:center;color:#64748b;transition:background .15s;flex-shrink:0;"'
                + ' onmouseover="this.style.background=\'#e2e8f0\'" onmouseout="this.style.background=\'#f8fafc\'">'
                + '<i data-lucide="more-horizontal" style="width:14px;height:14px;"></i>'
                + '</button>'
                + '</div>'
                + '</div>'

                // Amount
                + '<div style="font-size:22px;font-weight:700;color:#0f172a;'
                + 'font-family:\'JetBrains Mono\',monospace;letter-spacing:-0.02em;">'
                + fmt(e.amount)
                + '</div>'

                // Meta row: billing month + paid by
                + '<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">'
                + '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;'
                + 'font-size:11px;font-weight:600;background:#e0e7ff;color:#4338ca;'
                + 'font-family:\'JetBrains Mono\',monospace;">'
                + '<i data-lucide="calendar-range" style="width:10px;height:10px;"></i>'
                + billingMonth
                + '</span>'
                + paidByHtml
                + '</div>'

                + '</div>';
        }).join('');

        lucide.createIcons({ nodes: [grid] });
    }

    // ── Date-range hint helpers ──────────────────────────────────────
    function updateDateRangeUI(prefix, splitMethod) {
        var isDaysPresent = splitMethod === 'daysPresent';
        var hintEl = document.getElementById(prefix + '-date-range-hint');
        var hintText = document.getElementById(prefix + '-date-range-hint-text');
        var starFrom = document.getElementById(prefix + '-from-required-star');
        var starTo = document.getElementById(prefix + '-to-required-star');
        var errEl = document.getElementById(prefix + '-date-error');

        if (isDaysPresent) {
            hintEl.style.background = '#fce7f3';
            hintEl.style.borderColor = '#fbcfe8';
            hintEl.style.color = '#9d174d';
            hintText.textContent = 'Required for this type — absent days are calculated from the date range.';
            if (starFrom) starFrom.style.display = 'inline';
            if (starTo) starTo.style.display = 'inline';
        } else if (splitMethod === '') {
            hintEl.style.background = '#fef9c3';
            hintEl.style.borderColor = '#fde68a';
            hintEl.style.color = '#92400e';
            hintText.textContent = 'Select an expense type to see if dates are required.';
            if (starFrom) starFrom.style.display = 'none';
            if (starTo) starTo.style.display = 'none';
        } else {
            hintEl.style.background = '#f0fdf4';
            hintEl.style.borderColor = '#bbf7d0';
            hintEl.style.color = '#166534';
            hintText.textContent = 'Optional for this expense type.';
            if (starFrom) starFrom.style.display = 'none';
            if (starTo) starTo.style.display = 'none';
        }
        if (errEl) errEl.style.display = 'none';
    }

    function onAddTypeChange(typeId) {
        var method = SPLIT_METHOD_MAP[String(typeId)] || '';
        updateDateRangeUI('add', typeId ? method : '');
    }
    function onEditTypeChange(typeId) {
        var method = SPLIT_METHOD_MAP[String(typeId)] || '';
        updateDateRangeUI('edit', typeId ? method : '');
    }

    function validateDates(prefix) {
        var typeId = document.getElementById(
            prefix === 'add' ? 'exp-type' : 'edit-exp-type'
        ).value;
        var method = SPLIT_METHOD_MAP[String(typeId)] || '';
        if (method !== 'daysPresent') return true;
        var fromId = prefix === 'add' ? 'exp-from' : 'edit-exp-from';
        var toId = prefix === 'add' ? 'exp-to' : 'edit-exp-to';
        var fromVal = document.getElementById(fromId).value;
        var toVal = document.getElementById(toId).value;
        if (!fromVal || !toVal) {
            document.getElementById(prefix + '-date-error').style.display = 'block';
            return false;
        }
        document.getElementById(prefix + '-date-error').style.display = 'none';
        return true;
    }

    // ── SS.Table init ────────────────────────────────────────────────
    var _expenseTable = SS.Table({
        tbodyId: 'expenses-tbody',
        url: '/expense/getExpenses',
        countId: 'expense-count',
        searchPlaceholder: 'Search by type, description, paid by\u2026',
        pageSize: 15,
        colSpan: <?= $currentUser['role'] === 'admin' ? 9 : 8 ?>,

        cols: [
            { label: 'Type', key: 'expense_type' },
            { label: 'Description', key: 'description' },
            { label: 'Amount', key: 'amount' },
            { label: 'Billing Month', key: 'billing_month' },
            { label: 'Period', key: 'from_date' },
            { label: 'Paid By', key: 'paid_by_name' },
            { label: 'Involved', key: 'total_involved', align: 'center' },
            { label: 'Actions', key: null, sortable: false, align: 'right' },
        ],

        onLoad: function (data) {
            _lastData = data;
            var total = data.reduce(function (s, e) {
                return s + parseFloat(e.amount || 0);
            }, 0);
            document.getElementById('expense-total').textContent = fmt(total);
            // Keep grid in sync whenever data reloads
            if (_currentView === 'grid') renderGrid(data);
        },

        rowFn: function (e) {
            var fromDate = fmtDate(e.from_date);
            var toDate = fmtDate(e.to_date);
            var period = (!e.from_date && !e.to_date)
                ? '<span style="color:#cbd5e1;font-style:italic;">—</span>'
                : (fromDate === toDate ? fromDate : fromDate + ' \u2192 ' + toDate);

            var billingMonth = e.billing_month || '—';

            return '<tr style="transition:background .1s;" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'\'">'

                + (IS_ADMIN
                    ? '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;">'
                    + '<input type="checkbox" class="row-checkbox" data-id="' + e.id + '" style="width:16px;height:16px;cursor:pointer;">'
                    + '</td>'
                    : '')

                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">'
                + '<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#fce7f3;color:#be185d;">'
                + '<i data-lucide="tag" style="width:11px;height:11px;"></i>'
                + (e.expense_type || '—')
                + '</span></td>'

                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;max-width:200px;">'
                + '<span style="font-size:13px;color:#64748b;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'
                + (e.description || '<span style="color:#cbd5e1;font-style:italic;">No description</span>')
                + '</span></td>'

                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">'
                + '<span style="font-size:14px;font-weight:700;color:#0f172a;font-family:\'JetBrains Mono\',monospace;">'
                + fmt(e.amount) + '</span></td>'

                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">'
                + '<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#e0e7ff;color:#4338ca;font-family:\'JetBrains Mono\',monospace;">'
                + '<i data-lucide="calendar-range" style="width:11px;height:11px;"></i>'
                + billingMonth + '</span></td>'

                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">'
                + '<span style="font-size:13px;color:#64748b;">' + period + '</span></td>'

                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">'
                + (e.paid_by_name
                    ? '<span style="display:inline-flex;align-items:center;gap:5px;font-size:13px;color:#334155;">'
                    + '<i data-lucide="user" style="width:12px;height:12px;color:#94a3b8;"></i>'
                    + e.paid_by_name + '</span>'
                    : '<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#fef9c3;color:#a16207;">'
                    + '<i data-lucide="clock" style="width:11px;height:11px;"></i>Pending</span>')
                + '</td>'

                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;text-align:center;">'
                + '<span class="ss-involved-badge" data-names="' + (e.involved_names || '') + '" style="'
                + 'display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;'
                + 'font-size:12px;font-weight:600;background:#dbeafe;color:#1d4ed8;cursor:default;">'
                + '<i data-lucide="users" style="width:11px;height:11px;"></i>'
                + (e.total_involved || 0) + '</span></td>'

                + '<td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">'
                + '<div style="display:inline-flex;gap:6px;align-items:center;">'
                + '<button class="editExpenseBtn" data-id="' + e.id + '" style="'
                + 'display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:6px;'
                + 'background:#e0e7ff;color:#4338ca;border:none;cursor:pointer;'
                + 'font-size:12px;font-weight:600;font-family:\'DM Sans\',sans-serif;min-height:32px;transition:background .15s;"'
                + ' onmouseover="this.style.background=\'#c7d2fe\'" onmouseout="this.style.background=\'#e0e7ff\'">'
                + '<i data-lucide="pencil" style="width:12px;height:12px;"></i>Edit</button>'
                + '<button class="deleteExpenseBtn" data-id="' + e.id + '" style="'
                + 'display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:6px;'
                + 'background:#fee2e2;color:#dc2626;border:none;cursor:pointer;'
                + 'font-size:12px;font-weight:600;font-family:\'DM Sans\',sans-serif;min-height:32px;transition:background .15s;"'
                + ' onmouseover="this.style.background=\'#fecaca\'" onmouseout="this.style.background=\'#fee2e2\'">'
                + '<i data-lucide="trash-2" style="width:12px;height:12px;"></i>Delete</button>'
                + '</div></td>'

                + '</tr>';
        },

        onRender: function (data) {
            // Wire list-view action buttons
            document.querySelectorAll('.deleteExpenseBtn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = this.dataset.id;
                    ssConfirm({
                        title: 'Delete Expense',
                        message: 'Delete this expense? This cannot be undone.',
                        confirmText: 'Delete',
                        onConfirm: function () {
                            $.ajax({
                                url: '/expense/deleteExpense/' + id,
                                type: 'DELETE',
                                success: function () {
                                    ssToast('Expense deleted.', 'success');
                                    _selectedIds.delete(id);
                                    updateBulkBar();
                                    _expenseTable.reload();
                                },
                                error: function () {
                                    ssToast('Failed to delete expense.', 'error');
                                }
                            });
                        }
                    });
                });
            });

            document.querySelectorAll('.editExpenseBtn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    openEditModal(this.dataset.id);
                });
            });

            // Wire row checkboxes — restore checked state from _selectedIds
            // (selection persists across search/sort/page changes)
            if (IS_ADMIN) {
                document.querySelectorAll('.row-checkbox').forEach(function (cb) {
                    cb.checked = _selectedIds.has(cb.dataset.id);
                    cb.addEventListener('change', function () {
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
            .forEach(function (cb) { cb.checked = true; });
    }
    function deselectAllUsers() {
        document.querySelectorAll('#involved-users-list input[type="checkbox"]')
            .forEach(function (cb) { cb.checked = false; });
    }

    var currentUserId = '<?= (int) $currentUser['id'] ?>';
    function togglePaidBy(val) {
        var input = document.getElementById('paid-by-value');
        if (input) input.value = val === 'me' ? currentUserId : '';
    }

    function openAddModal() {
        var backdrop = document.getElementById('modal-backdrop');
        var modal = document.getElementById('add-expense-modal');
        backdrop.style.display = 'block';
        modal.style.display = 'flex';
        requestAnimationFrame(function () {
            modal.style.opacity = '1';
            modal.style.transform = 'translate(-50%,-50%) scale(1)';
        });
        updateDateRangeUI('add', '');
        document.getElementById('exp-type').focus();
    }

    function closeAddModal() {
        var modal = document.getElementById('add-expense-modal');
        var backdrop = document.getElementById('modal-backdrop');
        modal.style.opacity = '0';
        modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
        setTimeout(function () {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
            document.getElementById('addExpenseForm').reset();
            deselectAllUsers();
            document.getElementById('involved-error').style.display = 'none';
            document.getElementById('add-date-error').style.display = 'none';
            updateDateRangeUI('add', '');
            var pboNone = document.getElementById('pbo-none');
            var pbInput = document.getElementById('paid-by-value');
            if (pboNone) pboNone.checked = true;
            if (pbInput) pbInput.value = '';
            resetAddBtn();
        }, 180);
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

    document.getElementById('addExpenseForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var checked = document.querySelectorAll('#involved-users-list input[type="checkbox"]:checked');
        if (checked.length === 0) {
            document.getElementById('involved-error').style.display = 'block';
            var list = document.getElementById('involved-users-list');
            list.style.borderColor = '#ef4444';
            setTimeout(function () { list.style.borderColor = '#e2e8f0'; }, 2000);
            return;
        }
        document.getElementById('involved-error').style.display = 'none';
        if (!validateDates('add')) return;
        setAddBtnLoading();
        $.post('/expense/addExpense', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                ssToast('Expense added successfully!', 'success');
                closeAddModal();
                _expenseTable.reload();
            } else {
                ssToast('Failed to save expense.', 'error');
                resetAddBtn();
            }
        }, 'json').fail(function () {
            ssToast('Something went wrong.', 'error');
            resetAddBtn();
        });
    });


    // ── Edit modal ───────────────────────────────────────────────────
    var _editingId = null;

    function openEditModal(id) {
        _editingId = id;
        var backdrop = document.getElementById('edit-modal-backdrop');
        var modal = document.getElementById('edit-expense-modal');
        backdrop.style.display = 'block';
        modal.style.display = 'flex';
        requestAnimationFrame(function () {
            modal.style.opacity = '1';
            modal.style.transform = 'translate(-50%,-50%) scale(1)';
        });

        $.get('/expense/getExpense/' + id, function (res) {
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

            var method = SPLIT_METHOD_MAP[String(d.expense_type_id)] || '';
            updateDateRangeUI('edit', d.expense_type_id ? method : '');

            var isAdmin = <?= $currentUser['role'] === 'admin' ? 'true' : 'false' ?>;
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
            document.querySelectorAll('.edit-involved-cb').forEach(function (cb) {
                cb.checked = involvedIds.indexOf(parseInt(cb.value, 10)) !== -1;
            });

            lucide.createIcons();
            document.getElementById('edit-exp-type').focus();
        }).fail(function () {
            ssToast('Failed to load expense data.', 'error');
            closeEditModal();
        });
    }

    function closeEditModal() {
        var modal = document.getElementById('edit-expense-modal');
        var backdrop = document.getElementById('edit-modal-backdrop');
        modal.style.opacity = '0';
        modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
        setTimeout(function () {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
            document.getElementById('editExpenseForm').reset();
            document.getElementById('edit-involved-error').style.display = 'none';
            document.getElementById('edit-date-error').style.display = 'none';
            updateDateRangeUI('edit', '');
            resetEditBtn();
            _editingId = null;
        }, 180);
    }

    function editSelectAllUsers() {
        document.querySelectorAll('.edit-involved-cb').forEach(function (cb) { cb.checked = true; });
    }
    function editDeselectAllUsers() {
        document.querySelectorAll('.edit-involved-cb').forEach(function (cb) { cb.checked = false; });
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

    document.getElementById('editExpenseForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var checked = document.querySelectorAll('.edit-involved-cb:checked');
        if (checked.length === 0) {
            document.getElementById('edit-involved-error').style.display = 'block';
            var list = document.getElementById('edit-involved-users-list');
            list.style.borderColor = '#ef4444';
            setTimeout(function () { list.style.borderColor = '#e2e8f0'; }, 2000);
            return;
        }
        document.getElementById('edit-involved-error').style.display = 'none';
        if (!validateDates('edit')) return;
        setEditBtnLoading();
        $.post('/expense/updateExpense/' + _editingId, $(this).serialize(), function (res) {
            if (res.status === 'success') {
                ssToast('Expense updated successfully!', 'success');
                closeEditModal();
                _expenseTable.reload();
            } else {
                ssToast('Failed to update expense.', 'error');
                resetEditBtn();
            }
        }, 'json').fail(function () {
            ssToast('Something went wrong.', 'error');
            resetEditBtn();
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        closeAddModal();
        closeEditModal();
    });


    // ── Involved-users tooltip ───────────────────────────────────────
    (function () {
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
        document.addEventListener('mouseover', function (ev) {
            var badge = ev.target.closest('.ss-involved-badge');
            if (!badge) return;
            var names = (badge.getAttribute('data-names') || '').trim();
            if (!names) return;
            var list = names.split(',').map(function (n) { return '\u2022 ' + n.trim(); }).join('<br>');
            tip.innerHTML = '<div style="margin-bottom:4px;font-weight:700;font-size:11px;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;">Involved</div>' + list;
            clearTimeout(showTimer);
            showTimer = setTimeout(function () {
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
                requestAnimationFrame(function () { tip.style.opacity = '1'; });
            }, 80);
        });
        document.addEventListener('mouseout', function (ev) {
            if (!ev.target.closest('.ss-involved-badge')) return;
            clearTimeout(showTimer);
            tip.style.opacity = '0';
            setTimeout(function () { tip.style.display = 'none'; }, 120);
        });
    })();
</script>
<?= $this->endSection() ?>

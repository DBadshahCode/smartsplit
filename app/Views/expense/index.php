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
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:13px;color:#64748b;">Total:</span>
            <span id="expense-total"
                style="font-size:15px;font-weight:700;color:#0f172a;font-family:'JetBrains Mono',monospace;">—</span>
        </div>
    </div>

    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table style="width:100%;border-collapse:collapse;min-width:620px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th
                        style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Type</th>
                    <th
                        style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Description</th>
                    <th
                        style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Amount</th>
                    <th
                        style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Period</th>
                    <th
                        style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Paid By</th>
                    <th
                        style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Involved</th>
                    <th
                        style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                        Actions</th>
                </tr>
            </thead>
            <tbody id="expenses-tbody">
                <tr>
                    <td colspan="6" style="padding:48px 16px;text-align:center;color:#cbd5e1;">
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
     ADD EXPENSE MODAL
════════════════════════════════════════════════════════════════ -->

<!-- Backdrop -->
<div id="modal-backdrop" onclick="closeAddModal()" style="
    display:none;position:fixed;inset:0;
    background:rgba(15,23,42,.45);z-index:100;
    backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);
"></div>

<!-- Modal — taller form so scrollable on mobile -->
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
    display:none;
    flex-direction:column;
">
    <!-- Modal header — fixed -->
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

    <!-- Modal body — scrollable -->
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
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
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
                    <input type="text" id="exp-description" name="description" placeholder="e.g. Rent" class="ss-input"
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

            <!-- Date range — side by side -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <div>
                    <label class="ss-label" for="exp-from">From Date</label>
                    <div style="position:relative;">
                        <i data-lucide="calendar"
                            style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                        <input type="date" id="exp-from" name="from_date" value="<?= date('Y-m-d') ?>" class="ss-input"
                            style="padding-left:38px;"
                            onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                            onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    </div>
                </div>
                <div>
                    <label class="ss-label" for="exp-to">To Date</label>
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

            <!-- Paid By -->
            <div style="margin-bottom:16px;">
                <label class="ss-label">
                    Paid By
                    <span style="font-size:11px;font-weight:400;color:#94a3b8;margin-left:4px;">optional — can be set
                        later</span>
                </label>

                <?php if ($role === 'admin'): ?>
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
                        <!-- Toggle: paid by me or not yet -->
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
                        <!-- Hidden input — empty by default, set to userId when "paid by me" chosen -->
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
                            style="padding:4px 10px;font-size:12px;min-height:28px;">
                            All
                        </button>
                        <button type="button" onclick="deselectAllUsers()" class="ss-btn ss-btn-ghost"
                            style="padding:4px 10px;font-size:12px;min-height:28px;">
                            None
                        </button>
                    </div>
                </div>

                <!-- Checkbox-style user picker -->
                <div id="involved-users-list" style="
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
                <button type="button" onclick="closeAddModal()" class="ss-btn ss-btn-ghost" style="flex:1;">
                    Cancel
                </button>
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

<!-- Edit Backdrop -->
<div id="edit-modal-backdrop" onclick="closeEditModal()" style="
    display:none;position:fixed;inset:0;
    background:rgba(15,23,42,.45);z-index:100;
    backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);
"></div>

<!-- Edit Modal -->
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
    <!-- Modal header -->
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

    <!-- Modal body — scrollable -->
    <div style="overflow-y:auto;-webkit-overflow-scrolling:touch;flex:1;">
        <!-- Permission Denied Message -->
        <div id="edit-permission-denied" style="display:none;padding:20px 24px;">
            <div style="
                display:flex;flex-direction:column;align-items:center;
                justify-content:center;gap:12px;
                padding:32px 24px;
                text-align:center;
            ">
                <div style="
                    width:56px;height:56px;
                    border-radius:12px;background:#fee2e2;
                    display:flex;align-items:center;justify-content:center;
                ">
                    <i data-lucide="lock" style="width:24px;height:24px;color:#dc2626;"></i>
                </div>
                <div>
                    <h4 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 6px;">
                        Cannot Edit This Expense
                    </h4>
                    <p style="font-size:13px;color:#64748b;margin:0;line-height:1.6;">
                        You can only edit expenses that you paid for or expenses with no assigned payer. This expense was paid by another user.
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
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
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
                        ) onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                </div>
            </div>

            <!-- Date range -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <div>
                    <label class="ss-label" for="edit-exp-from">From Date</label>
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
                    <label class="ss-label" for="edit-exp-to">To Date</label>
                    <div style="position:relative;">
                        <i data-lucide="calendar"
                            style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                        <input type="date" id="edit-exp-to" name="to_date" class="ss-input" style="padding-left:38px;"
                            onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                            onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    </div>
                </div>
            </div>

            <!-- Paid By -->
            <div style="margin-bottom:16px;">
                <label class="ss-label">
                    Paid By
                    <span style="font-size:11px;font-weight:400;color:#94a3b8;margin-left:4px;">optional</span>
                </label>

                <?php if ($role === 'admin'): ?>
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
                <div id="edit-involved-users-list" style="
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
                <button type="button" onclick="closeEditModal()" class="ss-btn ss-btn-ghost" style="flex:1;">
                    Cancel
                </button>
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

    // ── Helpers ──────────────────────────────────────────────────────
    function fmt(n) {
        return '₹' + parseFloat(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function fmtDate(d) {
        if (!d) return '—';
        const raw = typeof d === 'object' ? (d.date || '') : String(d);
        return raw.split(' ')[0] || '—';
    }

    // ── Load & render expenses ───────────────────────────────────────
    function loadExpenses() {
        $.get('/expense/getExpenses', function (res) {
            const expenses = res.data || [];
            document.getElementById('expense-count').textContent = expenses.length;

            // Running total
            const total = expenses.reduce(function (s, e) { return s + parseFloat(e.amount || 0); }, 0);
            document.getElementById('expense-total').textContent = fmt(total);

            const tbody = document.getElementById('expenses-tbody');

            if (expenses.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="padding:48px 16px;text-align:center;color:#cbd5e1;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i data-lucide="receipt" style="width:24px;height:24px;color:#e2e8f0;"></i>
                            <span style="font-size:14px;">No expenses recorded yet</span>
                        </div>
                    </td>
                </tr>`;
                lucide.createIcons();
                return;
            }

            tbody.innerHTML = expenses.map(function (e) {
                const fromDate = fmtDate(e.from_date);
                const toDate = fmtDate(e.to_date);
                const period = (fromDate === toDate || toDate === '—')
                    ? fromDate
                    : fromDate + ' → ' + toDate;

                return `<tr style="transition:background .1s;"
                        onmouseover="this.style.background='#f8fafc'"
                        onmouseout="this.style.background=''">

                <!-- Type -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="
                        display:inline-flex;align-items:center;gap:6px;
                        padding:4px 10px;border-radius:999px;
                        font-size:12px;font-weight:600;
                        background:#fce7f3;color:#be185d;
                    ">
                        <i data-lucide="tag" style="width:11px;height:11px;"></i>
                        ${e.expense_type || '—'}
                    </span>
                </td>

                <!-- Description -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;max-width:220px;">
                    <span style="font-size:13px;color:#64748b;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${e.description || '<span style="color:#cbd5e1;font-style:italic;">No description</span>'}
                    </span>
                </td>

                <!-- Amount -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="font-size:14px;font-weight:700;color:#0f172a;font-family:'JetBrains Mono',monospace;">
                        ${fmt(e.amount)}
                    </span>
                </td>

                <!-- Period -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="font-size:13px;color:#64748b;">${period}</span>
                </td>

                <!-- Paid By -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    ${e.paid_by_name
                        ? `<span style="display:inline-flex;align-items:center;gap:5px;font-size:13px;color:#334155;">
                               <i data-lucide="user" style="width:12px;height:12px;color:#94a3b8;"></i>
                               ${e.paid_by_name}
                           </span>`
                        : `<span style="
                               display:inline-flex;align-items:center;gap:5px;
                               padding:3px 10px;border-radius:999px;
                               font-size:12px;font-weight:600;
                               background:#fef9c3;color:#a16207;
                           ">
                               <i data-lucide="clock" style="width:11px;height:11px;"></i>
                               Pending
                           </span>`
                    }
                </td>

                <!-- Involved count -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span class="ss-involved-badge" data-names="${e.involved_names || ''}" style="
                        display:inline-flex;align-items:center;gap:5px;
                        padding:3px 10px;border-radius:999px;
                        font-size:12px;font-weight:600;
                        background:#dbeafe;color:#1d4ed8;
                        position:relative;cursor:default;
                    ">
                        <i data-lucide="users" style="width:11px;height:11px;"></i>
                        ${e.total_involved || 0}
                    </span>
                </td>

                <!-- Actions -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                    <div style="display:inline-flex;gap:6px;align-items:center;">
                        <button class="editExpenseBtn"
                            data-id="${e.id}"
                            style="
                                display:inline-flex;align-items:center;gap:5px;
                                padding:6px 12px;border-radius:6px;
                                background:#e0e7ff;color:#4338ca;
                                border:none;cursor:pointer;
                                font-size:12px;font-weight:600;
                                font-family:'DM Sans',sans-serif;
                                min-height:32px;transition:background .15s;
                            "
                            onmouseover="this.style.background='#c7d2fe'"
                            onmouseout="this.style.background='#e0e7ff'">
                            <i data-lucide="pencil" style="width:12px;height:12px;"></i>
                            Edit
                        </button>
                        <button class="deleteExpenseBtn"
                            data-id="${e.id}"
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
                    </div>
                </td>
            </tr>`;
            }).join('');

            lucide.createIcons();

            // Delete handler
            document.querySelectorAll('.deleteExpenseBtn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    if (!confirm('Delete this expense? This cannot be undone.')) return;
                    $.ajax({
                        url: '/expense/deleteExpense/' + id,
                        type: 'DELETE',
                        success: function () {
                            ssToast('Expense deleted.', 'success');
                            loadExpenses();
                        },
                        error: function () {
                            ssToast('Failed to delete expense.', 'error');
                        }
                    });
                });
            });

            // Edit handler
            document.querySelectorAll('.editExpenseBtn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    openEditModal(this.dataset.id);
                });
            });
        });
    }
    loadExpenses();


    // ── Involved users helpers ───────────────────────────────────────
    function selectAllUsers() {
        document.querySelectorAll('#involved-users-list input[type="checkbox"]')
            .forEach(function (cb) { cb.checked = true; });
    }
    function deselectAllUsers() {
        document.querySelectorAll('#involved-users-list input[type="checkbox"]')
            .forEach(function (cb) { cb.checked = false; });
    }


    // ── Non-admin paid-by toggle ─────────────────────────────────────
    const currentUserId = '<?= $userId ?>';
    function togglePaidBy(val) {
        const input = document.getElementById('paid-by-value');
        if (input) {
            input.value = val === 'me' ? currentUserId : '';
        }
    }

    // ── Modal open / close ───────────────────────────────────────────
    function openAddModal() {
        const backdrop = document.getElementById('modal-backdrop');
        const modal = document.getElementById('add-expense-modal');
        backdrop.style.display = 'block';
        modal.style.display = 'flex';
        requestAnimationFrame(function () {
            modal.style.opacity = '1';
            modal.style.transform = 'translate(-50%,-50%) scale(1)';
        });
        document.getElementById('exp-type').focus();
    }
    function closeAddModal() {
        const modal = document.getElementById('add-expense-modal');
        const backdrop = document.getElementById('modal-backdrop');
        modal.style.opacity = '0';
        modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
        setTimeout(function () {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
            document.getElementById('addExpenseForm').reset();
            deselectAllUsers();
            document.getElementById('involved-error').style.display = 'none';
            // Reset non-admin paid-by toggle
            const pboNone = document.getElementById('pbo-none');
            const pbInput = document.getElementById('paid-by-value');
            if (pboNone) pboNone.checked = true;
            if (pbInput) pbInput.value = '';
            resetAddBtn();
        }, 180);
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAddModal();
    });


    // ── Submit button helpers ────────────────────────────────────────
    function setAddBtnLoading() {
        const btn = document.getElementById('addExpenseBtn');
        const text = document.getElementById('addExpenseBtnText');
        const icon = document.getElementById('addExpenseBtnIcon');
        btn.disabled = true;
        btn.style.opacity = '0.75';
        text.textContent = 'Saving…';
        icon.setAttribute('data-lucide', 'loader');
        lucide.createIcons();
    }
    function resetAddBtn() {
        const btn = document.getElementById('addExpenseBtn');
        const text = document.getElementById('addExpenseBtnText');
        const icon = document.getElementById('addExpenseBtnIcon');
        if (!btn) return;
        btn.disabled = false;
        btn.style.opacity = '1';
        text.textContent = 'Save Expense';
        icon.setAttribute('data-lucide', 'plus');
        lucide.createIcons();
    }


    // ── Form submit ──────────────────────────────────────────────────
    document.getElementById('addExpenseForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Validate at least one roommate selected
        const checked = document.querySelectorAll('#involved-users-list input[type="checkbox"]:checked');
        if (checked.length === 0) {
            document.getElementById('involved-error').style.display = 'block';
            document.getElementById('involved-users-list').style.borderColor = '#ef4444';
            setTimeout(function () {
                document.getElementById('involved-users-list').style.borderColor = '#e2e8f0';
            }, 2000);
            return;
        }
        document.getElementById('involved-error').style.display = 'none';

        setAddBtnLoading();

        $.post('/expense/addExpense', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                ssToast('Expense added successfully!', 'success');
                closeAddModal();
                loadExpenses();
            } else {
                ssToast('Failed to save expense.', 'error');
                resetAddBtn();
            }
        }, 'json').fail(function () {
            ssToast('Something went wrong.', 'error');
            resetAddBtn();
        });
    });

    // ── Edit modal: open / close ────────────────────────────────────
    var _editingId = null;

    function openEditModal(id) {
        _editingId = id;
        // Show modal immediately with loading state
        var backdrop = document.getElementById('edit-modal-backdrop');
        var modal = document.getElementById('edit-expense-modal');
        backdrop.style.display = 'block';
        modal.style.display = 'flex';
        requestAnimationFrame(function () {
            modal.style.opacity = '1';
            modal.style.transform = 'translate(-50%,-50%) scale(1)';
        });

        // Fetch expense data
        $.get('/expense/getExpense/' + id, function (res) {
            var d = res.data;
            var canEdit = d.can_edit || false;

            // Show permission denied message if user cannot edit
            var permDeniedDiv = document.getElementById('edit-permission-denied');
            var formDiv = document.querySelector('#edit-expense-modal form');
            if (!canEdit) {
                if (permDeniedDiv) permDeniedDiv.style.display = 'block';
                if (formDiv) formDiv.style.display = 'none';
                lucide.createIcons();
                return;
            }

            // Hide permission denied message and show form
            if (permDeniedDiv) permDeniedDiv.style.display = 'none';
            if (formDiv) formDiv.style.display = 'block';

            // Populate fields
            document.getElementById('edit-expense-id').value = d.id;
            document.getElementById('edit-exp-description').value = d.description;
            document.getElementById('edit-exp-type').value = d.expense_type_id;
            document.getElementById('edit-exp-amount').value = d.amount;

            // Dates: CI4 Time serialises as "YYYY-MM-DD HH:MM:SS" — take first 10 chars
            var fromStr = (d.from_date || '').substring(0, 10);
            var toStr = (d.to_date || '').substring(0, 10);
            document.getElementById('edit-exp-from').value = fromStr;
            document.getElementById('edit-exp-to').value = toStr;

            // Paid by — admin select vs user radio
            var isAdmin = <?= session()->get('role') === 'admin' ? 'true' : 'false' ?>;
            if (isAdmin) {
                var sel = document.getElementById('edit-paid-by');
                if (sel) sel.value = d.paid_by || '';
            } else {
                var meRadio = document.getElementById('edit-pbo-me');
                var noneRadio = document.getElementById('edit-pbo-none');
                var hiddenPb = document.getElementById('edit-paid-by-value');
                var myId = '<?= (int) $userId ?>';
                if (d.paid_by && String(d.paid_by) === myId) {
                    if (meRadio) meRadio.checked = true;
                    if (hiddenPb) hiddenPb.value = myId;
                } else {
                    if (noneRadio) noneRadio.checked = true;
                    if (hiddenPb) hiddenPb.value = '';
                }
            }

            // Involved checkboxes
            var involvedIds = d.involved_ids || [];
            document.querySelectorAll('.edit-involved-cb').forEach(function (cb) {
                cb.checked = involvedIds.indexOf(parseInt(cb.value)) !== -1;
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
            resetEditBtn();
            _editingId = null;
        }, 180);
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeEditModal();
    });


    // ── Edit modal: involved-users helpers ──────────────────────────
    function editSelectAllUsers() {
        document.querySelectorAll('.edit-involved-cb').forEach(function (cb) { cb.checked = true; });
    }
    function editDeselectAllUsers() {
        document.querySelectorAll('.edit-involved-cb').forEach(function (cb) { cb.checked = false; });
    }


    // ── Edit modal: non-admin paid-by toggle ────────────────────────
    function toggleEditPaidBy(val) {
        var input = document.getElementById('edit-paid-by-value');
        if (input) {
            input.value = val === 'me' ? '<?= (int) $userId ?>' : '';
        }
    }


    // ── Edit modal: button state helpers ────────────────────────────
    function setEditBtnLoading() {
        var btn = document.getElementById('editExpenseBtn');
        var text = document.getElementById('editExpenseBtnText');
        btn.disabled = true;
        btn.style.opacity = '0.75';
        text.textContent = 'Saving…';
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


    // ── Edit modal: form submit ──────────────────────────────────────
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

        setEditBtnLoading();

        $.post('/expense/updateExpense/' + _editingId, $(this).serialize(), function (res) {
            if (res.status === 'success') {
                ssToast('Expense updated successfully!', 'success');
                closeEditModal();
                loadExpenses();
            } else {
                ssToast('Failed to update expense.', 'error');
                resetEditBtn();
            }
        }, 'json').fail(function () {
            ssToast('Something went wrong.', 'error');
            resetEditBtn();
        });
    });


    /* ── Involved-users tooltip ── */
    (function () {
        // Create singleton tooltip element
        var tip = document.createElement('div');
        tip.id = 'ss-involved-tip';
        tip.style.cssText = [
            'position:fixed',
            'z-index:9999',
            'background:#1e293b',
            'color:#f8fafc',
            'font-size:12px',
            'font-family:"DM Sans",sans-serif',
            'font-weight:500',
            'line-height:1.5',
            'padding:8px 12px',
            'border-radius:8px',
            'box-shadow:0 4px 16px rgba(0,0,0,.18)',
            'pointer-events:none',
            'white-space:pre',
            'max-width:220px',
            'white-space:normal',
            'word-break:break-word',
            'display:none',
            'transition:opacity .12s',
            'opacity:0',
        ].join(';');
        document.body.appendChild(tip);

        var showTimer;

        document.addEventListener('mouseover', function (ev) {
            var badge = ev.target.closest('.ss-involved-badge');
            if (!badge) return;
            var names = (badge.getAttribute('data-names') || '').trim();
            if (!names) {
                tip.style.display = 'none';
                return;
            }
            // Build bullet list
            var list = names.split(',').map(function (n) { return '• ' + n.trim(); }).join('\n');
            tip.innerHTML = '<div style="margin-bottom:4px;font-weight:700;font-size:11px;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;">Involved</div>' + list.replace(/\n/g, '<br>');

            clearTimeout(showTimer);
            showTimer = setTimeout(function () {
                positionTip(badge);
                tip.style.display = 'block';
                requestAnimationFrame(function () { tip.style.opacity = '1'; });
            }, 80);
        });

        document.addEventListener('mouseout', function (ev) {
            var badge = ev.target.closest('.ss-involved-badge');
            if (!badge) return;
            clearTimeout(showTimer);
            tip.style.opacity = '0';
            setTimeout(function () { tip.style.display = 'none'; }, 120);
        });

        document.addEventListener('scroll', function () {
            var badge = document.querySelector('.ss-involved-badge:hover');
            if (badge) positionTip(badge);
        }, true);

        function positionTip(badge) {
            var rect = badge.getBoundingClientRect();
            var tipW = tip.offsetWidth || 180;
            var tipH = tip.offsetHeight || 60;
            var left = rect.left + rect.width / 2 - tipW / 2;
            var top = rect.top - tipH - 8;
            // keep within viewport
            if (left < 8) left = 8;
            if (left + tipW > window.innerWidth - 8) left = window.innerWidth - tipW - 8;
            if (top < 8) top = rect.bottom + 8; // flip below if no room
            tip.style.left = left + 'px';
            tip.style.top = top + 'px';
        }
    })();
</script>
<?= $this->endSection() ?>
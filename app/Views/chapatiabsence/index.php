<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Chapati Absences<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Page header ─────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">Chapati Absences</h1>
        <p class="page-subtitle">Record days each roommate was absent during a chapati period</p>
    </div>
    <button onclick="openAddModal()" class="ss-btn ss-btn-primary">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        Record Absence
    </button>
</div>

<!-- ── Info strip ──────────────────────────────────────────────── -->
<div style="
    display:flex;align-items:flex-start;gap:10px;
    padding:12px 16px;margin-bottom:24px;
    background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;
">
    <i data-lucide="info" style="width:16px;height:16px;color:#1d4ed8;flex-shrink:0;margin-top:1px;"></i>
    <p style="font-size:13px;color:#1e40af;margin:0;line-height:1.6;">
        Absences reduce a user's share of the chapati bill for that period.
        A user with <strong>0 absent days</strong> (or no record) is treated as fully present.
        Record only the days someone was actually away.
    </p>
</div>

<!-- ── Absences table card ─────────────────────────────────────── -->
<div class="ss-card">
    <div class="ss-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">All Absence Records</h2>
            <p style="font-size:13px;color:#94a3b8;margin:3px 0 0;">
                <span id="absence-count">—</span> records
            </p>
        </div>
    </div>

    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table style="width:100%;border-collapse:collapse;min-width:480px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Member</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Chapati Period</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Days Absent</th>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody id="absence-tbody">
                <tr>
                    <td colspan="4" style="padding:48px 16px;text-align:center;color:#cbd5e1;">
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
     ADD ABSENCE MODAL
════════════════════════════════════════════════════════════════ -->

<!-- Backdrop -->
<div id="modal-backdrop" onclick="closeAddModal()" style="
    display:none;position:fixed;inset:0;
    background:rgba(15,23,42,.45);z-index:100;
    backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);
"></div>

<!-- Modal -->
<div id="add-absence-modal" style="
    display:none;position:fixed;
    top:50%;left:50%;
    transform:translate(-50%,-50%) scale(0.97);
    width:calc(100% - 32px);max-width:460px;
    background:#fff;border-radius:16px;
    box-shadow:0 20px 60px rgba(0,0,0,.15);
    z-index:101;opacity:0;
    transition:transform .2s ease, opacity .2s ease;
">
    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid #f1f5f9;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:8px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="calendar-x" style="width:16px;height:16px;color:#4338ca;"></i>
            </div>
            <div>
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Record Absence</h3>
                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0;">Select member, period and days missed</p>
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
    <form id="addAbsenceForm" style="padding:20px 24px 24px;">

        <!-- Member — admin sees full dropdown, regular user sees their own name locked -->
        <div style="margin-bottom:16px;">
            <label class="ss-label" for="ab-user">Member <span style="color:#ef4444;">*</span></label>

            <?php if (session()->get('role') === 'admin'): ?>

            <!-- Admin: full user dropdown -->
            <div style="position:relative;">
                <i data-lucide="user" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                <select id="ab-user" name="user_id" required
                    class="ss-input" style="padding-left:38px;cursor:pointer;appearance:none;-webkit-appearance:none;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    <option value="">— Select member —</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user->id ?>"><?= esc($user->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <i data-lucide="chevron-down" style="position:absolute;right:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
            </div>

            <?php else: ?>

            <!-- Regular user: locked to their own account -->
            <input type="hidden" id="ab-user" name="user_id" value="<?= session()->get('user_id') ?>">
            <div style="
                display:flex;align-items:center;gap:10px;
                padding:11px 14px;border:1px solid #e2e8f0;border-radius:8px;
                background:#f8fafc;min-height:44px;
            ">
                <?php
                    // Cast session values to string — session()->get() returns mixed
                    $currentUserName = esc((string) (session()->get('name') ?? ''));
                    $currentUserId   = (int) (session()->get('user_id') ?? 0);
                    // Generate avatar colour deterministically
                    $avatarColors = [
                        ['#ede9fe','#7c3aed'],['#fce7f3','#be185d'],['#dcfce7','#15803d'],
                        ['#fef9c3','#a16207'],['#dbeafe','#1d4ed8'],['#fee2e2','#dc2626'],
                        ['#e0e7ff','#4338ca'],['#f0fdf4','#166534'],
                    ];
                    $charSum = array_sum(array_map('ord', str_split($currentUserName ?: 'U')));
                    [$avBg, $avFg] = $avatarColors[$charSum % count($avatarColors)];
                    $nameParts = explode(' ', trim($currentUserName));
                    $initials  = strtoupper(
                        count($nameParts) >= 2
                            ? $nameParts[0][0] . end($nameParts)[0]
                            : substr($currentUserName ?: 'U', 0, 2)
                    );
                ?>
                <div style="
                    width:28px;height:28px;border-radius:50%;
                    background:<?= $avBg ?>;color:<?= $avFg ?>;
                    display:flex;align-items:center;justify-content:center;
                    font-size:11px;font-weight:700;flex-shrink:0;
                "><?= $initials ?></div>
                <span style="font-size:14px;font-weight:500;color:#334155;flex:1;"><?= $currentUserName ?></span>
                <span style="
                    font-size:11px;font-weight:600;
                    padding:2px 8px;border-radius:999px;
                    background:#e0e7ff;color:#4338ca;
                ">You</span>
            </div>

            <?php endif; ?>

        <!-- Chapati Period -->
        <div style="margin-bottom:16px;">
            <label class="ss-label" for="ab-period">Chapati Period <span style="color:#ef4444;">*</span></label>
            <div style="position:relative;">
                <i data-lucide="utensils" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                <select id="ab-period" name="chapati_expense_id" required
                    class="ss-input" style="padding-left:38px;cursor:pointer;appearance:none;-webkit-appearance:none;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                    onchange="updateMaxDays(this)">
                    <option value="" data-days="0">— Select period —</option>
                    <?php foreach ($chapatiExpenses as $expense): ?>
                        <?php
                            // Extract date strings safely from datetime objects or strings
                            $from = is_object($expense->from_date)
                                ? substr($expense->from_date->toDateString(), 0, 10)
                                : substr((string) $expense->from_date, 0, 10);
                            $to = is_object($expense->to_date)
                                ? substr($expense->to_date->toDateString(), 0, 10)
                                : substr((string) $expense->to_date, 0, 10);

                            // Calculate total days for this period
                            $totalDays = (int) floor(
                                (strtotime($to) - strtotime($from)) / 86400
                            ) + 1;
                        ?>
                        <option value="<?= $expense->id ?>"
                                data-from="<?= $from ?>"
                                data-to="<?= $to ?>"
                                data-days="<?= $totalDays ?>">
                            <?= $from ?> → <?= $to ?>
                            (<?= $totalDays ?> day<?= $totalDays !== 1 ? 's' : '' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <i data-lucide="chevron-down" style="position:absolute;right:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
            </div>
        </div>

        <!-- Period total days hint -->
        <div id="period-hint" style="display:none;margin-bottom:16px;">
            <div style="
                display:flex;align-items:center;gap:8px;
                padding:10px 14px;background:#f0fdf4;
                border:1px solid #bbf7d0;border-radius:8px;
            ">
                <i data-lucide="calendar-days" style="width:15px;height:15px;color:#15803d;flex-shrink:0;"></i>
                <span style="font-size:13px;color:#15803d;font-weight:500;">
                    This period has <strong id="period-total-days">0</strong> total days.
                    Absent days must be between 1 and <strong id="period-max-days">0</strong>.
                </span>
            </div>
        </div>

        <!-- Days Absent -->
        <div style="margin-bottom:24px;">
            <label class="ss-label" for="ab-days">Days Absent <span style="color:#ef4444;">*</span></label>
            <div style="position:relative;">
                <i data-lucide="calendar-x" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                <input type="number" id="ab-days" name="days_absent"
                    placeholder="e.g. 5"
                    min="1" step="1" required
                    class="ss-input" style="padding-left:38px;font-family:'JetBrains Mono',monospace;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                    oninput="validateDays()">
            </div>
            <p id="days-error" style="display:none;font-size:12px;color:#ef4444;margin-top:6px;">
                Days absent cannot exceed the total days in the selected period.
            </p>
        </div>

        <!-- Actions -->
        <div style="display:flex;gap:10px;">
            <button type="button" onclick="closeAddModal()" class="ss-btn ss-btn-ghost" style="flex:1;">
                Cancel
            </button>
            <button type="submit" id="addAbsenceBtn" class="ss-btn ss-btn-primary" style="flex:2;">
                <i data-lucide="plus" style="width:15px;height:15px;" id="addAbsenceBtnIcon"></i>
                <span id="addAbsenceBtnText">Save Absence</span>
            </button>
        </div>

    </form>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
lucide.createIcons();

// ── Avatar helpers (reused from users page) ──────────────────────
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

// ── Chapati period lookup map (id → label) built from select options
const periodMap = {};
document.querySelectorAll('#ab-period option[value]').forEach(function(opt) {
    if (opt.value) {
        periodMap[opt.value] = opt.textContent.trim();
    }
});

// ── Load & render absences ───────────────────────────────────────
function loadAbsences() {
    $.get('/chapatiabsence/getAbsences', function(res) {
        const records = res.data || [];
        document.getElementById('absence-count').textContent = records.length;
        const tbody = document.getElementById('absence-tbody');

        if (records.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" style="padding:48px 16px;text-align:center;color:#cbd5e1;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i data-lucide="calendar-x" style="width:24px;height:24px;color:#e2e8f0;"></i>
                            <span style="font-size:14px;">No absence records yet</span>
                        </div>
                    </td>
                </tr>`;
            lucide.createIcons();
            return;
        }

        tbody.innerHTML = records.map(function(r) {
            const [bg, fg] = avatarColor(r.user_name);

            // Days absent severity colouring
            const days      = parseInt(r.days_absent) || 0;
            const daysBg    = days >= 10 ? '#fee2e2' : days >= 5 ? '#fef9c3' : '#dcfce7';
            const daysFg    = days >= 10 ? '#dc2626' : days >= 5 ? '#a16207' : '#15803d';

            // Period label from map, fallback to ID
            const periodLabel = periodMap[r.chapati_expense_id]
                || ('Period #' + r.chapati_expense_id);

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
                        ">${initials(r.user_name)}</div>
                        <span style="font-size:14px;font-weight:600;color:#0f172a;">
                            ${r.user_name || '—'}
                        </span>
                    </div>
                </td>

                <!-- Chapati Period -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;">
                    <span style="
                        display:inline-flex;align-items:center;gap:6px;
                        padding:4px 10px;border-radius:999px;
                        font-size:12px;font-weight:600;
                        background:#fef9c3;color:#a16207;
                    ">
                        <i data-lucide="utensils" style="width:11px;height:11px;"></i>
                        ${periodLabel}
                    </span>
                </td>

                <!-- Days Absent -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="
                        display:inline-flex;align-items:center;gap:5px;
                        padding:4px 12px;border-radius:999px;
                        font-size:13px;font-weight:700;
                        background:${daysBg};color:${daysFg};
                        font-family:'JetBrains Mono',monospace;
                    ">
                        ${days} day${days !== 1 ? 's' : ''}
                    </span>
                </td>

                <!-- Delete -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                    <button class="deleteAbsenceBtn"
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
        document.querySelectorAll('.deleteAbsenceBtn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (!confirm('Delete this absence record?')) return;
                $.ajax({
                    url:  '/chapatiabsence/deleteAbsence/' + id,
                    type: 'DELETE',
                    success: function() {
                        ssToast('Absence record deleted.', 'success');
                        loadAbsences();
                    },
                    error: function() {
                        ssToast('Failed to delete absence record.', 'error');
                    }
                });
            });
        });
    });
}
loadAbsences();


// ── Period selection → update max days hint ──────────────────────
let maxDaysForPeriod = 0;

function updateMaxDays(select) {
    const opt  = select.options[select.selectedIndex];
    const days = parseInt(opt.dataset.days) || 0;
    maxDaysForPeriod = days;

    const hint = document.getElementById('period-hint');
    if (days > 0) {
        document.getElementById('period-total-days').textContent = days;
        document.getElementById('period-max-days').textContent   = days;
        hint.style.display = 'block';
        // Update input max attribute
        document.getElementById('ab-days').setAttribute('max', days);
    } else {
        hint.style.display = 'none';
        document.getElementById('ab-days').removeAttribute('max');
    }
    validateDays();
}

function validateDays() {
    const val      = parseInt(document.getElementById('ab-days').value) || 0;
    const errEl    = document.getElementById('days-error');
    const daysInput = document.getElementById('ab-days');
    if (maxDaysForPeriod > 0 && val > maxDaysForPeriod) {
        errEl.style.display       = 'block';
        daysInput.style.borderColor = '#ef4444';
    } else {
        errEl.style.display       = 'none';
        daysInput.style.borderColor = '#e2e8f0';
    }
}


// ── Modal open / close ───────────────────────────────────────────
function openAddModal() {
    const backdrop = document.getElementById('modal-backdrop');
    const modal    = document.getElementById('add-absence-modal');
    backdrop.style.display = 'block';
    modal.style.display    = 'block';
    requestAnimationFrame(function() {
        modal.style.opacity   = '1';
        modal.style.transform = 'translate(-50%,-50%) scale(1)';
    });
    // Focus period select for non-admins (user field is a hidden input for them)
    const firstFocus = document.getElementById('ab-period') || document.getElementById('ab-user');
    if (firstFocus) firstFocus.focus();
}
function closeAddModal() {
    const modal    = document.getElementById('add-absence-modal');
    const backdrop = document.getElementById('modal-backdrop');
    modal.style.opacity   = '0';
    modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
    setTimeout(function() {
        modal.style.display    = 'none';
        backdrop.style.display = 'none';
        document.getElementById('addAbsenceForm').reset();
        document.getElementById('period-hint').style.display = 'none';
        document.getElementById('days-error').style.display  = 'none';
        maxDaysForPeriod = 0;
        resetAddBtn();
    }, 180);
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAddModal();
});


// ── Submit button helpers ────────────────────────────────────────
function setAddBtnLoading() {
    const btn  = document.getElementById('addAbsenceBtn');
    const text = document.getElementById('addAbsenceBtnText');
    const icon = document.getElementById('addAbsenceBtnIcon');
    btn.disabled      = true;
    btn.style.opacity = '0.75';
    text.textContent  = 'Saving…';
    icon.setAttribute('data-lucide', 'loader');
    lucide.createIcons();
}
function resetAddBtn() {
    const btn  = document.getElementById('addAbsenceBtn');
    const text = document.getElementById('addAbsenceBtnText');
    const icon = document.getElementById('addAbsenceBtnIcon');
    if (!btn) return;
    btn.disabled      = false;
    btn.style.opacity = '1';
    text.textContent  = 'Save Absence';
    icon.setAttribute('data-lucide', 'plus');
    lucide.createIcons();
}


// ── Form submit ──────────────────────────────────────────────────
document.getElementById('addAbsenceForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate days don't exceed period length
    const days = parseInt(document.getElementById('ab-days').value) || 0;
    if (maxDaysForPeriod > 0 && days > maxDaysForPeriod) {
        document.getElementById('days-error').style.display = 'block';
        return;
    }

    setAddBtnLoading();

    $.post('/chapatiabsence/addAbsence', $(this).serialize(), function(res) {
        if (res.status === 'success') {
            ssToast('Absence recorded successfully!', 'success');
            closeAddModal();
            loadAbsences();
        } else {
            ssToast('Failed to save absence.', 'error');
            resetAddBtn();
        }
    }, 'json').fail(function() {
        ssToast('Something went wrong.', 'error');
        resetAddBtn();
    });
});
</script>
<?= $this->endSection() ?>
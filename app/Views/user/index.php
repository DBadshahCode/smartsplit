<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Users<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Page header ─────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">Users</h1>
        <p class="page-subtitle">Manage roommates and their access roles</p>
    </div>
    <?php if (session()->get('role') === 'admin'): ?>
    <button onclick="openAddModal()" class="ss-btn ss-btn-primary">
        <i data-lucide="user-plus" style="width:16px;height:16px;"></i>
        Add User
    </button>
    <?php endif; ?>
</div>

<!-- ── Users table card ────────────────────────────────────────── -->
<div class="ss-card">
    <div class="ss-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">All Users</h2>
            <p style="font-size:13px;color:#94a3b8;margin:3px 0 0;">
                <span id="user-count">—</span> registered members
            </p>
        </div>
    </div>

    <!-- Table wrapped for horizontal scroll on mobile -->
    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table id="usersTable" style="width:100%;border-collapse:collapse;min-width:500px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Member</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Email</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Role</th>
                    <?php if (session()->get('role') === 'admin'): ?>
                    <th style="padding:11px 16px;text-align:right;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase;border-bottom:1px solid #f1f5f9;white-space:nowrap;">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="users-tbody">
                <tr>
                    <td colspan="4" style="padding:48px 16px;text-align:center;color:#cbd5e1;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i data-lucide="loader" style="width:20px;height:20px;color:#cbd5e1;"></i>
                            <span style="font-size:14px;">Loading users…</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
     ADD USER MODAL  (admin only)
════════════════════════════════════════════════════════════════ -->
<?php if (session()->get('role') === 'admin'): ?>

<!-- Backdrop -->
<div id="modal-backdrop" onclick="closeAddModal()" style="
    display:none;
    position:fixed;inset:0;
    background:rgba(15,23,42,.45);
    z-index:100;
    backdrop-filter:blur(2px);
    -webkit-backdrop-filter:blur(2px);
"></div>

<!-- Modal panel -->
<div id="add-user-modal" style="
    display:none;
    position:fixed;
    top:50%;left:50%;
    transform:translate(-50%,-50%) scale(0.97);
    width:calc(100% - 32px);
    max-width:460px;
    background:#fff;
    border-radius:16px;
    box-shadow:0 20px 60px rgba(0,0,0,.15);
    z-index:101;
    transition:transform .2s ease, opacity .2s ease;
    opacity:0;
">
    <!-- Modal header -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid #f1f5f9;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:8px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="user-plus" style="width:16px;height:16px;color:#7c3aed;"></i>
            </div>
            <div>
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Add New User</h3>
                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0;">Fill in the details below</p>
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

    <!-- Modal body -->
    <form id="addUserForm" style="padding:20px 24px 24px;">

        <!-- Name -->
        <div style="margin-bottom:16px;">
            <label class="ss-label" for="u-name">Full Name</label>
            <div style="position:relative;">
                <i data-lucide="user" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                <input
                    type="text" id="u-name" name="name"
                    placeholder="e.g. Rahul Sharma"
                    required autocomplete="off"
                    class="ss-input" style="padding-left:38px;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                >
            </div>
        </div>

        <!-- Email -->
        <div style="margin-bottom:16px;">
            <label class="ss-label" for="u-email">Email Address</label>
            <div style="position:relative;">
                <i data-lucide="mail" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                <input
                    type="email" id="u-email" name="email"
                    placeholder="rahul@example.com"
                    required autocomplete="off"
                    class="ss-input" style="padding-left:38px;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                >
            </div>
        </div>

        <!-- Password -->
        <div style="margin-bottom:16px;">
            <label class="ss-label" for="u-password">Password</label>
            <div style="position:relative;">
                <i data-lucide="lock" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
                <input
                    type="password" id="u-password" name="password"
                    placeholder="••••••••"
                    required autocomplete="new-password"
                    class="ss-input" style="padding-left:38px;padding-right:44px;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                >
                <button type="button" onclick="toggleUserPwd()" style="
                    position:absolute;right:12px;top:50%;transform:translateY(-50%);
                    background:none;border:none;cursor:pointer;color:#94a3b8;
                    padding:4px;display:flex;align-items:center;min-width:32px;min-height:32px;
                ">
                    <i data-lucide="eye" id="u-pwd-icon" style="width:15px;height:15px;"></i>
                </button>
            </div>
        </div>

        <!-- Role -->
        <div style="margin-bottom:24px;">
            <label class="ss-label" for="u-role">Role</label>
            <div style="position:relative;">
                <i data-lucide="shield" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;z-index:1;"></i>
                <select id="u-role" name="role" required
                    class="ss-input" style="padding-left:38px;cursor:pointer;appearance:none;-webkit-appearance:none;"
                    onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                >
                    <option value="">— Select role —</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <i data-lucide="chevron-down" style="position:absolute;right:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
            </div>
        </div>

        <!-- Actions -->
        <div style="display:flex;gap:10px;">
            <button type="button" onclick="closeAddModal()" class="ss-btn ss-btn-ghost" style="flex:1;">
                Cancel
            </button>
            <button type="submit" id="addUserBtn" class="ss-btn ss-btn-primary" style="flex:2;">
                <i data-lucide="user-plus" style="width:15px;height:15px;" id="addUserBtnIcon"></i>
                <span id="addUserBtnText">Save User</span>
            </button>
        </div>

    </form>
</div>

<?php endif; ?>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
lucide.createIcons();

// ── Avatar initials helper ───────────────────────────────────────
const AVATAR_COLORS = [
    ['#ede9fe','#7c3aed'], ['#fce7f3','#be185d'], ['#dcfce7','#15803d'],
    ['#fef9c3','#a16207'], ['#dbeafe','#1d4ed8'], ['#fee2e2','#dc2626'],
    ['#e0e7ff','#4338ca'], ['#f0fdf4','#166534'],
];
function avatarColor(name) {
    let i = 0;
    for (let c of (name || '')) i += c.charCodeAt(0);
    return AVATAR_COLORS[i % AVATAR_COLORS.length];
}
function initials(name) {
    if (!name) return '?';
    const parts = name.trim().split(' ');
    return parts.length >= 2
        ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
        : name.slice(0, 2).toUpperCase();
}

// ── Load & render users ──────────────────────────────────────────
function loadUsers() {
    $.get('/user/getUsers', function(res) {
        const users = res.data || [];
        document.getElementById('user-count').textContent = users.length;

        const isAdmin = <?= session()->get('role') === 'admin' ? 'true' : 'false' ?>;
        const tbody   = document.getElementById('users-tbody');

        if (users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${isAdmin ? 4 : 3}" style="padding:48px 16px;text-align:center;color:#cbd5e1;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i data-lucide="users" style="width:24px;height:24px;color:#e2e8f0;"></i>
                            <span style="font-size:14px;">No users found</span>
                        </div>
                    </td>
                </tr>`;
            lucide.createIcons();
            return;
        }

        tbody.innerHTML = users.map(function(u) {
            const [bg, fg] = avatarColor(u.name);
            const isAdminRole = u.role === 'admin';
            const badgeBg  = isAdminRole ? '#ede9fe' : '#f1f5f9';
            const badgeFg  = isAdminRole ? '#7c3aed' : '#475569';
            const badgeTxt = isAdminRole ? 'Admin'   : 'User';

            return `<tr style="transition:background .1s;"
                        onmouseover="this.style.background='#f8fafc'"
                        onmouseout="this.style.background=''">

                <!-- Member column: avatar + name + email stacked -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="
                            width:36px;height:36px;border-radius:50%;
                            background:${bg};color:${fg};
                            display:flex;align-items:center;justify-content:center;
                            font-size:13px;font-weight:700;flex-shrink:0;
                        ">${initials(u.name)}</div>
                        <div>
                            <div style="font-size:14px;font-weight:600;color:#0f172a;">${u.name || '—'}</div>
                        </div>
                    </div>
                </td>

                <!-- Email -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:13px;color:#64748b;">${u.email || '—'}</span>
                </td>

                <!-- Role badge -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;white-space:nowrap;">
                    <span style="
                        display:inline-flex;align-items:center;gap:5px;
                        padding:3px 10px;border-radius:999px;
                        font-size:12px;font-weight:600;
                        background:${badgeBg};color:${badgeFg};
                    ">
                        <i data-lucide="${isAdminRole ? 'shield' : 'user'}" style="width:11px;height:11px;"></i>
                        ${badgeTxt}
                    </span>
                </td>

                ${isAdmin ? `
                <!-- Actions -->
                <td style="padding:13px 16px;border-bottom:1px solid #f1f5f9;text-align:right;white-space:nowrap;">
                    <div style="display:inline-flex;align-items:center;gap:8px;">
                        <!-- Role toggle -->
                        <button
                            class="toggleRoleBtn"
                            data-id="${u.id}"
                            data-name="${u.name}"
                            data-role="${u.role}"
                            title="${isAdminRole ? 'Demote to User' : 'Promote to Admin'}"
                            style="
                                display:inline-flex;align-items:center;gap:5px;
                                padding:6px 12px;border-radius:6px;
                                background:${isAdminRole ? '#fef9c3' : '#e0e7ff'};
                                color:${isAdminRole ? '#a16207' : '#4338ca'};
                                border:none;cursor:pointer;
                                font-size:12px;font-weight:600;
                                font-family:'DM Sans',sans-serif;
                                min-height:32px;transition:background .15s;
                            "
                            onmouseover="this.style.opacity='0.8'"
                            onmouseout="this.style.opacity='1'"
                        >
                            <i data-lucide="${isAdminRole ? 'shield-off' : 'shield-check'}" style="width:12px;height:12px;"></i>
                            ${isAdminRole ? 'Demote' : 'Promote'}
                        </button>
                        <!-- Delete -->
                        <button
                            class="deleteUserBtn"
                            data-id="${u.id}"
                            data-name="${u.name}"
                            style="
                                display:inline-flex;align-items:center;gap:5px;
                                padding:6px 12px;border-radius:6px;
                                background:#fee2e2;color:#dc2626;
                                border:none;cursor:pointer;
                                font-size:12px;font-weight:600;
                                font-family:'DM Sans',sans-serif;
                                transition:background .15s;
                                min-height:32px;
                            "
                            onmouseover="this.style.background='#fecaca'"
                            onmouseout="this.style.background='#fee2e2'"
                        >
                            <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                            Delete
                        </button>
                    </div>
                </td>` : ''}
            </tr>`;
        }).join('');

        lucide.createIcons();

        // Delete handler
        document.querySelectorAll('.deleteUserBtn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id   = this.dataset.id;
                const name = this.dataset.name;
                if (!confirm(`Delete user "${name}"? This cannot be undone.`)) return;
                $.ajax({
                    url: '/user/deleteUser/' + id,
                    type: 'DELETE',
                    success: function() {
                        ssToast('User deleted successfully.', 'success');
                        loadUsers();
                    },
                    error: function() {
                        ssToast('Failed to delete user.', 'error');
                    }
                });
            });
        });

        // Role toggle handler
        document.querySelectorAll('.toggleRoleBtn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id      = this.dataset.id;
                const name    = this.dataset.name;
                const current = this.dataset.role;
                const action  = current === 'admin' ? 'Demote to User' : 'Promote to Admin';
                if (!confirm(`${action} for "${name}"?`)) return;

                // Disable button while request is in flight
                const self = this;
                self.disabled     = true;
                self.style.opacity = '0.6';

                $.ajax({
                    url:  '/user/updateRole/' + id,
                    type: 'POST',
                    success: function(res) {
                        if (res.status === 'success') {
                            ssToast(`${name} is now ${res.new_role === 'admin' ? 'an Admin' : 'a User'}.`, 'success');
                            loadUsers();
                        } else {
                            ssToast(res.message || 'Could not update role.', 'error');
                            self.disabled      = false;
                            self.style.opacity = '1';
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Something went wrong.';
                        ssToast(msg, 'error');
                        self.disabled      = false;
                        self.style.opacity = '1';
                    }
                });
            });
        });
    });
}
loadUsers();


// ── Modal open / close ───────────────────────────────────────────
function openAddModal() {
    const backdrop = document.getElementById('modal-backdrop');
    const modal    = document.getElementById('add-user-modal');
    if (!backdrop || !modal) return;
    backdrop.style.display = 'block';
    modal.style.display    = 'block';
    requestAnimationFrame(function() {
        modal.style.opacity   = '1';
        modal.style.transform = 'translate(-50%,-50%) scale(1)';
    });
    document.getElementById('u-name').focus();
}
function closeAddModal() {
    const backdrop = document.getElementById('modal-backdrop');
    const modal    = document.getElementById('add-user-modal');
    if (!backdrop || !modal) return;
    modal.style.opacity   = '0';
    modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
    setTimeout(function() {
        modal.style.display    = 'none';
        backdrop.style.display = 'none';
        document.getElementById('addUserForm').reset();
        resetAddBtn();
    }, 180);
}
// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAddModal();
});

// ── Password show/hide ───────────────────────────────────────────
function toggleUserPwd() {
    const input = document.getElementById('u-password');
    const icon  = document.getElementById('u-pwd-icon');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.setAttribute('data-lucide', isHidden ? 'eye-off' : 'eye');
    lucide.createIcons();
}

// ── Submit button loading state helpers ──────────────────────────
function setAddBtnLoading() {
    const btn  = document.getElementById('addUserBtn');
    const text = document.getElementById('addUserBtnText');
    const icon = document.getElementById('addUserBtnIcon');
    btn.disabled          = true;
    btn.style.opacity     = '0.75';
    text.textContent      = 'Saving…';
    icon.setAttribute('data-lucide', 'loader');
    lucide.createIcons();
}
function resetAddBtn() {
    const btn  = document.getElementById('addUserBtn');
    const text = document.getElementById('addUserBtnText');
    const icon = document.getElementById('addUserBtnIcon');
    if (!btn) return;
    btn.disabled      = false;
    btn.style.opacity = '1';
    text.textContent  = 'Save User';
    icon.setAttribute('data-lucide', 'user-plus');
    lucide.createIcons();
}

// ── Add user form submit ─────────────────────────────────────────
const addForm = document.getElementById('addUserForm');
if (addForm) {
    addForm.addEventListener('submit', function(e) {
        e.preventDefault();
        setAddBtnLoading();

        $.ajax({
            url:  '/user/addUser',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success') {
                    ssToast('User added successfully!', 'success');
                    closeAddModal();
                    loadUsers();
                } else {
                    ssToast(res.message || 'Could not add user.', 'error');
                    resetAddBtn();
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Something went wrong.';
                ssToast(msg, 'error');
                resetAddBtn();
            }
        });
    });
}
</script>
<?= $this->endSection() ?>
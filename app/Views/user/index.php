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
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Add User
        </button>
    <?php endif; ?>
</div>

<!-- ── Users table card ────────────────────────────────────────── -->
<div class="ss-card">
    <div class="ss-card-header flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-[15px] font-bold text-surface-900 m-0">All Users</h2>
            <p class="text-[13px] text-surface-400 mt-[3px] mb-0">
                <span id="user-count">—</span> registered members
            </p>
        </div>
    </div>

    <div class="ss-table-wrap" style="border:none;border-radius:0;">
        <table id="usersTable" class="w-full border-collapse" style="min-width:500px;">
            <thead>
                <tr class="bg-surface-50">
                    <th class="abs-th text-left">Member</th>
                    <th class="abs-th text-left">Email</th>
                    <th class="abs-th text-left">Role</th>
                    <?php if (session()->get('role') === 'admin'): ?>
                        <th class="abs-th text-right">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="users-tbody">
                <tr>
                    <td colspan="4" class="mini-table-empty-td">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="loader" class="w-5 h-5 text-surface-200"></i>
                            <span class="text-sm">Loading users…</span>
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

    <div id="modal-backdrop" onclick="closeAddModal()" class="modal-backdrop" style="z-index:100;"></div>

    <div id="add-user-modal" class="modal-panel modal-shell" style="z-index:101;max-width:460px;">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-header-icon bg-violet-100">
                    <i data-lucide="user-plus" class="w-4 h-4 text-violet-600"></i>
                </div>
                <div>
                    <h3 class="modal-header-title">Add New User</h3>
                    <p class="modal-header-subtitle">Fill in the details below</p>
                </div>
            </div>
            <button onclick="closeAddModal()" class="icon-btn icon-btn-muted icon-btn-sm">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <form id="addUserForm" class="modal-form">

                <!-- Name -->
                <div class="field-group">
                    <label class="ss-label" for="u-name">Full Name</label>
                    <div class="field-icon-wrap">
                        <i data-lucide="user" class="field-icon"></i>
                        <input type="text" id="u-name" name="name" placeholder="e.g. Rahul Sharma" required
                            autocomplete="off" class="ss-input pl-[38px]">
                    </div>
                </div>

                <!-- Email -->
                <div class="field-group">
                    <label class="ss-label" for="u-email">Email Address</label>
                    <div class="field-icon-wrap">
                        <i data-lucide="mail" class="field-icon"></i>
                        <input type="email" id="u-email" name="email" placeholder="rahul@example.com" required
                            autocomplete="off" class="ss-input pl-[38px]">
                    </div>
                </div>

                <!-- Password -->
                <div class="field-group">
                    <label class="ss-label" for="u-password">Password</label>
                    <div class="field-icon-wrap">
                        <i data-lucide="lock" class="field-icon"></i>
                        <input type="password" id="u-password" name="password" placeholder="••••••••" required
                            autocomplete="new-password" class="ss-input pl-[38px] pr-11">
                        <button type="button" onclick="toggleUserPwd()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 bg-transparent border-none cursor-pointer text-surface-400 p-1 flex items-center justify-center min-w-[32px] min-h-[32px]">
                            <i data-lucide="eye" id="u-pwd-icon" class="w-[15px] h-[15px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Role -->
                <div class="field-group-lg">
                    <label class="ss-label" for="u-role">Role</label>
                    <div class="field-icon-wrap">
                        <i data-lucide="shield" class="field-icon"></i>
                        <select id="u-role" name="role" required class="ss-input ss-input-icon">
                            <option value="">— Select role —</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        <i data-lucide="chevron-down" class="field-icon-trail"></i>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2.5">
                    <button type="button" onclick="closeAddModal()" class="ss-btn ss-btn-ghost flex-1">Cancel</button>
                    <button type="submit" id="addUserBtn" class="ss-btn ss-btn-primary flex-[2]">
                        <i data-lucide="user-plus" class="w-[15px] h-[15px]" id="addUserBtnIcon"></i>
                        <span id="addUserBtnText">Save User</span>
                    </button>
                </div>

            </form>
        </div>
    </div>

<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     RESET PASSWORD MODAL  (admin only)
════════════════════════════════════════════════════════════════ -->
<?php if (session()->get('role') === 'admin'): ?>

    <div id="reset-backdrop" onclick="closeResetModal()" class="modal-backdrop" style="z-index:100;"></div>

    <div id="reset-pwd-modal" class="modal-panel modal-shell" style="z-index:101;max-width:420px;">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-header-icon bg-yellow-100">
                    <i data-lucide="key-round" class="w-4 h-4 text-yellow-700"></i>
                </div>
                <div>
                    <h3 class="modal-header-title">Reset Password</h3>
                    <p id="reset-modal-subtitle" class="modal-header-subtitle">Set a new password for this user</p>
                </div>
            </div>
            <button onclick="closeResetModal()" class="icon-btn icon-btn-muted icon-btn-sm">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <form id="resetPwdForm" class="modal-form">
                <input type="hidden" id="reset-user-id" value="">

                <!-- Info strip -->
                <div class="hint-box hint-warn" style="align-items:flex-start;margin-bottom:16px;">
                    <i data-lucide="info" class="w-[14px] h-[14px] flex-shrink-0 mt-px"></i>
                    <p class="m-0" style="line-height:1.5;">
                        The user will need to use this new password on their next login.
                        Share it with them securely.
                    </p>
                </div>

                <!-- New password -->
                <div class="field-group">
                    <label class="ss-label" for="reset-pwd">New Password <span class="required-star">*</span></label>
                    <div class="field-icon-wrap">
                        <i data-lucide="lock" class="field-icon"></i>
                        <input type="password" id="reset-pwd" name="password" placeholder="Min. 6 characters" required
                            autocomplete="new-password" class="ss-input pl-[38px] pr-11" oninput="validateResetPwd()">
                        <button type="button" onclick="toggleResetPwd()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 bg-transparent border-none cursor-pointer text-surface-400 p-1 flex items-center justify-center min-w-[32px] min-h-[32px]">
                            <i data-lucide="eye" id="reset-pwd-icon" class="w-[15px] h-[15px]"></i>
                        </button>
                    </div>
                    <p id="reset-pwd-error" class="field-hint hidden">Password must be at least 6 characters.</p>
                </div>

                <!-- Confirm password -->
                <div class="field-group-lg">
                    <label class="ss-label" for="reset-pwd-confirm">Confirm Password <span class="required-star">*</span></label>
                    <div class="field-icon-wrap">
                        <i data-lucide="lock" class="field-icon"></i>
                        <input type="password" id="reset-pwd-confirm" placeholder="Re-enter password" required
                            autocomplete="new-password" class="ss-input pl-[38px]" oninput="validateResetPwd()">
                    </div>
                    <p id="reset-match-error" class="field-hint hidden">Passwords do not match.</p>
                </div>

                <!-- Actions -->
                <div class="flex gap-2.5">
                    <button type="button" onclick="closeResetModal()" class="ss-btn ss-btn-ghost flex-1">Cancel</button>
                    <button type="submit" id="resetPwdSubmitBtn" class="ss-btn ss-btn-primary flex-[2]">
                        <i data-lucide="key-round" class="w-[15px] h-[15px]" id="resetPwdSubmitIcon"></i>
                        <span id="resetPwdSubmitText">Reset Password</span>
                    </button>
                </div>

            </form>
        </div>
    </div>

<?php endif; ?>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    lucide.createIcons();

    // ── Avatar initials helper ───────────────────────────────────────
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

    function avatarColor(name) {
        let i = 0;
        for (let c of (name || '')) i += c.charCodeAt(0);
        return AVATAR_COLORS[i % AVATAR_COLORS.length];
    }

    function initials(name) {
        if (!name) return '?';
        const parts = name.trim().split(' ');
        return parts.length >= 2 ?
            (parts[0][0] + parts[parts.length - 1][0]).toUpperCase() :
            name.slice(0, 2).toUpperCase();
    }

    // ══════════════════════════════════════════════════════════════
    // loadUsers() — row template. Role badge reuses .ss-badge +
    // Tailwind color utilities (bg-violet-100/text-violet-600 for
    // Admin, bg-surface-100/text-surface-600 for User — both exact
    // hex matches to the originals). Action buttons reuse
    // .row-action-btn plus a color modifier each. Row hover's
    // onmouseover/onmouseout is gone — #users-tbody's wrapper already
    // has `.ss-table-wrap`, which already carries the app-wide
    // `tbody tr:hover` rule.
    // ══════════════════════════════════════════════════════════════
    function loadUsers() {
        $.get('/user/getUsers', function(res) {
            const users = res.data || [];
            document.getElementById('user-count').textContent = users.length;

            const isAdmin = <?= session()->get('role') === 'admin' ? 'true' : 'false' ?>;
            const tbody = document.getElementById('users-tbody');

            if (users.length === 0) {
                tbody.innerHTML = `
            <tr>
                <td colspan="${isAdmin ? 4 : 3}" class="mini-table-empty-td">
                    <div class="flex flex-col items-center gap-2">
                        <i data-lucide="users" class="w-6 h-6 text-surface-200"></i>
                        <span class="text-sm">No users found</span>
                    </div>
                </td>
            </tr>`;
                lucide.createIcons();
                return;
            }

            tbody.innerHTML = users.map(function(u) {
                const [bg, fg] = avatarColor(u.name);
                const isAdminRole = u.role === 'admin';
                const roleBadgeClass = isAdminRole ? 'bg-violet-100 text-violet-600' : 'bg-surface-100 text-surface-600';
                const badgeTxt = isAdminRole ? 'Admin' : 'User';

                return `<tr style="border-bottom:1px solid #f1f5f9;">

            <td class="abs-td" style="white-space:nowrap;">
                <div class="flex items-center gap-2.5">
                    <div class="avatar-circle" style="width:36px;height:36px;font-size:13px;background:${bg};color:${fg};">
                        ${initials(u.name)}
                    </div>
                    <div class="text-sm font-semibold text-surface-900">${u.name || '—'}</div>
                </div>
            </td>

            <td class="abs-td">
                <span class="text-[13px] text-surface-500">${u.email || '—'}</span>
            </td>

            <td class="abs-td" style="white-space:nowrap;">
                <span class="ss-badge ${roleBadgeClass}" style="gap:5px;">
                    <i data-lucide="${isAdminRole ? 'shield' : 'user'}" class="w-[11px] h-[11px]"></i>
                    ${badgeTxt}
                </span>
            </td>

            ${isAdmin ? `
            <td class="abs-td text-right" style="white-space:nowrap;">
                <div class="action-menu-wrap">
                    <button type="button" class="action-menu-trigger" aria-label="Actions">
                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                    </button>
                    <div class="action-menu-dropdown">
                        <button class="action-menu-item toggleRoleBtn ${isAdminRole ? 'action-menu-warn' : ''}"
                            data-id="${u.id}" data-name="${u.name}" data-role="${u.role}">
                            <i data-lucide="${isAdminRole ? 'shield-off' : 'shield-check'}" class="w-3.5 h-3.5"></i>
                            ${isAdminRole ? 'Demote to User' : 'Promote to Admin'}
                        </button>
                        <button class="action-menu-item resetPwdBtn" data-id="${u.id}" data-name="${u.name}">
                            <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                            Reset Password
                        </button>
                        <div class="action-menu-divider"></div>
                        <button class="action-menu-item deleteUserBtn action-menu-danger" data-id="${u.id}" data-name="${u.name}">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            Delete User
                        </button>
                    </div>
                </div>
            </td>` : ''}
        </tr>`;
            }).join('');

            lucide.createIcons();
            initActionMenus(tbody);

            // ── Delete handler — the two console.log() debug lines that
            // were here (id + jQuery-availability checks) are removed.
            document.querySelectorAll('.deleteUserBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    ssConfirm({
                        title: 'Delete User',
                        message: 'Are you sure you want to delete user "' + name + '"? This cannot be undone.',
                        confirmText: 'Delete',
                        onConfirm: function() {
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
                        }
                    });
                });
            });

            // ── Role toggle handler ──────────────────────────────────
            document.querySelectorAll('.toggleRoleBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const current = this.dataset.role;
                    const action = current === 'admin' ? 'Demote to User' : 'Promote to Admin';
                    ssConfirm({
                        title: 'Update User Role',
                        message: 'Are you sure you want to ' + action.toLowerCase() + ' "' + name + '"?',
                        confirmText: action,
                        onConfirm: function() {
                            $.ajax({
                                url: '/user/updateRole/' + id,
                                type: 'POST',
                                success: function(res) {
                                    if (res.status === 'success') {
                                        ssToast(name + ' is now ' + (res.new_role === 'admin' ? 'an Admin' : 'a User') + '.', 'success');
                                        loadUsers();
                                    } else {
                                        ssToast(res.message || 'Could not update role.', 'error');
                                    }
                                },
                                error: function(xhr) {
                                    ssToast((xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.', 'error');
                                }
                            });
                        }
                    });
                });
            });

            // ── Reset password handler ───────────────────────────────
            document.querySelectorAll('.resetPwdBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openResetModal(this.dataset.id, this.dataset.name);
                });
            });

        }); // end $.get
    } // end loadUsers

    loadUsers();


    // ══════════════════════════════════════════════════════════════
    // Add user modal — display:'block' → 'flex' (now uses .modal-shell,
    // same fix as expensetype/expense/absentday).
    // ══════════════════════════════════════════════════════════════
    function openAddModal() {
        const backdrop = document.getElementById('modal-backdrop');
        const modal = document.getElementById('add-user-modal');
        if (!backdrop || !modal) return;
        backdrop.style.display = 'block';
        modal.style.display = 'flex';
        requestAnimationFrame(function() {
            modal.style.opacity = '1';
            modal.style.transform = 'translate(-50%,-50%) scale(1)';
        });
        document.getElementById('u-name').focus();
    }

    function closeAddModal() {
        const backdrop = document.getElementById('modal-backdrop');
        const modal = document.getElementById('add-user-modal');
        if (!backdrop || !modal) return;
        modal.style.opacity = '0';
        modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
        setTimeout(function() {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
            document.getElementById('addUserForm').reset();
            resetAddBtn();
        }, 180);
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeResetModal();
        }
    });


    // ══════════════════════════════════════════════════════════════
    // FIX: all four icon-swap functions below were using
    // icon.setAttribute('data-lucide', ...) + lucide.createIcons()
    // directly — the same known-broken pattern as expensetype's and
    // login's button icons. None of these icon swaps were actually
    // rendering. All four fixed to use window.setLucideIcon().
    // ══════════════════════════════════════════════════════════════
    function toggleUserPwd() {
        const input = document.getElementById('u-password');
        input.type = input.type === 'password' ? 'text' : 'password';
        window.setLucideIcon('u-pwd-icon', input.type === 'password' ? 'eye' : 'eye-off');
    }

    function setAddBtnLoading() {
        const btn = document.getElementById('addUserBtn');
        const text = document.getElementById('addUserBtnText');
        btn.disabled = true;
        btn.style.opacity = '0.75';
        text.textContent = 'Saving…';
        window.setLucideIcon('addUserBtnIcon', 'loader');
    }

    function resetAddBtn() {
        const btn = document.getElementById('addUserBtn');
        if (!btn) return;
        const text = document.getElementById('addUserBtnText');
        btn.disabled = false;
        btn.style.opacity = '1';
        text.textContent = 'Save User';
        window.setLucideIcon('addUserBtnIcon', 'user-plus');
    }

    const addForm = document.getElementById('addUserForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            setAddBtnLoading();
            $.ajax({
                url: '/user/addUser',
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
                    ssToast(xhr.responseJSON?.message || 'Something went wrong.', 'error');
                    resetAddBtn();
                }
            });
        });
    }


    // ── Reset password modal ─────────────────────────────────────────
    let resetUserId = null;

    function openResetModal(id, name) {
        resetUserId = id;
        document.getElementById('reset-user-id').value = id;
        document.getElementById('reset-modal-subtitle').textContent = 'Set a new password for ' + name;
        document.getElementById('reset-pwd').value = '';
        document.getElementById('reset-pwd-confirm').value = '';
        document.getElementById('reset-pwd-error').classList.add('hidden');
        document.getElementById('reset-match-error').classList.add('hidden');
        document.getElementById('reset-pwd').style.borderColor = '#e2e8f0';
        document.getElementById('reset-pwd-confirm').style.borderColor = '#e2e8f0';
        const backdrop = document.getElementById('reset-backdrop');
        const modal = document.getElementById('reset-pwd-modal');
        if (!backdrop || !modal) return;
        backdrop.style.display = 'block';
        modal.style.display = 'flex'; // was 'block' — same .modal-shell fix
        requestAnimationFrame(function() {
            modal.style.opacity = '1';
            modal.style.transform = 'translate(-50%,-50%) scale(1)';
        });
        document.getElementById('reset-pwd').focus();
    }

    function closeResetModal() {
        const modal = document.getElementById('reset-pwd-modal');
        const backdrop = document.getElementById('reset-backdrop');
        if (!modal || !backdrop) return;
        modal.style.opacity = '0';
        modal.style.transform = 'translate(-50%,-50%) scale(0.97)';
        setTimeout(function() {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
            resetAddResetBtn();
            resetUserId = null;
        }, 180);
    }

    function toggleResetPwd() {
        const input = document.getElementById('reset-pwd');
        input.type = input.type === 'password' ? 'text' : 'password';
        window.setLucideIcon('reset-pwd-icon', input.type === 'password' ? 'eye' : 'eye-off');
    }

    function validateResetPwd() {
        const pwd = document.getElementById('reset-pwd').value;
        const confirm = document.getElementById('reset-pwd-confirm').value;
        const lenErr = document.getElementById('reset-pwd-error');
        const matchErr = document.getElementById('reset-match-error');
        const pwdInput = document.getElementById('reset-pwd');
        const confInput = document.getElementById('reset-pwd-confirm');

        if (pwd.length > 0 && pwd.length < 6) {
            lenErr.classList.remove('hidden');
            pwdInput.style.borderColor = '#ef4444';
        } else {
            lenErr.classList.add('hidden');
            pwdInput.style.borderColor = '#e2e8f0';
        }
        if (confirm.length > 0 && pwd !== confirm) {
            matchErr.classList.remove('hidden');
            confInput.style.borderColor = '#ef4444';
        } else {
            matchErr.classList.add('hidden');
            confInput.style.borderColor = '#e2e8f0';
        }
    }

    function setResetBtnLoading() {
        const btn = document.getElementById('resetPwdSubmitBtn');
        if (!btn) return;
        btn.disabled = true;
        btn.style.opacity = '0.75';
        document.getElementById('resetPwdSubmitText').textContent = 'Resetting…';
        window.setLucideIcon('resetPwdSubmitIcon', 'loader');
    }

    function resetAddResetBtn() {
        const btn = document.getElementById('resetPwdSubmitBtn');
        if (!btn) return;
        btn.disabled = false;
        btn.style.opacity = '1';
        document.getElementById('resetPwdSubmitText').textContent = 'Reset Password';
        window.setLucideIcon('resetPwdSubmitIcon', 'key-round');
    }

    const resetForm = document.getElementById('resetPwdForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const pwd = document.getElementById('reset-pwd').value;
            const confirm = document.getElementById('reset-pwd-confirm').value;
            if (pwd.length < 6) {
                document.getElementById('reset-pwd-error').classList.remove('hidden');
                return;
            }
            if (pwd !== confirm) {
                document.getElementById('reset-match-error').classList.remove('hidden');
                return;
            }
            setResetBtnLoading();
            $.ajax({
                url: '/user/resetPassword/' + resetUserId,
                type: 'POST',
                data: {
                    password: pwd
                },
                success: function(res) {
                    if (res.status === 'success') {
                        ssToast('Password reset successfully.', 'success');
                        closeResetModal();
                    } else {
                        ssToast(res.message || 'Failed to reset password.', 'error');
                        resetAddResetBtn();
                    }
                },
                error: function(xhr) {
                    ssToast(xhr.responseJSON?.message || 'Something went wrong.', 'error');
                    resetAddResetBtn();
                }
            });
        });
    }
</script>
<?= $this->endSection() ?>
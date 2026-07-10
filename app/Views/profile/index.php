<?php $this->extend('layout/main'); ?>

<?= $this->section('title') ?>Profile<?= $this->endSection() ?>

<?php $this->section('content'); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Profile</h1>
        <p class="page-subtitle">View and update your account details</p>
    </div>
</div>

<div style="max-width:640px; display:flex; flex-direction:column; gap:1.25rem;">

    <!-- ── Monthly share card ──────────────────────────────── -->
    <div class="ss-card" id="share-card">
        <div class="ss-card-body">

            <!-- Header row: title + month picker -->
            <div
                style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div
                        style="width:32px;height:32px;border-radius:8px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="bar-chart-2" style="width:15px;height:15px;color:#4338ca;"></i>
                    </div>
                    <div>
                        <div style="font-size:15px;font-weight:700;color:#0f172a;">My Monthly Share</div>
                        <div style="font-size:12px;color:#94a3b8;margin-top:1px;">Your final amount for the selected
                            month</div>
                    </div>
                </div>

                <!-- Month picker -->
                <div style="position:relative;">
                    <i data-lucide="calendar"
                        style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:13px;height:13px;color:#94a3b8;pointer-events:none;"></i>
                    <input type="month" id="share-month-input" class="ss-input"
                        style="padding-left:32px;padding-right:12px;font-family:'JetBrains Mono',monospace;font-size:13px;min-height:36px;width:auto;"
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                        onchange="loadShareData(this.value)">
                </div>
            </div>

            <!-- Result area -->
            <div id="share-result">
                <div style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:24px 0;">
                    <i data-lucide="loader" style="width:18px;height:18px;color:#cbd5e1;"></i>
                    <span style="font-size:13px;color:#94a3b8;">Loading…</span>
                </div>
            </div>

        </div>
    </div>

    <!-- ── Account overview card ───────────────────────────── -->
    <div class="ss-card">
        <div class="ss-card-body" style="display:flex; align-items:center; gap:1.25rem;">

            <!-- Avatar -->
            <div id="profile-avatar" style="width:64px; height:64px; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;
                        font-size:1.375rem; font-weight:700; flex-shrink:0;
                        font-family:'DM Sans', sans-serif; letter-spacing:.5px;">
            </div>

            <!-- Name / email / meta -->
            <div style="min-width:0; flex:1;">
                <div style="display:flex; align-items:center; gap:.625rem; flex-wrap:wrap;">
                    <span id="overview-name" style="font-size:1.125rem; font-weight:700;
                                 color:var(--text-primary); line-height:1.2;">
                        <?= esc($user->name) ?>
                    </span>
                    <!-- Role badge -->
                    <?php if (session()->get('role') === 'admin'): ?>
                        <span style="font-size:.6875rem; font-weight:600; letter-spacing:.04em;
                                     padding:.125rem .5rem; border-radius:999px;
                                     background:#ede9fe; color:#7c3aed; text-transform:uppercase;">
                            Admin
                        </span>
                    <?php else: ?>
                        <span style="font-size:.6875rem; font-weight:600; letter-spacing:.04em;
                                     padding:.125rem .5rem; border-radius:999px;
                                     background:#f1f5f9; color:#475569; text-transform:uppercase;">
                            User
                        </span>
                    <?php endif; ?>
                </div>
                <div id="overview-email" style="font-size:.875rem; color:var(--text-secondary); margin-top:.25rem;">
                    <?= esc($user->email) ?>
                </div>
                <?php if (!empty($user->joined_date)): ?>
                    <div style="font-size:.8125rem; color:var(--text-muted);
                                margin-top:.375rem; display:flex; align-items:center; gap:.375rem;">
                        <i data-lucide="calendar" style="width:13px;height:13px;"></i>
                        Member since
                        <?= date('d M Y', strtotime((string) $user->joined_date)) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Personal information form ──────────────────────── -->
    <div class="ss-card">
        <div class="ss-card-header">
            <div style="display:flex; align-items:center; gap:.5rem;">
                <i data-lucide="user" style="width:16px;height:16px;color:var(--brand-indigo);"></i>
                <span style="font-weight:600; font-size:.9375rem;">Personal Information</span>
            </div>
        </div>
        <div class="ss-card-body" style="display:flex; flex-direction:column; gap:1rem;">

            <div>
                <label class="ss-label" for="info-name">Full Name</label>
                <input class="ss-input" type="text" id="info-name" value="<?= esc($user->name) ?>"
                    placeholder="Your name" autocomplete="name" style="font-size:16px;">
            </div>

            <div>
                <label class="ss-label" for="info-email">Email Address</label>
                <input class="ss-input" type="email" id="info-email" value="<?= esc($user->email) ?>"
                    placeholder="you@example.com" autocomplete="email" style="font-size:16px;">
            </div>

            <div style="display:flex; justify-content:flex-end; padding-top:.25rem;">
                <button class="ss-btn ss-btn-primary" id="btn-save-info" style="min-width:130px;">
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- ── Change password form ────────────────────────────── -->
    <div class="ss-card">
        <div class="ss-card-header">
            <div style="display:flex; align-items:center; gap:.5rem;">
                <i data-lucide="lock" style="width:16px;height:16px;color:var(--brand-indigo);"></i>
                <span style="font-weight:600; font-size:.9375rem;">Change Password</span>
            </div>
        </div>
        <div class="ss-card-body" style="display:flex; flex-direction:column; gap:1rem;">

            <div>
                <label class="ss-label" for="pw-current">Current Password</label>
                <div style="position:relative;">
                    <input class="ss-input" type="password" id="pw-current" placeholder="Enter current password"
                        autocomplete="current-password" style="font-size:16px; padding-right:2.75rem;">
                    <button type="button" class="pw-toggle-btn" data-target="pw-current" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                                   background:none;border:none;cursor:pointer;
                                   color:var(--text-muted);padding:0;display:flex;">
                        <i data-lucide="eye" style="width:16px;height:16px;"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="ss-label" for="pw-new">New Password</label>
                <div style="position:relative;">
                    <input class="ss-input" type="password" id="pw-new" placeholder="At least 6 characters"
                        autocomplete="new-password" style="font-size:16px; padding-right:2.75rem;">
                    <button type="button" class="pw-toggle-btn" data-target="pw-new" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                                   background:none;border:none;cursor:pointer;
                                   color:var(--text-muted);padding:0;display:flex;">
                        <i data-lucide="eye" style="width:16px;height:16px;"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="ss-label" for="pw-confirm">Confirm New Password</label>
                <div style="position:relative;">
                    <input class="ss-input" type="password" id="pw-confirm" placeholder="Repeat new password"
                        autocomplete="new-password" style="font-size:16px; padding-right:2.75rem;">
                    <button type="button" class="pw-toggle-btn" data-target="pw-confirm" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                                   background:none;border:none;cursor:pointer;
                                   color:var(--text-muted);padding:0;display:flex;">
                        <i data-lucide="eye" style="width:16px;height:16px;"></i>
                    </button>
                </div>
            </div>

            <!-- Strength indicator -->
            <div id="pw-strength-wrap" style="display:none;">
                <div style="display:flex; gap:.25rem; margin-bottom:.375rem;">
                    <div class="pw-bar" style="height:3px;flex:1;border-radius:2px;background:#e2e8f0;"></div>
                    <div class="pw-bar" style="height:3px;flex:1;border-radius:2px;background:#e2e8f0;"></div>
                    <div class="pw-bar" style="height:3px;flex:1;border-radius:2px;background:#e2e8f0;"></div>
                    <div class="pw-bar" style="height:3px;flex:1;border-radius:2px;background:#e2e8f0;"></div>
                </div>
                <span id="pw-strength-label" style="font-size:.75rem; color:var(--text-muted);"></span>
            </div>

            <div style="display:flex; justify-content:flex-end; padding-top:.25rem;">
                <button class="ss-btn ss-btn-primary" id="btn-save-password" style="min-width:150px;">
                    Update Password
                </button>
            </div>
        </div>
    </div>

</div><!-- /max-width wrapper -->

<script>
    (function () {

        /* ── Avatar ─────────────────────────────────────────── */
        const AVATAR_COLORS = [
            ['#ede9fe', '#7c3aed'], ['#fce7f3', '#be185d'], ['#dcfce7', '#15803d'],
            ['#fef9c3', '#a16207'], ['#dbeafe', '#1d4ed8'], ['#fee2e2', '#dc2626'],
            ['#e0e7ff', '#4338ca'], ['#f0fdf4', '#166534'],
        ];
        function avatarColor(name) {
            let i = 0;
            for (let c of (name || '')) i += c.charCodeAt(0);
            return AVATAR_COLORS[i % AVATAR_COLORS.length];
        }
        function initials(name) {
            if (!name) return '?';
            const p = name.trim().split(' ');
            return (p.length >= 2 ? p[0][0] + p[p.length - 1][0] : name.slice(0, 2)).toUpperCase();
        }

        function renderAvatar(name) {
            const el = document.getElementById('profile-avatar');
            if (!el) return;
            const [bg, fg] = avatarColor(name);
            el.style.background = bg;
            el.style.color = fg;
            el.textContent = initials(name);
        }

        const initialName = <?= json_encode($user->name) ?>;
        renderAvatar(initialName);

        /* ── Save personal info ──────────────────────────────── */
        document.getElementById('btn-save-info').addEventListener('click', function () {
            const btn = this;
            const name = document.getElementById('info-name').value.trim();
            const email = document.getElementById('info-email').value.trim();

            if (!name) { ssToast('Name cannot be empty.', 'error'); return; }
            if (!email) { ssToast('Email cannot be empty.', 'error'); return; }

            btn.disabled = true;
            btn.textContent = 'Saving…';

            $.ajax({
                url: '/profile/updateInfo',
                type: 'POST',
                data: { name, email },
                success: function (res) {
                    ssToast(res.message || 'Profile updated.', 'success');
                    // Reflect changes in the overview card
                    document.getElementById('overview-name').textContent = res.name || name;
                    document.getElementById('overview-email').textContent = email;
                    renderAvatar(res.name || name);
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to save changes.';
                    ssToast(msg, 'error');
                },
                complete: function () {
                    btn.disabled = false;
                    btn.textContent = 'Save Changes';
                }
            });
        });

        /* ── Password strength ───────────────────────────────── */
        const pwNew = document.getElementById('pw-new');
        const strengthWrap = document.getElementById('pw-strength-wrap');
        const strengthLabel = document.getElementById('pw-strength-label');
        const bars = document.querySelectorAll('.pw-bar');

        const STRENGTH_LEVELS = [
            { color: '#ef4444', label: 'Weak' },
            { color: '#f97316', label: 'Fair' },
            { color: '#eab308', label: 'Good' },
            { color: '#22c55e', label: 'Strong' },
        ];

        function calcStrength(pw) {
            let score = 0;
            if (pw.length >= 6) score++;
            if (pw.length >= 10) score++;
            if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
            if (/[0-9]/.test(pw)) score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;
            return Math.min(Math.floor(score / 1.25), 3); // 0-3
        }

        pwNew.addEventListener('input', function () {
            const val = this.value;
            if (!val) {
                strengthWrap.style.display = 'none';
                return;
            }
            strengthWrap.style.display = 'block';
            const level = calcStrength(val);
            bars.forEach(function (bar, idx) {
                bar.style.background = idx <= level ? STRENGTH_LEVELS[level].color : '#e2e8f0';
            });
            strengthLabel.textContent = STRENGTH_LEVELS[level].label;
            strengthLabel.style.color = STRENGTH_LEVELS[level].color;
        });

        /* ── Save password ───────────────────────────────────── */
        document.getElementById('btn-save-password').addEventListener('click', function () {
            const btn = this;
            const current = document.getElementById('pw-current').value;
            const newPw = document.getElementById('pw-new').value;
            const confirm = document.getElementById('pw-confirm').value;

            if (!current) { ssToast('Enter your current password.', 'error'); return; }
            if (!newPw) { ssToast('Enter a new password.', 'error'); return; }
            if (newPw !== confirm) { ssToast('New passwords do not match.', 'error'); return; }

            btn.disabled = true;
            btn.textContent = 'Updating…';

            $.ajax({
                url: '/profile/updatePassword',
                type: 'POST',
                data: {
                    current_password: current,
                    new_password: newPw,
                    confirm_password: confirm,
                },
                success: function (res) {
                    ssToast(res.message || 'Password updated.', 'success');
                    // Clear all password fields
                    document.getElementById('pw-current').value = '';
                    document.getElementById('pw-new').value = '';
                    document.getElementById('pw-confirm').value = '';
                    strengthWrap.style.display = 'none';
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to update password.';
                    ssToast(msg, 'error');
                },
                complete: function () {
                    btn.disabled = false;
                    btn.textContent = 'Update Password';
                }
            });
        });

        /* ── Password visibility toggles ────────────────────── */
        document.querySelectorAll('.pw-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) return;

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';

                // Swap icon
                const iconName = isHidden ? 'eye-off' : 'eye';
                const iconEl = this.querySelector('i[data-lucide], svg');
                if (iconEl) {
                    const id = 'pw-toggle-icon-' + targetId;
                    iconEl.id = id;
                    window.setLucideIcon(id, iconName);
                }
            });
        });

        /* ── Render icons ────────────────────────────────────── */
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

    })();

    /* ── Monthly share ───────────────────────────────────── */
    function fmtMoney(n) {
        return '₹' + parseFloat(n || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    function fmtMonthLabel(month) {
        var parts = (month || '').split('-');
        if (parts.length < 2) return month;
        return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, 1)
            .toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });
    }

    function loadShareData(month) {
        if (!month) return;
        var result = document.getElementById('share-result');
        result.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:24px 0;">'
            + '<i data-lucide="loader" style="width:18px;height:18px;color:#cbd5e1;"></i>'
            + '<span style="font-size:13px;color:#94a3b8;">Loading…</span>'
            + '</div>';
        lucide.createIcons();

        $.get('/profile/getDistributionByMonth/' + month, function (res) {
            var data = res.data;
            var monthLabel = fmtMonthLabel(month);

            if (!data) {
                result.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:24px 0;">'
                    + '<div style="width:40px;height:40px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">'
                    + '<i data-lucide="inbox" style="width:18px;height:18px;color:#e2e8f0;"></i>'
                    + '</div>'
                    + '<span style="font-size:13px;font-weight:500;color:#94a3b8;">No distribution for ' + monthLabel + '</span>'
                    + '</div>';
                lucide.createIcons();
                return;
            }

            var final = parseFloat(data.final_amount || 0);
            var badgeBg, badgeFg, badgeIcon, badgeLabel;

            if (final > 0.005) {
                badgeBg = '#fee2e2'; badgeFg = '#dc2626';
                badgeIcon = 'arrow-up-right'; badgeLabel = 'Due';
            } else if (final < -0.005) {
                badgeBg = '#dcfce7'; badgeFg = '#15803d';
                badgeIcon = 'arrow-down-left'; badgeLabel = 'Credit';
            } else {
                badgeBg = '#f1f5f9'; badgeFg = '#64748b';
                badgeIcon = 'check'; badgeLabel = 'Settled';
            }

            result.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:8px 0 4px;">'
                + '<div style="font-size:32px;font-weight:700;color:#0f172a;font-family:\'JetBrains Mono\',monospace;letter-spacing:-0.02em;">'
                + fmtMoney(Math.abs(final))
                + '</div>'
                + '<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 14px;border-radius:999px;'
                + 'font-size:12px;font-weight:700;background:' + badgeBg + ';color:' + badgeFg + ';">'
                + '<i data-lucide="' + badgeIcon + '" style="width:12px;height:12px;"></i>'
                + badgeLabel
                + '</span>'
                + '<div style="font-size:12px;color:#94a3b8;margin-top:2px;">' + monthLabel + '</div>'
                + '</div>';
            lucide.createIcons();

        }).fail(function () {
            result.innerHTML = '<div style="text-align:center;padding:24px 0;font-size:13px;color:#ef4444;">Failed to load data.</div>';
        });
    }

    // On page load — fetch latest month then load its data
    $.get('/profile/getLatestDistributionMonth', function (res) {
        var month = res.month || '';
        var input = document.getElementById('share-month-input');
        if (month) {
            input.value = month;
            loadShareData(month);
        } else {
            // No data at all — show empty state immediately
            input.value = '';
            document.getElementById('share-result').innerHTML =
                '<div style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:24px 0;">'
                + '<div style="width:40px;height:40px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">'
                + '<i data-lucide="inbox" style="width:18px;height:18px;color:#e2e8f0;"></i>'
                + '</div>'
                + '<span style="font-size:13px;font-weight:500;color:#94a3b8;">No distribution records yet</span>'
                + '</div>';
            lucide.createIcons();
        }
    }).fail(function () {
        document.getElementById('share-result').innerHTML =
            '<div style="text-align:center;padding:24px 0;font-size:13px;color:#ef4444;">Failed to load.</div>';
    });
</script>

<?php $this->endSection(); ?>
<?php $this->extend('layout/main'); ?>

<?= $this->section('title') ?>Profile<?= $this->endSection() ?>

<?php $this->section('content'); ?>

<?php
/**
 * @var array{
 *     id: int,
 *     name: string,
 *     role: string
 * } $currentUser
 * @var App\Entities\User $user
 */

$isAdmin = $currentUser['role'] === 'admin';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Profile</h1>
        <p class="page-subtitle">View and update your account details</p>
    </div>
</div>

<div class="flex flex-col gap-5" style="max-width:640px;">

    <!-- ── Monthly share card ──────────────────────────────── -->
    <div class="ss-card" id="share-card">
        <div class="ss-card-body">

            <!-- Header row: title + month picker -->
            <div class="flex items-center justify-between gap-4 flex-wrap mb-5">
                <div class="flex items-center gap-2">
                    <div class="stat-icon-box bg-indigo-100">
                        <i data-lucide="bar-chart-2" class="w-[15px] h-[15px] text-indigo-700"></i>
                    </div>
                    <div>
                        <div class="text-[15px] font-bold text-surface-900">My Monthly Share</div>
                        <div class="text-xs text-surface-400 mt-px">Your final amount for the selected month</div>
                    </div>
                </div>

                <!-- Month picker -->
                <div class="relative">
                    <i data-lucide="calendar" class="w-[13px] h-[13px] text-surface-400"
                        style="position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none;"></i>
                    <input type="month" id="share-month-input" class="ss-input font-mono text-[13px]"
                        style="padding-left:32px;padding-right:12px;min-height:36px;width:auto;"
                        onchange="loadShareData(this.value)">
                </div>
            </div>

            <!-- Result area -->
            <div id="share-result">
                <div class="flex flex-col items-center gap-2 py-6">
                    <i data-lucide="loader" class="w-[18px] h-[18px] text-surface-300"></i>
                    <span class="text-[13px] text-surface-400">Loading…</span>
                </div>
            </div>

        </div>
    </div>

    <!-- ── Account overview card ───────────────────────────── -->
    <div class="ss-card">
        <div class="ss-card-body flex items-center gap-5">

            <!-- Avatar -->
            <div id="profile-avatar"
                class="w-16 h-16 rounded-full flex items-center justify-center text-[1.375rem] font-bold flex-shrink-0 tracking-wide">
            </div>

            <!-- Name / email / meta -->
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span id="overview-name" class="text-lg font-bold text-surface-900" style="line-height:1.2;">
                        <?= esc($user->name) ?>
                    </span>
                    <!-- Role badge -->
                    <?php if ($isAdmin): ?>
                        <span class="ss-badge bg-violet-100 text-violet-600 uppercase tracking-wide">Admin</span>
                    <?php else: ?>
                        <span class="ss-badge bg-surface-100 text-surface-600 uppercase tracking-wide">Member</span>
                    <?php endif; ?>
                </div>
                <div id="overview-email" class="text-sm text-surface-500 mt-1">
                    <?= esc($user->email) ?>
                </div>
                <?php if (!empty($user->joined_date)): ?>
                    <div class="text-[13px] text-surface-400 mt-1.5 flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-[13px] h-[13px]"></i>
                        Member since <?= date('d M Y', strtotime((string) $user->joined_date)) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Personal information form ────────────────────────── -->
    <div class="ss-card">
        <div class="ss-card-header">
            <div class="flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-brand-500"></i>
                <span class="font-semibold text-[15px]">Personal Information</span>
            </div>
        </div>
        <div class="ss-card-body flex flex-col gap-4">

            <div>
                <label class="ss-label" for="info-name">Full Name</label>
                <input class="ss-input" type="text" id="info-name" value="<?= esc($user->name) ?>"
                    placeholder="Your name" autocomplete="name">
            </div>

            <div>
                <label class="ss-label" for="info-email">Email Address</label>
                <input class="ss-input" type="email" id="info-email" value="<?= esc($user->email) ?>"
                    placeholder="you@example.com" autocomplete="email">
            </div>

            <div class="flex justify-end pt-1">
                <button class="ss-btn ss-btn-primary" id="btn-save-info" style="min-width:130px;">
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- ── Change password form ─────────────────────────────── -->
    <div class="ss-card">
        <div class="ss-card-header">
            <div class="flex items-center gap-2">
                <i data-lucide="lock" class="w-4 h-4 text-brand-500"></i>
                <span class="font-semibold text-[15px]">Change Password</span>
            </div>
        </div>
        <div class="ss-card-body flex flex-col gap-4">

            <div>
                <label class="ss-label" for="pw-current">Current Password</label>
                <div class="relative">
                    <input class="ss-input pr-11" type="password" id="pw-current" placeholder="Enter current password"
                        autocomplete="current-password">
                    <button type="button" class="text-surface-400"
                        data-toggle-password="pw-current"
                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;display:flex;">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="ss-label" for="pw-new">New Password</label>
                <div class="relative">
                    <input class="ss-input pr-11" type="password" id="pw-new" placeholder="At least 6 characters"
                        autocomplete="new-password">
                    <button type="button" class="text-surface-400"
                        data-toggle-password="pw-new"
                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;display:flex;">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="ss-label" for="pw-confirm">Confirm New Password</label>
                <div class="relative">
                    <input class="ss-input pr-11" type="password" id="pw-confirm" placeholder="Repeat new password"
                        autocomplete="new-password">
                    <button type="button" class="text-surface-400"
                        data-toggle-password="pw-confirm"
                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;display:flex;">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Strength indicator -->
            <div id="pw-strength-wrap" class="hidden">
                <div class="flex gap-1 mb-1.5">
                    <div class="pw-bar h-[3px] flex-1 rounded" style="background:#e2e8f0;"></div>
                    <div class="pw-bar h-[3px] flex-1 rounded" style="background:#e2e8f0;"></div>
                    <div class="pw-bar h-[3px] flex-1 rounded" style="background:#e2e8f0;"></div>
                </div>
                <span id="pw-strength-label" class="text-xs text-surface-400"></span>
            </div>

            <div class="flex justify-end pt-1">
                <button class="ss-btn ss-btn-primary" id="btn-save-password" style="min-width:150px;">
                    Update Password
                </button>
            </div>
        </div>
    </div>

</div><!-- /max-width wrapper -->

<script>
    (function() {

        /* ── Avatar ─────────────────────────────────────────── */
        // Colour + initials logic now lives globally in app.js
        // (window.renderAvatarInto) — no longer copy-pasted per view.
        const initialName = <?= json_encode($user->name) ?>;
        window.renderAvatarInto('profile-avatar', initialName);

        /* ── Save personal info ──────────────────────────────── */
        document.getElementById('btn-save-info').addEventListener('click', function() {
            const btn = this;
            const name = document.getElementById('info-name').value.trim();
            const email = document.getElementById('info-email').value.trim();

            if (!name) {
                ssToast('Name cannot be empty.', 'error');
                return;
            }
            if (!email) {
                ssToast('Email cannot be empty.', 'error');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Saving…';

            $.ajax({
                url: '/profile/updateInfo',
                type: 'POST',
                data: {
                    name,
                    email
                },
                success: function(res) {
                    ssToast(res.message || 'Profile updated.', 'success');
                    // Reflect changes in the overview card
                    document.getElementById('overview-name').textContent = res.name || name;
                    document.getElementById('overview-email').textContent = email;
                    window.renderAvatarInto('profile-avatar', res.name || name);
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to save changes.';
                    ssToast(msg, 'error');
                },
                complete: function() {
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

        const STRENGTH_LEVELS = [{
                color: '#ef4444',
                label: 'Weak'
            },
            {
                color: '#f97316',
                label: 'Fair'
            },
            {
                color: '#eab308',
                label: 'Good'
            },
            {
                color: '#22c55e',
                label: 'Strong'
            },
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

        pwNew.addEventListener('input', function() {
            const val = this.value;
            if (!val) {
                strengthWrap.style.display = 'none';
                return;
            }
            strengthWrap.style.display = 'block';
            const level = calcStrength(val);
            bars.forEach(function(bar, idx) {
                bar.style.background = idx <= level ? STRENGTH_LEVELS[level].color : '#e2e8f0';
            });
            strengthLabel.textContent = STRENGTH_LEVELS[level].label;
            strengthLabel.style.color = STRENGTH_LEVELS[level].color;
        });

        /* ── Save password ───────────────────────────────────── */
        document.getElementById('btn-save-password').addEventListener('click', function() {
            const btn = this;
            const current = document.getElementById('pw-current').value;
            const newPw = document.getElementById('pw-new').value;
            const confirm = document.getElementById('pw-confirm').value;

            if (!current) {
                ssToast('Enter your current password.', 'error');
                return;
            }
            if (!newPw) {
                ssToast('Enter a new password.', 'error');
                return;
            }
            if (newPw !== confirm) {
                ssToast('New passwords do not match.', 'error');
                return;
            }

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
                success: function(res) {
                    ssToast(res.message || 'Password updated.', 'success');
                    // Clear all password fields
                    document.getElementById('pw-current').value = '';
                    document.getElementById('pw-new').value = '';
                    document.getElementById('pw-confirm').value = '';
                    strengthWrap.style.display = 'none';
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to update password.';
                    ssToast(msg, 'error');
                },
                complete: function() {
                    btn.disabled = false;
                    btn.textContent = 'Update Password';
                }
            });
        });

        /* ── Render icons ────────────────────────────────────── */
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

    })();

    function loadShareData(month) {
        if (!month) return;
        var result = document.getElementById('share-result');
        result.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:24px 0;">' +
            '<i data-lucide="loader" style="width:18px;height:18px;color:#cbd5e1;"></i>' +
            '<span style="font-size:13px;color:#94a3b8;">Loading…</span>' +
            '</div>';
        lucide.createIcons();

        $.get('/profile/getDistributionByMonth/' + month, function(res) {
            var data = res.data;
            var monthLabel = fmtMonthLabel(month);

            if (!data) {
                result.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:24px 0;">' +
                    '<div style="width:40px;height:40px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">' +
                    '<i data-lucide="inbox" style="width:18px;height:18px;color:#e2e8f0;"></i>' +
                    '</div>' +
                    '<span style="font-size:13px;font-weight:500;color:#94a3b8;">No distribution for ' + monthLabel + '</span>' +
                    '</div>';
                lucide.createIcons();
                return;
            }

            var final = parseFloat(data.final_amount || 0);
            var badgeBg, badgeFg, badgeIcon, badgeLabel;

            if (final > 0.005) {
                badgeBg = '#fee2e2';
                badgeFg = '#dc2626';
                badgeIcon = 'arrow-up-right';
                badgeLabel = 'Due';
            } else if (final < -0.005) {
                badgeBg = '#dcfce7';
                badgeFg = '#15803d';
                badgeIcon = 'arrow-down-left';
                badgeLabel = 'Credit';
            } else {
                badgeBg = '#f1f5f9';
                badgeFg = '#64748b';
                badgeIcon = 'check';
                badgeLabel = 'Settled';
            }

            result.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:8px 0 4px;">' +
                '<div style="font-size:32px;font-weight:700;color:#0f172a;font-family:\'JetBrains Mono\',monospace;letter-spacing:-0.02em;">' +
                window.fmtMoney(Math.abs(final)) +
                '</div>' +
                '<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 14px;border-radius:999px;' +
                'font-size:12px;font-weight:700;background:' + badgeBg + ';color:' + badgeFg + ';">' +
                '<i data-lucide="' + badgeIcon + '" style="width:12px;height:12px;"></i>' +
                badgeLabel +
                '</span>' +
                '<div style="font-size:12px;color:#94a3b8;margin-top:2px;">' + monthLabel + '</div>' +
                '</div>';
            lucide.createIcons();

        }).fail(function() {
            result.innerHTML = '<div style="text-align:center;padding:24px 0;font-size:13px;color:#ef4444;">Failed to load data.</div>';
        });
    }

    // On page load — fetch latest month then load its data
    $.get('/profile/getLatestDistributionMonth', function(res) {
        var month = res.month || '';
        var input = document.getElementById('share-month-input');
        if (month) {
            input.value = month;
            loadShareData(month);
        } else {
            // No data at all — show empty state immediately
            input.value = '';
            document.getElementById('share-result').innerHTML =
                '<div style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:24px 0;">' +
                '<div style="width:40px;height:40px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">' +
                '<i data-lucide="inbox" style="width:18px;height:18px;color:#e2e8f0;"></i>' +
                '</div>' +
                '<span style="font-size:13px;font-weight:500;color:#94a3b8;">No distribution records yet</span>' +
                '</div>';
            lucide.createIcons();
        }
    }).fail(function() {
        document.getElementById('share-result').innerHTML =
            '<div style="text-align:center;padding:24px 0;font-size:13px;color:#ef4444;">Failed to load.</div>';
    });
</script>

<?php $this->endSection(); ?>
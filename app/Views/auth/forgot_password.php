<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Forgot Password<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="relative w-full min-h-screen flex items-center justify-center overflow-hidden px-4 py-6 bg-surface-50">

    <div style="position:absolute;top:-120px;left:-120px;width:400px;height:400px;border-radius:50%;pointer-events:none;
                background:radial-gradient(circle, rgba(92,106,240,0.12) 0%, transparent 70%);"></div>
    <div style="position:absolute;bottom:-80px;right:-80px;width:320px;height:320px;border-radius:50%;pointer-events:none;
                background:radial-gradient(circle, rgba(16,185,129,0.10) 0%, transparent 70%);"></div>

    <div class="bg-white border border-surface-200 rounded-[20px] shadow-[0_4px_32px_rgba(0,0,0,.08)] w-full max-w-[420px] py-10 px-9 relative z-[1]">

        <div class="text-center mb-8">
            <img src="<?= base_url('/assets/smartsplit-horizontal.svg') ?>" alt="SmartSplit Logo"
                class="w-[350px] h-12 mb-4 mx-auto">
            <h1 class="text-[22px] font-bold text-surface-900 tracking-[-0.02em] mb-1.5">Reset your password</h1>
            <p class="text-sm text-surface-500 m-0">Enter your email and recovery code to set a new password</p>
        </div>

        <!-- Flash messages -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="status-banner status-error mb-5">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                <span><?= esc(session()->getFlashdata('error')) ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="status-banner status-error mb-5" style="align-items:flex-start;">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-px"></i>
                <div>
                    <?php foreach (session()->getFlashdata('errors') as $err): ?>
                        <div><?= esc($err) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="post" action="<?= base_url('/auth/resetWithRecoveryCode') ?>" id="forgotForm" data-loading-submit>

            <?= csrf_field() ?>

            <!-- Email -->
            <div class="mb-[18px]">
                <label for="email" class="ss-label">Email address</label>
                <div class="field-icon-wrap">
                    <i data-lucide="mail" class="field-icon"></i>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required
                        autocomplete="email" class="ss-input pl-10">
                </div>
            </div>

            <!-- Recovery code -->
            <div class="mb-[18px]">
                <label for="recovery_code" class="ss-label">Recovery code</label>
                <div class="field-icon-wrap">
                    <i data-lucide="key-round" class="field-icon"></i>
                    <input type="text" id="recovery_code" name="recovery_code" placeholder="XXXX-XXXX-XXXX-XXXX"
                        required autocomplete="off" class="ss-input pl-10 font-mono uppercase"
                        style="letter-spacing:.03em;">
                </div>
                <p class="text-xs text-surface-400 mt-1.5 m-0">
                    This was shown once when your account was created. Don't have it? Ask your admin to
                    regenerate one for you.
                </p>
            </div>

            <!-- New password -->
            <div class="mb-[18px]">
                <label for="password" class="ss-label">New password</label>
                <div class="field-icon-wrap">
                    <i data-lucide="lock" class="field-icon"></i>
                    <input type="password" id="password" name="password" placeholder="At least 6 characters"
                        required autocomplete="new-password" class="ss-input pl-10 pr-11">
                    <button type="button" data-toggle-password="password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-transparent border-none cursor-pointer text-surface-400 p-1 flex items-center justify-center min-w-[32px] min-h-[32px]">
                        <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm password -->
            <div class="mb-6">
                <label for="password_confirm" class="ss-label">Confirm new password</label>
                <div class="field-icon-wrap">
                    <i data-lucide="lock" class="field-icon"></i>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Re-enter password"
                        required autocomplete="new-password" class="ss-input pl-10 pr-11">
                    <button type="button" data-toggle-password="password_confirm"
                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-transparent border-none cursor-pointer text-surface-400 p-1 flex items-center justify-center min-w-[32px] min-h-[32px]">
                        <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="ss-btn ss-btn-primary w-full"
                data-loading-text="Resetting…" data-loading-icon="loader">
                <i data-lucide="key-round" class="w-4 h-4"></i>
                <span>Reset password</span>
            </button>

        </form>

        <p class="text-center text-xs text-surface-400 mt-6 m-0">
            <a href="<?= base_url('/auth/login') ?>" class="text-brand-600 font-semibold no-underline hover:underline">
                &larr; Back to sign in
            </a>
        </p>

    </div>
</div>

<?= $this->endSection() ?>
<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Login<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="relative w-full min-h-screen flex items-center justify-center overflow-hidden px-4 py-6 bg-surface-50">

    <!-- Decorative background blobs — kept as inline style; a one-off
         radial-gradient like this isn't worth fighting Tailwind's
         arbitrary-value syntax for, and it's used nowhere else. -->
    <div style="position:absolute;top:-120px;left:-120px;width:400px;height:400px;border-radius:50%;pointer-events:none;
                background:radial-gradient(circle, rgba(92,106,240,0.12) 0%, transparent 70%);"></div>
    <div style="position:absolute;bottom:-80px;right:-80px;width:320px;height:320px;border-radius:50%;pointer-events:none;
                background:radial-gradient(circle, rgba(16,185,129,0.10) 0%, transparent 70%);"></div>

    <!-- Card -->
    <div class="bg-white border border-surface-200 rounded-[20px] shadow-[0_4px_32px_rgba(0,0,0,.08)] w-full max-w-[420px] py-10 px-9 relative z-[1]">

        <!-- Logo + heading -->
        <div class="text-center mb-8">
            <img src="<?= base_url('/assets/smartsplit-horizontal.svg') ?>" alt="SmartSplit Logo"
                class="w-[350px] h-12 mb-4 mx-auto">
            <h1 class="text-[22px] font-bold text-surface-900 tracking-[-0.02em] mb-1.5">Welcome back</h1>
            <p class="text-sm text-surface-500 m-0">Sign in to your SmartSplit account</p>
        </div>

        <!-- Flash error -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="status-banner status-error mb-5">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                <span><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="post" action="<?= base_url('/auth/loginUser') ?>" id="loginForm">

            <!-- Email -->
            <div class="mb-[18px]">
                <label for="email" class="ss-label">Email address</label>
                <div class="field-icon-wrap">
                    <i data-lucide="mail" class="field-icon"></i>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required
                        autocomplete="email" class="ss-input pl-10">
                </div>
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label for="password" class="ss-label">Password</label>
                <div class="field-icon-wrap">
                    <i data-lucide="lock" class="field-icon"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required
                        autocomplete="current-password" class="ss-input pl-10 pr-11">
                    <!-- Show/hide toggle -->
                    <button type="button" id="togglePassword" onclick="togglePasswordVisibility()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-transparent border-none cursor-pointer text-surface-400 p-1 flex items-center justify-center min-w-[32px] min-h-[32px]"
                        aria-label="Toggle password visibility">
                        <i data-lucide="eye" id="togglePasswordIcon" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" id="submitBtn" class="ss-btn ss-btn-primary w-full">
                <i data-lucide="log-in" class="w-4 h-4" id="btnIcon"></i>
                <span id="btnText">Sign in</span>
            </button>

        </form>

        <!-- Footer note -->
        <p class="text-center text-xs text-surface-400 mt-6 m-0">
            SmartSplit &copy; <?= date('Y') ?> &mdash; Expense sharing made simple
        </p>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Re-render lucide icons after page load
    lucide.createIcons();

    // ── Show / hide password ──────────────────────────────
    // FIX: was setAttribute()+createIcons() directly, which does not
    // re-render an icon Lucide has already swapped to <svg> — same bug
    // class as expensetype's button icon. Uses the safe project helper now.
    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        window.setLucideIcon('togglePasswordIcon', isHidden ? 'eye-off' : 'eye');
    }

    // ── Loading state on submit ───────────────────────────
    // Same fix applied here — the spinner icon was never actually
    // appearing on submit.
    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        btn.disabled = true;
        btn.style.opacity = '0.75';
        btn.style.cursor = 'not-allowed';
        btnText.textContent = 'Signing in…';
        window.setLucideIcon('btnIcon', 'loader');
    });
</script>
<?= $this->endSection() ?>
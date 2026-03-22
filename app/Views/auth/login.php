<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Login<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div style="
    width: 100%;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    background: #f8fafc;
    position: relative;
    overflow: hidden;
">

    <!-- Decorative background blobs -->
    <div style="
        position: absolute;
        top: -120px; left: -120px;
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(92,106,240,0.12) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    "></div>
    <div style="
        position: absolute;
        bottom: -80px; right: -80px;
        width: 320px; height: 320px;
        background: radial-gradient(circle, rgba(16,185,129,0.10) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    "></div>

    <!-- Card -->
    <div style="
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 4px 32px rgba(0,0,0,.08);
        width: 100%;
        max-width: 420px;
        padding: 40px 36px;
        position: relative;
        z-index: 1;
    ">

        <!-- Logo + heading -->
        <div style="text-align: center; margin-bottom: 32px;">
            <img src="<?= base_url('/assets/smartsplit-horizontal.svg') ?>" alt="SmartSplit Logo"
                style="width:350px;height:48px;margin-bottom:16px;">
            <h1 style="
                font-family: 'DM Sans', sans-serif;
                font-size: 22px;
                font-weight: 700;
                color: #0f172a;
                letter-spacing: -0.02em;
                margin: 0 0 6px;
            ">Welcome back</h1>
            <p style="
                font-family: 'DM Sans', sans-serif;
                font-size: 14px;
                color: #64748b;
                margin: 0;
            ">Sign in to your SmartSplit account</p>
        </div>

        <!-- Flash error -->
        <?php if (session()->getFlashdata('error')): ?>
            <div style="
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: #dc2626;
        ">
                <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;margin-top:1px;"></i>
                <span><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="post" action="<?= base_url('/auth/loginUser') ?>" id="loginForm">

            <!-- Email -->
            <div style="margin-bottom: 18px;">
                <label for="email" style="
                    display: block;
                    font-family: 'DM Sans', sans-serif;
                    font-size: 13px;
                    font-weight: 600;
                    color: #475569;
                    margin-bottom: 6px;
                ">Email address</label>
                <div style="position: relative;">
                    <i data-lucide="mail" style="
                        position: absolute;
                        left: 13px; top: 50%;
                        transform: translateY(-50%);
                        width: 16px; height: 16px;
                        color: #94a3b8;
                        pointer-events: none;
                    "></i>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required
                        autocomplete="email" style="
                            width: 100%;
                            padding: 11px 14px 11px 40px;
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            font-family: 'DM Sans', sans-serif;
                            font-size: 16px;
                            color: #1e293b;
                            background: #fff;
                            outline: none;
                            min-height: 44px;
                            transition: border-color .15s, box-shadow .15s;
                            -webkit-appearance: none;
                        "
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                </div>
            </div>

            <!-- Password -->
            <div style="margin-bottom: 24px;">
                <label for="password" style="
                    display: block;
                    font-family: 'DM Sans', sans-serif;
                    font-size: 13px;
                    font-weight: 600;
                    color: #475569;
                    margin-bottom: 6px;
                ">Password</label>
                <div style="position: relative;">
                    <i data-lucide="lock" style="
                        position: absolute;
                        left: 13px; top: 50%;
                        transform: translateY(-50%);
                        width: 16px; height: 16px;
                        color: #94a3b8;
                        pointer-events: none;
                    "></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required
                        autocomplete="current-password" style="
                            width: 100%;
                            padding: 11px 44px 11px 40px;
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            font-family: 'DM Sans', sans-serif;
                            font-size: 16px;
                            color: #1e293b;
                            background: #fff;
                            outline: none;
                            min-height: 44px;
                            transition: border-color .15s, box-shadow .15s;
                            -webkit-appearance: none;
                        "
                        onfocus="this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    <!-- Show/hide toggle -->
                    <button type="button" id="togglePassword" onclick="togglePasswordVisibility()" style="
                            position: absolute;
                            right: 12px; top: 50%;
                            transform: translateY(-50%);
                            background: none;
                            border: none;
                            cursor: pointer;
                            color: #94a3b8;
                            padding: 4px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 32px;
                            min-height: 32px;
                        " aria-label="Toggle password visibility">
                        <i data-lucide="eye" id="togglePasswordIcon" style="width:16px;height:16px;"></i>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" id="submitBtn" style="
                    width: 100%;
                    padding: 13px 20px;
                    background: #5c6af0;
                    color: #fff;
                    border: none;
                    border-radius: 8px;
                    font-family: 'DM Sans', sans-serif;
                    font-size: 15px;
                    font-weight: 600;
                    cursor: pointer;
                    min-height: 44px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    transition: background .15s, transform .1s, box-shadow .15s;
                    touch-action: manipulation;
                    -webkit-appearance: none;
                " onmouseover="this.style.background='#4549e4';this.style.boxShadow='0 4px 14px rgba(92,106,240,.40)'"
                onmouseout="this.style.background='#5c6af0';this.style.boxShadow='none'"
                onmousedown="this.style.transform='translateY(1px)'" onmouseup="this.style.transform='translateY(0)'">
                <i data-lucide="log-in" style="width:16px;height:16px;" id="btnIcon"></i>
                <span id="btnText">Sign in</span>
            </button>

        </form>

        <!-- Footer note -->
        <p style="
            text-align: center;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            color: #94a3b8;
            margin: 24px 0 0;
        ">SmartSplit &copy; <?= date('Y') ?> &mdash; Expense sharing made simple</p>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Re-render lucide icons after page load
    lucide.createIcons();

    // ── Show / hide password ──────────────────────────────
    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        icon.setAttribute('data-lucide', isHidden ? 'eye-off' : 'eye');
        lucide.createIcons();
    }

    // ── Loading state on submit ───────────────────────────
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        btn.disabled = true;
        btn.style.opacity = '0.75';
        btn.style.cursor = 'not-allowed';
        btnText.textContent = 'Signing in…';
        btnIcon.setAttribute('data-lucide', 'loader');
        lucide.createIcons();
    });
</script>
<?= $this->endSection() ?>
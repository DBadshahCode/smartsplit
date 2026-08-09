<?php
$uri = service('uri');
$seg1 = $uri->getSegment(1);

// Helper: returns 'active' class string if the segment matches
function navActive(string $segment, string $check): string
{
    return $segment === $check ? 'active' : '';
}
?>

<aside id="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="<?= base_url('/') ?>" class="flex items-center gap-3 no-underline">
            <img src="<?= base_url('/assets/smartsplit-dark.svg') ?>" alt="SmartSplit Logo" class="w-[200px] h-auto">
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <!-- Main -->
        <div class="nav-section-label">Main</div>

        <a href="<?= base_url('/') ?>" class="nav-link <?= navActive($seg1, '') ?>">
            <i data-lucide="layout-dashboard" class="nav-icon w-4 h-4"></i>
            <span class="nav-text">Dashboard</span>
        </a>

        <?php if (session()->get('role') === 'admin'): ?>
            <a href="<?= base_url('/user') ?>" class="nav-link <?= navActive($seg1, 'user') ?>">
                <i data-lucide="users" class="nav-icon w-4 h-4"></i>
                <span class="nav-text">Users</span>
            </a>
        <?php endif; ?>

        <!-- Expenses -->
        <div class="nav-section-label">Expenses</div>

        <a href="<?= base_url('/expensetype') ?>" class="nav-link <?= navActive($seg1, 'expensetype') ?>">
            <i data-lucide="tag" class="nav-icon w-4 h-4"></i>
            <span class="nav-text">Expense Types</span>
        </a>

        <a href="<?= base_url('/expense') ?>" class="nav-link <?= navActive($seg1, 'expense') ?>">
            <i data-lucide="receipt" class="nav-icon w-4 h-4"></i>
            <span class="nav-text">Expenses</span>
        </a>

        <a href="<?= base_url('/absentday') ?>" class="nav-link <?= navActive($seg1, 'absentday') ?>">
            <i data-lucide="calendar-x" class="nav-icon w-4 h-4"></i>
            <span class="nav-text">Absent Days</span>
        </a>

        <!-- Reports -->
        <div class="nav-section-label">Reports</div>

        <a href="<?= base_url('/finaldistribution') ?>" class="nav-link <?= navActive($seg1, 'finaldistribution') ?>">
            <i data-lucide="bar-chart-2" class="nav-icon w-4 h-4"></i>
            <span class="nav-text">Final Distribution</span>
        </a>

    </nav>

    <!-- Sidebar footer -->
    <div class="sidebar-footer">
        <a href="<?= base_url('/auth/logout') ?>" class="nav-link nav-link-danger">
            <i data-lucide="log-out" class="nav-icon w-4 h-4"></i>
            <span class="nav-text">Logout</span>
        </a>
    </div>

</aside>
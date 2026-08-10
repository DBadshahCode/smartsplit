// ══════════════════════════════════════════════════════════════════
// app.js — shared, page-independent JS helpers.
// Load this once from your layout (layout/main), before any
// page-specific <script> section runs, e.g.:
//     <script src="/assets/js/app.js"></script>
// so functions like initActionMenus() are already defined by the
// time a view's own script calls them.
// ══════════════════════════════════════════════════════════════════

// ── Reusable 3-dot row action menu ─────────────────────────────────
// Pairs with the `.action-menu-wrap / .action-menu-trigger /
// .action-menu-dropdown / .action-menu-item` classes in app.css.
//
// Usage in any view, after you render rows containing that markup:
//     initActionMenus(scopeEl);   // scopeEl optional, defaults to document
//
// Call it every time you re-render rows (page change, search, filter,
// reload, etc.) — it only wires up triggers, so calling it repeatedly
// on the same elements is harmless (each trigger just gets its click
// listener attached again... to avoid double-binding on repeated
// calls over the SAME nodes, prefer scoping to the freshly-rendered
// container, as SS.Table's onRender/rowFn callbacks already do).
function initActionMenus(scope) {
    scope = scope || document;
    scope.querySelectorAll('.action-menu-trigger').forEach(function (trigger) {
        if (trigger.dataset.menuBound === '1') return; // avoid double-binding
        trigger.dataset.menuBound = '1';
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var dropdown = this.nextElementSibling;
            var wasOpen = dropdown.classList.contains('is-open');
            closeAllActionMenus();
            if (!wasOpen) {
                dropdown.classList.add('is-open');
                this.classList.add('is-open');
            }
        });
    });
}

function closeAllActionMenus() {
    document.querySelectorAll('.action-menu-dropdown.is-open').forEach(function (d) {
        d.classList.remove('is-open');
        if (d.previousElementSibling) d.previousElementSibling.classList.remove('is-open');
    });
}

// Wired once, globally — closes whatever menu is open, no matter
// which view/table it belongs to.
document.addEventListener('click', closeAllActionMenus);
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllActionMenus();
});
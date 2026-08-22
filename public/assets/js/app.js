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
  scope.querySelectorAll(".action-menu-trigger").forEach(function (trigger) {
    if (trigger.dataset.menuBound === "1") return; // avoid double-binding
    trigger.dataset.menuBound = "1";
    trigger.addEventListener("click", function (e) {
      e.stopPropagation();
      var dropdown = this.nextElementSibling;
      var wasOpen = dropdown.classList.contains("is-open");
      closeAllActionMenus();
      if (!wasOpen) {
        dropdown.classList.add("is-open");
        this.classList.add("is-open");
      }
    });
  });
}

function closeAllActionMenus() {
  document
    .querySelectorAll(".action-menu-dropdown.is-open")
    .forEach(function (d) {
      d.classList.remove("is-open");
      if (d.previousElementSibling)
        d.previousElementSibling.classList.remove("is-open");
    });
}

// Wired once, globally — closes whatever menu is open, no matter
// which view/table it belongs to.
document.addEventListener("click", closeAllActionMenus);
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") closeAllActionMenus();
});

// ── Password visibility toggle (generic, global) ───────────────────
// Markup:
//   <button type="button" data-toggle-password="password">
//       <i data-lucide="eye"></i>
//   </button>
//   <input id="password" type="password" ...>
// data-toggle-password's value is the id of the input to toggle.
// NOTE: the icon inside the trigger is located via 'svg, [data-lucide]'
// (not just [data-lucide]) — by the time a user can click anything,
// main.php's global lucide.createIcons() has already converted the
// original <i data-lucide="eye"> into an <svg>, and this codebase
// doesn't rely on that attribute surviving the conversion anywhere
// else, so we don't assume it here either.
function initPasswordToggles(scope) {
  scope = scope || document;
  scope.querySelectorAll("[data-toggle-password]").forEach(function (trigger) {
    if (trigger.dataset.pwToggleBound === "1") return;
    trigger.dataset.pwToggleBound = "1";
    trigger.addEventListener("click", function () {
      var input = document.getElementById(this.dataset.togglePassword);
      if (!input) return;
      var isHidden = input.type === "password";
      input.type = isHidden ? "text" : "password";
      var icon = this.querySelector("svg, [data-lucide]");
      if (icon && typeof window.swapLucideIcon === "function") {
        window.swapLucideIcon(icon, isHidden ? "eye-off" : "eye");
      }
    });
  });
}

// ── Submit-button loading state (generic, global) ──────────────────
// Markup:
//   <form data-loading-submit>
//       <button type="submit" data-loading-text="Signing in…" data-loading-icon="loader">
//           <i data-lucide="log-in"></i><span>Sign in</span>
//       </button>
//   </form>
// On submit: disables the button, swaps its icon (default "loader-2"),
// and swaps the text inside its <span> to data-loading-text. Intended
// for real (non-AJAX) form posts, where the loading state just needs
// to survive until the browser navigates away.
function initFormLoadingState(scope) {
  scope = scope || document;
  scope.querySelectorAll("form[data-loading-submit]").forEach(function (form) {
    if (form.dataset.loadingBound === "1") return;
    form.dataset.loadingBound = "1";
    form.addEventListener("submit", function () {
      var btn = form.querySelector('button[type="submit"]');
      if (!btn) return;
      btn.disabled = true;
      btn.style.opacity = "0.75";
      btn.style.cursor = "not-allowed";
      var span = btn.querySelector("span");
      if (span && btn.dataset.loadingText)
        span.textContent = btn.dataset.loadingText;
      var icon = btn.querySelector("svg, [data-lucide]");
      if (icon && typeof window.swapLucideIcon === "function") {
        window.swapLucideIcon(icon, btn.dataset.loadingIcon || "loader-2");
      }
    });
  });
}

// Auto-wire both on first paint — covers static, server-rendered markup
// with zero page-specific script needed. Views that inject NEW matching
// markup later (e.g. into a modal via AJAX) should call
// initPasswordToggles(scope) / initFormLoadingState(scope) themselves,
// same convention as initActionMenus.
document.addEventListener("DOMContentLoaded", function () {
  initPasswordToggles();
  initFormLoadingState();
});

// ── Avatar helper (was duplicated verbatim in every view) ──────────
// Deterministic colour + initials for a user's name, used anywhere a
// small circular avatar is rendered — profile, expense involvement
// lists, user management, dashboard recent-activity rows, etc.
var AVATAR_COLORS = [
  ["#ede9fe", "#7c3aed"],
  ["#fce7f3", "#be185d"],
  ["#dcfce7", "#15803d"],
  ["#fef9c3", "#a16207"],
  ["#dbeafe", "#1d4ed8"],
  ["#fee2e2", "#dc2626"],
  ["#e0e7ff", "#4338ca"],
  ["#f0fdf4", "#166534"],
];

window.avatarColor = function (name) {
  var i = 0;
  for (var c of name || "") i += c.charCodeAt(0);
  return AVATAR_COLORS[i % AVATAR_COLORS.length];
};

window.initials = function (name) {
  if (!name) return "?";
  var p = name.trim().split(" ");
  return (
    p.length >= 2 ? p[0][0] + p[p.length - 1][0] : name.slice(0, 2)
  ).toUpperCase();
};

// Convenience wrapper for the common case: paint an existing element
// (e.g. a div with the right size/shape classes already on it) with
// the computed background/foreground colour + initials for a name.
// Accepts either an element or an element id.
window.renderAvatarInto = function (elOrId, name) {
  var el =
    typeof elOrId === "string" ? document.getElementById(elOrId) : elOrId;
  if (!el) return;
  var pair = window.avatarColor(name);
  el.style.background = pair[0];
  el.style.color = pair[1];
  el.textContent = window.initials(name);
};

// ── Money formatter (design-system rule: ₹ + en-IN locale, 2dp) ────
window.fmtMoney = function (n) {
  return (
    "₹" +
    parseFloat(n || 0).toLocaleString("en-IN", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  );
};

// ── Generic modal open/close (backdrop + panel fade/scale) ─────────
// Matches the project's standard modal pattern:
//   Open:  backdrop+modal display set → next frame → opacity 1, scale(1)
//   Close: opacity 0, scale(0.97) → 180ms → display:none
//
// Usage:
//   window.ssModalOpen({ modalId: 'qr-modal', backdropId: 'qr-backdrop' });
//   window.ssModalOpen({ modalId: 'add-user-modal', backdropId: 'modal-backdrop',
//                         display: 'flex', focusId: 'u-name' });
//   window.ssModalClose({ modalId: 'qr-modal', backdropId: 'qr-backdrop' });
//   window.ssModalClose({ modalId: 'add-user-modal', backdropId: 'modal-backdrop',
//                          onClosed: function () { form.reset(); } });
//
// `display` — 'block' (default, for simple .modal-panel dialogs like the
// QR modal) or 'flex' (required for any .modal-shell modal — display:block
// breaks the flex-column scroll layout on short viewports, bug #9).
// Only the modal panel's opacity is animated — the backdrop just toggles
// display, matching every existing modal's original behavior exactly.
//
// NOTE: ssConfirm's own modal intentionally does NOT use this helper.
// Its close timing — nulling _ssConfirmCallback synchronously, before
// the 180ms fade-out completes — is part of a documented, already-fixed
// bug (#8) and is left untouched to avoid any risk of reintroducing it.
window.ssModalOpen = function (opts) {
  var modal = document.getElementById(opts.modalId);
  var backdrop = document.getElementById(opts.backdropId);
  if (!modal || !backdrop) return;
  backdrop.style.display = "block";
  modal.style.display = opts.display || "block";
  if (typeof lucide !== "undefined") lucide.createIcons();
  requestAnimationFrame(function () {
    modal.style.opacity = "1";
    modal.style.transform = "translate(-50%,-50%) scale(1)";
  });
  if (opts.focusId) {
    var focusEl = document.getElementById(opts.focusId);
    if (focusEl) focusEl.focus();
  }
};

window.ssModalClose = function (opts) {
  var modal = document.getElementById(opts.modalId);
  var backdrop = document.getElementById(opts.backdropId);
  if (!modal || !backdrop) return;
  modal.style.opacity = "0";
  modal.style.transform = "translate(-50%,-50%) scale(0.97)";
  setTimeout(function () {
    modal.style.display = "none";
    backdrop.style.display = "none";
    if (typeof opts.onClosed === "function") opts.onClosed();
  }, 180);
};

// ── Month label formatter: "2026-03" → "March 2026" ─────────────────
// Was duplicated verbatim in profile.php (monthly share widget) and
// finaldistribution/index.php — now a single global implementation.
window.fmtMonthLabel = function (month) {
  var parts = (month || "").split("-");
  if (parts.length < 2) return month;
  return new Date(
    parseInt(parts[0], 10),
    parseInt(parts[1], 10) - 1,
    1,
  ).toLocaleDateString("en-IN", {
    month: "long",
    year: "numeric",
  });
};

// ── HTML-escape helper (mirrors PHP's esc()) ────────────────────────
// Every view that builds table rows/cards via JS template strings and
// injects them with innerHTML must escape any server-supplied text
// (names, descriptions, emails, etc.) before interpolating it — same
// discipline the PHP views already apply via esc(). Without this,
// a name/description containing HTML (or, in an inline onclick with
// interpolated text, a stray single quote) can break rendering or
// inject markup for anyone else viewing that table/card.
window.escHtml = function (str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
};

// ── Generate password (Add User / Reset Password / Change Password) ──
// Fetches a random memorable password from the server and fills it into
// the given input. Reveals it if currently masked — a masked freshly-
// generated password defeats the point of being able to see/share it —
// and fires a native 'input' event so any addEventListener('input', ...)
// listeners (e.g. profile's password-strength meter) react to the
// programmatic fill same as if the user had typed it.
// opts.mirrorId — optional second field to fill with the same value
//                 (a "confirm password" field)
window.generatePassword = function (inputId, opts) {
  opts = opts || {};
  $.get("/user/generatePassword", function (res) {
    if (res.status !== "success" || !res.password) {
      if (typeof window.ssToast === "function") {
        ssToast("Could not generate password.", "error");
      }
      return;
    }
    var input = document.getElementById(inputId);
    if (!input) return;
    input.value = res.password;

    if (input.type === "password") {
      var toggleBtn = document.querySelector(
        '[data-toggle-password="' + inputId + '"]',
      );
      var icon = toggleBtn
        ? toggleBtn.querySelector("svg, [data-lucide]")
        : null;
      input.type = "text";
      if (icon && typeof window.swapLucideIcon === "function") {
        window.swapLucideIcon(icon, "eye-off");
      }
    }

    if (opts.mirrorId) {
      var mirror = document.getElementById(opts.mirrorId);
      if (mirror) mirror.value = res.password;
    }

    input.dispatchEvent(new Event("input", { bubbles: true }));

    if (typeof opts.afterFill === "function") opts.afterFill();
  }).fail(function () {
    if (typeof window.ssToast === "function") {
      ssToast("Could not generate password.", "error");
    }
  });
};

// ── Copy a password field's value to clipboard ──────────────────────
window.copyPasswordField = function (inputId) {
  var input = document.getElementById(inputId);
  if (!input || !input.value) {
    if (typeof window.ssToast === "function") {
      ssToast("Generate or enter a password first.", "info");
    }
    return;
  }
  var text = input.value;

  function fallbackCopy() {
    var ta = document.createElement("textarea");
    ta.value = text;
    ta.style.position = "fixed";
    ta.style.opacity = "0";
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand("copy");
      if (typeof window.ssToast === "function")
        ssToast("Password copied to clipboard.", "success");
    } catch (e) {
      if (typeof window.ssToast === "function")
        ssToast("Could not copy password.", "error");
    }
    document.body.removeChild(ta);
  }

  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard
      .writeText(text)
      .then(function () {
        if (typeof window.ssToast === "function")
          ssToast("Password copied to clipboard.", "success");
      })
      .catch(fallbackCopy);
  } else {
    fallbackCopy();
  }
};

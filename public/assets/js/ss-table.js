/**
 * SS.Table — SmartSplit reusable table engine
 * Provides search, multi-column sort, pagination, loading/empty states.
 * Designed for SmartSplit's AJAX-rendered tables. No DataTables. No extra CSS.
 *
 * Usage:
 *   var t = SS.Table({ tbodyId, url, cols, rowFn, … });
 *   t.reload();   // force re-fetch from server
 *   t.refresh();  // re-render current data (after external mutation)
 *
 * Config options:
 *   tbodyId         {string}   required — id of the <tbody> to render into
 *   url             {string}   required — AJAX GET endpoint; must return { data: [] }
 *   cols            {Array}    required — column definitions (see below)
 *   rowFn           {Function} required — function(row) → HTML string for one <tr>
 *
 *   searchId        {string}   id of an existing <input> to use as search box
 *                              (if omitted, SS.Table injects one above the table)
 *   searchKeys      {Array}    keys to search across; default: all col keys
 *   searchPlaceholder {string} placeholder text; default: 'Search…'
 *
 *   pageSize        {number}   rows per page; default: 15; 0 = no pagination
 *   pagerId         {string}   id of an existing container for pager controls
 *                              (if omitted, SS.Table injects one below the table)
 *
 *   countId         {string}   id of element to update with filtered row count
 *   extraHeaderId   {string}   id of element to update with extra header content
 *                              (e.g. running total — updated via onLoad)
 *
 *   onLoad          {Function} callback(data) fired after each successful fetch
 *   onRender        {Function} callback(visibleRows) fired after each render
 *
 *   emptyIcon       {string}   lucide icon name for empty state; default: 'inbox'
 *   emptyText       {string}   empty state message; default: 'No records found'
 *   loadingText     {string}   loading state message; default: 'Loading…'
 *
 *   colSpan         {number}   colspan for state rows; default: cols.length + 1
 *
 * Column definition shape:
 *   { label, key, sortable, align, width, noSearch }
 *   label    {string}  header text
 *   key      {string}  property name on the data row used for sorting/searching
 *   sortable {boolean} default: true (set false to disable sort on this col)
 *   align    {string}  'left'|'center'|'right'; default: 'left'
 *   width    {string}  e.g. '120px' — optional fixed width
 *   noSearch {boolean} exclude this column from search; default: false
 *
 * The last column in cols is always the Actions column — it is right-aligned
 * and non-sortable by convention. You may override by setting align/sortable.
 *
 * Static (non-managed) header cells:
 *   If your <thead><tr> markup contains extra <th> cells that are NOT part of
 *   `cols` — e.g. a bulk-select checkbox column rendered conditionally by
 *   PHP — mark that <th> with `data-ss-static="1"`. SS.Table will leave any
 *   such cells untouched when it rebuilds the header row, instead of wiping
 *   them out. Static cells must come before the SS.Table-managed cells and
 *   the matching <td> must be prepended first (in the same order) inside
 *   rowFn for every row.
 */

(function (root) {
  "use strict";

  /* ─── Constants ───────────────────────────────────────────────────── */
  var HEADER_STYLE = [
    "padding:11px 16px",
    "text-align:left",
    "font-size:11px",
    "font-weight:600",
    "color:#94a3b8",
    "letter-spacing:.05em",
    "text-transform:uppercase",
    "border-bottom:1px solid #f1f5f9",
    "white-space:nowrap",
    "background:#f8fafc",
    "user-select:none",
  ].join(";");

  var SORT_ICON_STYLE = [
    "display:inline-flex",
    "align-items:center",
    "margin-left:4px",
    "opacity:.4",
    "vertical-align:middle",
    "transition:opacity .15s",
  ].join(";");

  var PAGER_BTN_BASE = [
    "display:inline-flex",
    "align-items:center",
    "justify-content:center",
    "gap:4px",
    "padding:5px 12px",
    "border-radius:6px",
    "border:1px solid #e2e8f0",
    "background:#fff",
    "color:#334155",
    "font-size:13px",
    "font-weight:500",
    'font-family:"DM Sans",sans-serif',
    "cursor:pointer",
    "transition:background .15s,border-color .15s",
    "min-height:32px",
  ].join(";");

  var SEARCH_WRAP_STYLE = [
    "display:flex",
    "align-items:center",
    "gap:8px",
    "padding:12px 16px",
    "border-bottom:1px solid #f1f5f9",
  ].join(";");

  var PAGER_WRAP_STYLE = [
    "display:flex",
    "align-items:center",
    "justify-content:space-between",
    "flex-wrap:wrap",
    "gap:8px",
    "padding:12px 16px",
    "border-top:1px solid #f1f5f9",
  ].join(";");

  /* ─── Tiny helpers ────────────────────────────────────────────────── */
  function el(id) {
    return document.getElementById(id);
  }

  function setHtml(id, html) {
    var e = el(id);
    if (e) e.innerHTML = html;
  }

  function setText(id, txt) {
    var e = el(id);
    if (e) e.textContent = txt;
  }

  /* Find the <table> that contains the given <tbody> */
  function tableOf(tbody) {
    var n = tbody.parentNode;
    while (n && n.tagName !== "TABLE") n = n.parentNode;
    return n;
  }

  /* Find the <thead> of the same table, or null */
  function theadOf(tbody) {
    var t = tableOf(tbody);
    return t ? t.querySelector("thead") : null;
  }

  /* Find or create the wrapping .ss-table-wrap (parent of the <table>) */
  function tableWrap(tbody) {
    var t = tableOf(tbody);
    return t ? t.parentNode : null;
  }

  /* Normalise a value to a sortable string */
  function toSortVal(v) {
    if (v === null || v === undefined) return "";
    // CI4 Time objects arrive as "YYYY-MM-DD HH:MM:SS" or { date: '...' }
    if (typeof v === "object" && v.date) return String(v.date);
    return String(v);
  }

  /* Case-insensitive includes check */
  function strIncludes(str, q) {
    return (
      String(str || "")
        .toLowerCase()
        .indexOf(q) !== -1
    );
  }

  /* Unique id generator */
  var _uid = 0;
  function uid(prefix) {
    return (prefix || "ss") + ++_uid;
  }

  /* Inject a block element immediately before a reference element */
  function insertBefore(newEl, refEl) {
    refEl.parentNode.insertBefore(newEl, refEl);
  }

  /* Inject a block element immediately after a reference element */
  function insertAfter(newEl, refEl) {
    var next = refEl.nextSibling;
    if (next) refEl.parentNode.insertBefore(newEl, next);
    else refEl.parentNode.appendChild(newEl);
  }

  /* ─── Core constructor ────────────────────────────────────────────── */
  function Table(cfg) {
    if (!(this instanceof Table)) return new Table(cfg);

    /* Validate required */
    if (!cfg.tbodyId) throw new Error("SS.Table: tbodyId is required");
    if (!cfg.url) throw new Error("SS.Table: url is required");
    if (!cfg.cols || !cfg.cols.length)
      throw new Error("SS.Table: cols is required");
    if (typeof cfg.rowFn !== "function")
      throw new Error("SS.Table: rowFn must be a function");

    this._cfg = cfg;
    this._data = []; // raw data from server
    this._filtered = []; // after search
    this._page = 1;
    this._sortCol = null; // key string
    this._sortDir = "asc"; // 'asc'|'desc'
    this._query = "";

    this._tbodyId = cfg.tbodyId;
    this._tbody = el(cfg.tbodyId);
    if (!this._tbody)
      throw new Error("SS.Table: element #" + cfg.tbodyId + " not found");

    this._pageSize = cfg.pageSize !== undefined ? cfg.pageSize : 15;
    this._colSpan = cfg.colSpan || cfg.cols.length + 1;

    this._searchKeys =
      cfg.searchKeys ||
      cfg.cols
        .filter(function (c) {
          return !c.noSearch && c.key;
        })
        .map(function (c) {
          return c.key;
        });

    /* Build injected UI */
    this._buildSearchBar();
    this._buildHeaders();
    this._buildPager();

    /* Initial fetch */
    this.reload();

    return this;
  }

  /* ── Build injected search bar ─────────────────────────────────────── */
  Table.prototype._buildSearchBar = function () {
    var cfg = this._cfg;

    /* Use caller-provided input if given */
    if (cfg.searchId) {
      this._searchInput = el(cfg.searchId);
    } else {
      /* Inject a search bar into the ss-table-wrap, above the table */
      var wrap = tableWrap(this._tbody);
      if (!wrap) return;

      var searchWrap = document.createElement("div");
      searchWrap.style.cssText = SEARCH_WRAP_STYLE;

      var inputId = uid("ss-search");
      searchWrap.innerHTML = [
        '<div style="position:relative;flex:1;max-width:320px;">',
        '<i data-lucide="search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#94a3b8;pointer-events:none;"></i>',
        '<input id="',
        inputId,
        '" type="text" placeholder="',
        cfg.searchPlaceholder || "Search\u2026",
        '" class="ss-input"',
        ' style="padding-left:34px;height:36px;font-size:13px;"',
        " onfocus=\"this.style.borderColor='#7f94f7';this.style.boxShadow='0 0 0 3px rgba(127,148,247,.15)'\"",
        " onblur=\"this.style.borderColor='#e2e8f0';this.style.boxShadow='none'\"",
        ">",
        "</div>",
      ].join("");

      var table = tableOf(this._tbody);
      insertBefore(searchWrap, table);

      this._searchInput = el(inputId);
    }

    if (!this._searchInput) return;

    var self = this;
    this._searchInput.addEventListener("input", function () {
      self._query = this.value.trim().toLowerCase();
      self._page = 1;
      self._applyFilter();
      self._render();
    });
  };

  /* ── Build sortable <thead> ────────────────────────────────────────── */
  Table.prototype._buildHeaders = function () {
    var self = this;
    var cols = this._cfg.cols;
    var thead = theadOf(this._tbody);
    if (!thead) return;

    /* Replace existing header row with sortable one */
    var tr = thead.querySelector("tr");
    if (!tr) return;

    /* Preserve any header cells the view has explicitly marked as static
     * (e.g. a bulk-select checkbox column that isn't part of `cols`).
     * Without this, rebuilding the row below would silently destroy them —
     * along with any elements/ids inside them — and shift every managed
     * column one position out of alignment with its matching <td>. */
    var staticHtml = Array.prototype.slice
      .call(tr.querySelectorAll("th[data-ss-static]"))
      .map(function (th) {
        return th.outerHTML;
      })
      .join("");

    var cells = "";
    cols.forEach(function (col, i) {
      var sortable = col.sortable !== false && col.key;
      var align = col.align || (i === cols.length - 1 ? "right" : "left");
      var extraStyle = col.width ? "width:" + col.width + ";" : "";
      var thId = uid("ss-th");

      cells += [
        '<th id="',
        thId,
        '" style="',
        HEADER_STYLE,
        ";text-align:",
        align,
        ";",
        extraStyle,
        sortable ? "cursor:pointer;" : "",
        '" data-key="',
        col.key || "",
        '" data-sortable="',
        sortable ? "1" : "0",
        '">',
        esc(col.label),
        sortable
          ? '<span id="' +
            thId +
            '-icon" style="' +
            SORT_ICON_STYLE +
            '">' +
            svgSort() +
            "</span>"
          : "",
        "</th>",
      ].join("");
    });

    /* Static cells (if any) stay first, in their original DOM order;
     * SS.Table-managed cells follow. */
    tr.innerHTML = staticHtml + cells;

    /* Attach click handlers */
    cols.forEach(function (col) {
      if (col.sortable === false || !col.key) return;
      var th = tr.querySelector('[data-key="' + col.key + '"]');
      if (!th) return;
      th.addEventListener("click", function () {
        if (self._sortCol === col.key) {
          self._sortDir = self._sortDir === "asc" ? "desc" : "asc";
        } else {
          self._sortCol = col.key;
          self._sortDir = "asc";
        }
        self._updateSortIcons();
        self._applyFilter();
        self._render();
      });
      th.addEventListener("mouseover", function () {
        this.style.background = "#f1f5f9";
      });
      th.addEventListener("mouseout", function () {
        this.style.background = "#f8fafc";
      });
    });
  };

  /* ── Build pager controls ──────────────────────────────────────────── */
  Table.prototype._buildPager = function () {
    if (this._pageSize === 0) return; /* pagination disabled */

    var cfg = this._cfg;

    if (cfg.pagerId) {
      this._pagerEl = el(cfg.pagerId);
    } else {
      var wrap = tableWrap(this._tbody);
      if (!wrap) return;

      var pagerEl = document.createElement("div");
      pagerEl.id = uid("ss-pager");
      pagerEl.style.cssText = PAGER_WRAP_STYLE;
      wrap.appendChild(pagerEl);
      this._pagerEl = pagerEl;
    }
  };

  /* ── Fetch from server ─────────────────────────────────────────────── */
  Table.prototype.reload = function () {
    var self = this;
    self._showLoading();

    $.get(this._cfg.url, function (res) {
      self._data = res.data || [];
      if (typeof self._cfg.onLoad === "function") {
        self._cfg.onLoad(self._data);
      }
      self._applyFilter();
      self._render();
    }).fail(function () {
      self._tbody.innerHTML = self._stateRow(
        "alert-circle",
        "Failed to load data. Try refreshing.",
        "#ef4444",
      );
      if (typeof window.ssToast === "function") {
        ssToast("Failed to load data.", "error");
      }
    });
  };

  /* ── Re-render current data (no fetch) ─────────────────────────────── */
  Table.prototype.refresh = function () {
    this._applyFilter();
    this._render();
  };

  /* ── Search + sort ─────────────────────────────────────────────────── */
  Table.prototype._applyFilter = function () {
    var self = this;
    var query = this._query;
    var keys = this._searchKeys;

    /* Search */
    var filtered = query
      ? this._data.filter(function (row) {
          return keys.some(function (k) {
            return strIncludes(row[k], query);
          });
        })
      : this._data.slice();

    /* Sort */
    if (this._sortCol) {
      var col = this._sortCol;
      var dir = this._sortDir === "asc" ? 1 : -1;
      filtered.sort(function (a, b) {
        var av = toSortVal(a[col]);
        var bv = toSortVal(b[col]);
        /* Numeric sort if both look like numbers */
        var an = parseFloat(av),
          bn = parseFloat(bv);
        if (!isNaN(an) && !isNaN(bn)) return (an - bn) * dir;
        return av.localeCompare(bv) * dir;
      });
    }

    this._filtered = filtered;

    /* Clamp page */
    var maxPage = Math.max(
      1,
      Math.ceil(filtered.length / (this._pageSize || filtered.length)),
    );
    if (this._page > maxPage) this._page = maxPage;
  };

  /* ── Render tbody + pager ──────────────────────────────────────────── */
  Table.prototype._render = function () {
    var cfg = this._cfg;
    var filtered = this._filtered;
    var pageSize = this._pageSize;

    /* Update count */
    if (cfg.countId) setText(cfg.countId, filtered.length);

    /* Slice for current page */
    var rows;
    if (pageSize > 0) {
      var start = (this._page - 1) * pageSize;
      rows = filtered.slice(start, start + pageSize);
    } else {
      rows = filtered;
    }

    /* Render rows or empty state */
    if (filtered.length === 0) {
      this._tbody.innerHTML = this._stateRow(
        cfg.emptyIcon || "inbox",
        this._query
          ? "No results for \u201c" + this._query + "\u201d"
          : cfg.emptyText || "No records found",
        "#cbd5e1",
      );
    } else {
      this._tbody.innerHTML = rows.map(cfg.rowFn).join("");
    }

    /* Re-render Lucide icons injected by rowFn */
    if (typeof lucide !== "undefined") lucide.createIcons();

    /* Pager */
    this._renderPager(filtered.length);

    /* Callback */
    if (typeof cfg.onRender === "function") cfg.onRender(rows);
  };

  /* ── Pager HTML ────────────────────────────────────────────────────── */
  Table.prototype._renderPager = function (total) {
    if (!this._pagerEl || this._pageSize === 0) return;

    var pageSize = this._pageSize;
    var page = this._page;
    var totalPages = Math.max(1, Math.ceil(total / pageSize));
    var self = this;

    var start = total === 0 ? 0 : (page - 1) * pageSize + 1;
    var end = Math.min(page * pageSize, total);

    var infoHtml = [
      '<span style="font-size:13px;color:#64748b;">',
      'Showing <b style="color:#334155;">',
      start,
      "\u2013",
      end,
      "</b>",
      ' of <b style="color:#334155;">',
      total,
      "</b>",
      "</span>",
    ].join("");

    var prevDisabled = page <= 1;
    var nextDisabled = page >= totalPages;

    var btnStyle = function (disabled) {
      return PAGER_BTN_BASE + (disabled ? ";opacity:.4;cursor:default;" : "");
    };

    var pagerHtml = [
      '<div style="display:flex;align-items:center;gap:6px;">',
      '<button id="',
      uid("ss-prev"),
      '" style="',
      btnStyle(prevDisabled),
      '"',
      prevDisabled ? " disabled" : "",
      ">",
      '<i data-lucide="chevron-left" style="width:14px;height:14px;"></i>',
      "Prev</button>",

      /* Page pills — show up to 5 */
      _pagePills(page, totalPages),

      '<button id="',
      uid("ss-next"),
      '" style="',
      btnStyle(nextDisabled),
      '"',
      nextDisabled ? " disabled" : "",
      ">",
      'Next<i data-lucide="chevron-right" style="width:14px;height:14px;"></i>',
      "</button>",
      "</div>",
    ].join("");

    this._pagerEl.innerHTML = infoHtml + pagerHtml;

    /* Wire prev/next */
    var prevBtn = this._pagerEl.querySelector('[id^="ss-prev"]');
    var nextBtn = this._pagerEl.querySelector('[id^="ss-next"]');
    if (prevBtn && !prevDisabled) {
      prevBtn.addEventListener("click", function () {
        self._page--;
        self._render();
      });
      prevBtn.addEventListener("mouseover", function () {
        this.style.background = "#f1f5f9";
      });
      prevBtn.addEventListener("mouseout", function () {
        this.style.background = "#fff";
      });
    }
    if (nextBtn && !nextDisabled) {
      nextBtn.addEventListener("click", function () {
        self._page++;
        self._render();
      });
      nextBtn.addEventListener("mouseover", function () {
        this.style.background = "#f1f5f9";
      });
      nextBtn.addEventListener("mouseout", function () {
        this.style.background = "#fff";
      });
    }

    /* Wire page pills */
    var self2 = this;
    this._pagerEl.querySelectorAll("[data-pg]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        self2._page = parseInt(this.dataset.pg, 10);
        self2._applyFilter();
        self2._render();
      });
      if (!btn.disabled) {
        btn.addEventListener("mouseover", function () {
          this.style.background = "#f1f5f9";
        });
        btn.addEventListener("mouseout", function () {
          this.style.background =
            this.dataset.pg == self2._page ? "#5c6af0" : "#fff";
          this.style.color =
            this.dataset.pg == self2._page ? "#fff" : "#334155";
        });
      }
    });

    if (typeof lucide !== "undefined") lucide.createIcons();
  };

  /* ── Page pills helper ─────────────────────────────────────────────── */
  function _pagePills(current, total) {
    if (total <= 1) return "";
    var pages = [];
    /* Always show first, last, current, and neighbours */
    var show = {};
    [1, total, current, current - 1, current + 1].forEach(function (p) {
      if (p >= 1 && p <= total) show[p] = true;
    });
    /* Build sorted list with ellipsis gaps */
    var keys = Object.keys(show)
      .map(Number)
      .sort(function (a, b) {
        return a - b;
      });
    var html = "";
    var prev = 0;
    keys.forEach(function (p) {
      if (prev && p - prev > 1) {
        html +=
          '<span style="font-size:13px;color:#94a3b8;padding:0 2px;">\u2026</span>';
      }
      var active = p === current;
      html += [
        '<button data-pg="',
        p,
        '" style="',
        PAGER_BTN_BASE,
        ";min-width:32px;padding:5px 8px;",
        active ? "background:#5c6af0;color:#fff;border-color:#5c6af0;" : "",
        '"',
        active ? " disabled" : "",
        ">",
        p,
        "</button>",
      ].join("");
      prev = p;
    });
    return html;
  }

  /* ── Sort icon SVG helpers ─────────────────────────────────────────── */
  function svgSort() {
    return (
      '<svg width="10" height="10" viewBox="0 0 10 10" fill="none" style="display:inline-block;">' +
      '<path d="M3 4L5 2L7 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>' +
      '<path d="M3 6L5 8L7 6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>' +
      "</svg>"
    );
  }
  function svgSortAsc() {
    return (
      '<svg width="10" height="10" viewBox="0 0 10 10" fill="none" style="display:inline-block;">' +
      '<path d="M3 4L5 2L7 4" stroke="#5c6af0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
      '<path d="M3 6L5 8L7 6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" opacity=".3"/>' +
      "</svg>"
    );
  }
  function svgSortDesc() {
    return (
      '<svg width="10" height="10" viewBox="0 0 10 10" fill="none" style="display:inline-block;">' +
      '<path d="M3 4L5 2L7 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" opacity=".3"/>' +
      '<path d="M3 6L5 8L7 6" stroke="#5c6af0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
      "</svg>"
    );
  }

  Table.prototype._updateSortIcons = function () {
    var self = this;
    this._cfg.cols.forEach(function (col) {
      if (!col.key || col.sortable === false) return;
      var iconEl = document.getElementById("ss-th-" + col.key + "-icon");
      /* IDs are generated; scan by data-key instead */
      var thead = theadOf(self._tbody);
      if (!thead) return;
      var th = thead.querySelector('[data-key="' + col.key + '"]');
      if (!th) return;
      var span = th.querySelector('[id$="-icon"]');
      if (!span) return;
      if (col.key === self._sortCol) {
        span.style.opacity = "1";
        span.innerHTML = self._sortDir === "asc" ? svgSortAsc() : svgSortDesc();
      } else {
        span.style.opacity = ".4";
        span.innerHTML = svgSort();
      }
    });
  };

  /* ── Loading state ─────────────────────────────────────────────────── */
  Table.prototype._showLoading = function () {
    var cfg = this._cfg;
    this._tbody.innerHTML = this._stateRow(
      "loader-2",
      cfg.loadingText || "Loading\u2026",
      "#cbd5e1",
    );
    if (typeof lucide !== "undefined") lucide.createIcons();
  };

  /* ── Generic state row ─────────────────────────────────────────────── */
  Table.prototype._stateRow = function (icon, text, color) {
    return [
      '<tr><td colspan="',
      this._colSpan,
      '" style="padding:48px 16px;text-align:center;">',
      '<div style="display:flex;flex-direction:column;align-items:center;gap:8px;">',
      '<i data-lucide="',
      icon,
      '" style="width:22px;height:22px;color:',
      color,
      ';"></i>',
      '<span style="font-size:14px;color:',
      color,
      ';">',
      esc(text),
      "</span>",
      "</div></td></tr>",
    ].join("");
  };

  /* ── HTML escape ───────────────────────────────────────────────────── */
  function esc(s) {
    return String(s || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  /* ── Expose ────────────────────────────────────────────────────────── */
  root.SS = root.SS || {};
  root.SS.Table = function (cfg) {
    return new Table(cfg);
  };
})(window);
<?php
require_once 'version.php';
require_once 'process.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="QuoteCalc — Tính thuế VAT (8%, 10%), đọc số tiền thành chữ tiếng Việt và tiếng Anh với đề xuất làm tròn thông minh.">
  <title>QuoteCalc — Tính Thuế VAT &amp; Đọc Số Tiền</title>
  <link rel="stylesheet" href="styles.css?v=<?php echo APP_VERSION; ?>">
</head>
<body>
<!-- Mặc định ẩn nội dung cho tới khi check theme xong để chống chớp trắng/đen -->
<script>
  if (localStorage.getItem('theme') === 'light' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
</script>
<div class="app-wrap">

  <!-- ── Header ── -->
  <header class="app-header">
    <div class="brand">
      <div class="brand-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      </div>
      <div>
        <div class="brand-name">QuoteCalc</div>
        <div class="brand-tagline">Tính VAT &amp; đọc số tiền</div>
      </div>
    </div>
    <div style="display: flex; align-items: center; gap: var(--sp-3);">
      <button id="theme-toggle" class="btn-theme" aria-label="Đổi giao diện Sáng/Tối">
        <svg class="icon-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg class="icon-moon" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
      <span class="version-badge">v<?php echo APP_VERSION; ?></span>
    </div>
  </header>

  <!-- ── Main Grid ── -->
  <div class="app-grid" id="content-start">

    <!-- ── Calculator Column ── -->
    <main class="calc-panel">

      <!-- Form Card -->
      <div class="card form-card">
        <form id="calcForm" novalidate>

          <!-- Amount input -->
          <div class="amount-wrap">
            <label class="amount-label" for="amount">Số tiền (VNĐ)</label>
            <input
              type="text"
              id="amount"
              class="amount-input"
              placeholder="0"
              autocomplete="off"
              inputmode="numeric"
              autofocus
              aria-label="Nhập số tiền"
            >
            <!-- Inline suggestion chips -->
            <div id="suggestion-chips" class="suggestion-chips" aria-label="Gợi ý nhanh"></div>
          </div>

          <!-- VAT + toggle + suggestion + button — cùng 1 dòng -->
          <div class="controls-row">
            <div class="vat-segment" role="radiogroup" aria-label="Thuế suất VAT">
              <label class="vat-option">
                <input type="radio" name="vat_rate" value="0.08" checked>
                <span>8%</span>
              </label>
              <label class="vat-option">
                <input type="radio" name="vat_rate" value="0.10">
                <span>10%</span>
              </label>
            </div>

            <label class="toggle-wrap" for="is_tax_included">
              <input type="checkbox" id="is_tax_included">
              <span class="toggle-track" aria-hidden="true"></span>
              <span class="toggle-label">Đã gồm VAT</span>
            </label>

            <!-- Spacer -->
            <div class="controls-spacer"></div>

            <!-- Suggestion inline (hiện khi có kết quả) -->
            <div id="suggestion-box" class="suggest-inline" role="status" aria-live="polite">
              <span class="suggest-inline-amount" id="sug-amount"></span>
              <span class="suggest-inline-diff" id="sug-diff"></span>
              <button type="button" class="btn-apply-inline" id="btn-apply-suggestion">Dùng</button>
            </div>

            <button type="submit" class="btn-calc" id="btn-calc">Tính nhanh</button>
          </div>
        </form>
      </div>

      <!-- Error -->
      <div id="error-msg" class="error-msg" role="alert" aria-live="polite"></div>

      <!-- Empty state -->
      <div id="empty-state" class="empty-state">
        <div class="empty-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M9 7H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2v-3"/><path d="M9 15h3l8.5-8.5a1.5 1.5 0 0 0-3-3L9 12v3z"/></svg>
        </div>
        <h2>Nhập số tiền để bắt đầu</h2>
        <p>Kết quả VAT, đề xuất làm tròn và đọc số sẽ hiện ngay bên dưới.</p>
      </div>

      <!-- Result area -->
      <div id="result-area">

        <!-- Metrics row -->
        <div class="metrics-row">
          <div class="metric-card">
            <div class="metric-head">
              <span class="metric-label">Trước thuế</span>
              <button class="btn-copy" aria-label="Copy số trước thuế" onclick="copyToClip('res-pre', this)">
                <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              </button>
            </div>
            <strong class="metric-value" id="res-pre"></strong>
          </div>

          <div class="metric-card">
            <div class="metric-head">
              <span class="metric-label" id="lbl-vat">VAT (8%)</span>
              <button class="btn-copy" aria-label="Copy thuế VAT" onclick="copyToClip('res-vat', this)">
                <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              </button>
            </div>
            <strong class="metric-value" id="res-vat"></strong>
          </div>

          <div class="metric-card metric-card--primary">
            <div class="metric-head">
              <span class="metric-label">Tổng thanh toán</span>
              <button class="btn-copy" aria-label="Copy tổng thanh toán" onclick="copyToClip('res-post', this)">
                <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              </button>
            </div>
            <strong class="metric-value" id="res-post"></strong>
          </div>
        </div>

        <!-- Text output card -->
        <div class="output-card">
          <div class="card-label">Đọc số thành chữ</div>
          <div class="output-row">
            <span class="output-tag">VI</span>
            <div class="output-text" id="txt-sentence"></div>
            <button class="btn-copy" aria-label="Copy tiếng Việt" onclick="copyToClip('txt-sentence', this)">
              <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            </button>
          </div>
          <div class="output-row">
            <span class="output-tag">EN</span>
            <div class="output-text" id="txt-en"></div>
            <button class="btn-copy" aria-label="Copy English" onclick="copyToClip('txt-en', this)">
              <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            </button>
          </div>
        </div>

      </div><!-- /#result-area -->
    </main>

    <!-- ── History Sidebar ── -->
    <aside class="history-sidebar" aria-label="Lịch sử tính toán">
      <div class="history-header">
        <span class="history-title">Lịch sử (10 gần nhất)</span>
        <button class="btn-clear" id="btn-clear-history" aria-label="Xóa lịch sử">Xóa</button>
      </div>
      <div class="history-body">
        <div class="history-list" id="history-list">
          <div class="history-empty">Chưa có lịch sử</div>
        </div>
      </div>
    </aside>

  </div><!-- /.app-grid -->
</div><!-- /.app-wrap -->

<script src="script.js?v=<?php echo APP_VERSION; ?>"></script>
</body>
</html>

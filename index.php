<?php
require_once 'version.php';
require_once 'process.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Công cụ Kế toán Online: Đọc số tiền thành chữ, Tính thuế VAT (8%, 10%), Tính ngược và Đề xuất làm tròn số tiền thông minh.">
    <title>QuoteCalc - Tính Thuế VAT & Đọc Số Tiền</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo APP_VERSION; ?>">
</head>
<body>
<main class="page-wrap">
    <section class="single-workspace">
        <header class="workspace-header">
            <div class="workspace-brand">
                <div class="hero-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="presentation">
                        <rect x="5" y="2.5" width="14" height="19" rx="2.5"></rect>
                        <rect x="7.5" y="5" width="9" height="3.2" rx="1"></rect>
                        <circle cx="8.7" cy="11" r="1"></circle>
                        <circle cx="12" cy="11" r="1"></circle>
                        <circle cx="15.3" cy="11" r="1"></circle>
                        <circle cx="8.7" cy="14.3" r="1"></circle>
                        <circle cx="12" cy="14.3" r="1"></circle>
                        <circle cx="15.3" cy="14.3" r="1"></circle>
                        <path d="M8.4 17.9h7.2"></path>
                    </svg>
                </div>
                <div class="brand-copy">
                    <p class="hero-kicker">QuoteCalc Workspace</p>
                    <h1 class="hero-title">QuoteCalc<span>+</span></h1>
                    <p class="hero-subtitle">Tính VAT và đọc số tiền trong một màn hình gọn, đủ dùng cho thao tác nhanh.</p>
                </div>
            </div>
            <div class="workspace-meta">
                <span class="version-chip">Phiên bản <?php echo APP_VERSION; ?></span>
            </div>
        </header>

        <form id="calcForm" class="control-bar">
            <div class="field amount-field">
                <label class="input-label" for="amount">Số tiền</label>
                <input type="text" id="amount" placeholder="VD: 11.500.000" autocomplete="off" autofocus>
                <div id="suggestion-dropdown" class="suggestion-dropdown"></div>
            </div>

            <div class="control-group compact-controls">
                <span class="input-label">VAT & chế độ</span>
                <div class="control-inline">
                    <div class="vat-toggle" role="radiogroup" aria-label="VAT">
                        <label class="vat-option">
                            <input type="radio" name="vat_rate" value="0.08" checked> 8%
                        </label>
                        <label class="vat-option">
                            <input type="radio" name="vat_rate" value="0.10"> 10%
                        </label>
                    </div>

                    <label class="checkbox-group">
                        <input type="checkbox" id="is_tax_included">
                        <span>Đã gồm VAT</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-submit">Xem Kết Quả</button>
        </form>

        <div class="workspace-layout" id="content-start">
            <section class="workspace-main">
                <div id="error-msg" class="error-msg"></div>

                <div id="empty-state" class="empty-state panel">
                    <p class="panel-kicker">Ready</p>
                    <h2 class="panel-title">Nhập số tiền để xem kết quả</h2>
                    <p class="empty-copy">Tất cả thông tin chính sẽ hiển thị ngay tại đây: trước thuế, VAT, tổng tiền, gợi ý làm tròn và nội dung copy.</p>
                </div>

                <div id="result-area">
                    <section class="panel metrics-panel">
                        <div class="metrics-grid">
                            <div class="metric-card">
                                <div class="metric-head">
                                    <span class="metric-label">Trước thuế</span>
                                    <button class="btn-copy-inline copy-metric" aria-label="Copy trước thuế" onclick="copyToClip('res-pre', this)">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h10v10H9z"></path><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path></svg>
                                    </button>
                                </div>
                                <strong class="metric-value" id="res-pre"></strong>
                            </div>
                            <div class="metric-card">
                                <div class="metric-head">
                                    <span class="metric-label" id="lbl-vat">VAT (8%)</span>
                                    <button class="btn-copy-inline copy-metric" aria-label="Copy VAT" onclick="copyToClip('res-vat', this)">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h10v10H9z"></path><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path></svg>
                                    </button>
                                </div>
                                <strong class="metric-value" id="res-vat"></strong>
                            </div>
                            <div class="metric-card metric-card-strong">
                                <div class="metric-head">
                                    <span class="metric-label">Tổng thanh toán</span>
                                    <button class="btn-copy-inline copy-metric" aria-label="Copy tổng thanh toán" onclick="copyToClip('res-post', this)">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h10v10H9z"></path><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path></svg>
                                    </button>
                                </div>
                                <strong class="metric-value" id="res-post"></strong>
                            </div>
                        </div>
                    </section>

                    <div class="content-grid">
                        <section id="suggestion-box" class="panel suggestion-panel">
                            <p class="panel-kicker">Đề xuất</p>
                            <h2 class="panel-title">Làm tròn thông minh</h2>
                            <span class="suggest-highlight" id="sug-amount"></span>
                            <div class="suggest-meta">Giảm <span id="sug-diff"></span> so với số nhập.</div>
                            <div class="suggest-stat-row">
                                <div class="suggest-stat">
                                    <span>Trước thuế</span>
                                    <strong id="sug-pre"></strong>
                                </div>
                                <div class="suggest-stat">
                                    <span>VAT</span>
                                    <strong id="sug-vat"></strong>
                                </div>
                            </div>
                            <button type="button" class="btn-apply" id="btn-apply-suggestion">Áp dụng gợi ý</button>
                        </section>

                        <section class="panel output-panel">
                            <p class="panel-kicker">Đọc số nhanh</p>
                            <h2 class="panel-title">2 phiên bản dùng nhiều nhất</h2>
                            <div class="copy-list">
                                <div class="copy-line">
                                    <div class="text-box" id="txt-sentence"></div>
                                    <button class="btn-copy-inline" aria-label="Copy viết hoa đầu câu" onclick="copyToClip('txt-sentence', this)">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h10v10H9z"></path><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path></svg>
                                    </button>
                                </div>
                                <div class="copy-line">
                                    <div class="text-box" id="txt-en"></div>
                                    <button class="btn-copy-inline" aria-label="Copy tiếng Anh" onclick="copyToClip('txt-en', this)">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h10v10H9z"></path><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </section>

            <aside class="history-panel visible">
                <div class="history-header">
                    <div class="history-title">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm.5 5H11v6l5.25 3.15.75-1.23-4.5-2.67z"></path></svg>
                        <div>
                            <p class="panel-kicker">Lịch sử</p>
                            <h2 class="panel-title">Gần đây</h2>
                        </div>
                    </div>
                    <button class="btn-clear-history" id="btn-clear-history" style="display: none;">Xóa</button>
                </div>
                <p class="history-note">Lưu tối đa 50 kết quả và cuộn trong khung này.</p>
                <div class="history-list" id="history-list">
                    <div class="history-empty">Chưa có lịch sử</div>
                </div>
            </aside>
        </div>
    </section>
</main>

<script src="script.js?v=<?php echo APP_VERSION; ?>"></script>

</body>
</html>

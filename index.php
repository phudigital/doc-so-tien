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
    <section class="ledger-shell">
        <header class="ledger-header">
            <div class="ledger-brand">
                <p class="eyebrow">Swiss Ledger</p>
                <div class="brand-row">
                    <h1 class="hero-title">QuoteCalc<span>+</span></h1>
                    <span class="version-chip">Phiên bản <?php echo APP_VERSION; ?></span>
                </div>
                <p class="hero-subtitle">Tính VAT, làm tròn gợi ý và copy kết quả trong một không gian làm việc gọn.</p>
            </div>
        </header>

        <div class="ledger-grid" id="content-start">
            <section class="ledger-main">
                <form id="calcForm" class="ledger-form">
                    <div class="field amount-field">
                        <label class="input-label" for="amount">Số tiền</label>
                        <input type="text" id="amount" placeholder="VD: 11.500.000" autocomplete="off" autofocus>
                        <div id="suggestion-dropdown" class="suggestion-dropdown"></div>
                    </div>

                    <div class="field compact-field">
                        <span class="input-label">Thiết lập VAT</span>
                        <div class="compact-stack">
                            <div class="vat-segment" role="radiogroup" aria-label="VAT">
                                <label class="vat-option">
                                    <input type="radio" name="vat_rate" value="0.08" checked>
                                    <span>8%</span>
                                </label>
                                <label class="vat-option">
                                    <input type="radio" name="vat_rate" value="0.10">
                                    <span>10%</span>
                                </label>
                            </div>

                            <label class="checkbox-group">
                                <input type="checkbox" id="is_tax_included">
                                <span>Đã gồm VAT</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Tính nhanh</button>
                </form>

                <div id="error-msg" class="error-msg"></div>

                <div id="empty-state" class="empty-state panel">
                    <p class="panel-kicker">Ready</p>
                    <h2 class="panel-title">Nhập số tiền để tạo bảng tính</h2>
                    <p class="empty-copy">Ba chỉ số chính, gợi ý làm tròn và hai dòng đọc số sẽ xuất hiện ngay trong cùng màn hình này.</p>
                </div>

                <div id="result-area">
                    <section class="panel metrics-strip">
                        <div class="metrics-grid">
                            <article class="metric-card">
                                <div class="metric-head">
                                    <span class="metric-label">Trước thuế</span>
                                    <button class="btn-copy-inline copy-metric" aria-label="Copy trước thuế" onclick="copyToClip('res-pre', this)">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h10v10H9z"></path><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path></svg>
                                    </button>
                                </div>
                                <strong class="metric-value" id="res-pre"></strong>
                            </article>

                            <article class="metric-card">
                                <div class="metric-head">
                                    <span class="metric-label" id="lbl-vat">VAT (8%)</span>
                                    <button class="btn-copy-inline copy-metric" aria-label="Copy VAT" onclick="copyToClip('res-vat', this)">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h10v10H9z"></path><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path></svg>
                                    </button>
                                </div>
                                <strong class="metric-value" id="res-vat"></strong>
                            </article>

                            <article class="metric-card metric-card-strong">
                                <div class="metric-head">
                                    <span class="metric-label">Tổng thanh toán</span>
                                    <button class="btn-copy-inline copy-metric" aria-label="Copy tổng thanh toán" onclick="copyToClip('res-post', this)">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h10v10H9z"></path><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path></svg>
                                    </button>
                                </div>
                                <strong class="metric-value" id="res-post"></strong>
                            </article>
                        </div>
                    </section>

                    <div class="detail-strip">
                        <section id="suggestion-box" class="panel suggestion-panel">
                            <p class="panel-kicker">Đề xuất</p>
                            <h2 class="panel-title">Làm tròn theo đơn vị nghìn</h2>
                            <span class="suggest-highlight" id="sug-amount"></span>
                            <div class="suggest-meta">Chênh lệch <span id="sug-diff"></span> so với số nhập.</div>
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
                            <div class="output-head">
                                <div>
                                    <p class="panel-kicker">Đọc số nhanh</p>
                                    <h2 class="panel-title">Copy hai định dạng cần dùng</h2>
                                </div>
                            </div>
                            <div class="copy-rows">
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

            <aside class="history-panel ledger-sidebar visible">
                <div class="history-header">
                    <div>
                        <p class="panel-kicker">Recent</p>
                        <h2 class="panel-title">Lịch sử tính nhanh</h2>
                    </div>
                    <button class="btn-clear-history" id="btn-clear-history" style="display: none;">Xóa</button>
                </div>
                <p class="history-note">Lưu tối đa 50 kết quả gần đây và cuộn ngay trong khung này.</p>
                <div class="history-scroll-shell">
                    <div class="history-list" id="history-list">
                        <div class="history-empty">Chưa có lịch sử</div>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</main>

<script src="script.js?v=<?php echo APP_VERSION; ?>"></script>

</body>
</html>

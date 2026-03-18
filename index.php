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
    <section class="hero">
        <div class="hero-inner console-shell">
            <div class="brand-bar">
                <div class="brand-lockup">
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
                    <div class="hero-copy">
                        <p class="hero-kicker">Invoice Console</p>
                        <h1 class="hero-title">QuoteCalc<span>+</span></h1>
                        <p class="hero-subtitle">Tính VAT, kiểm tra số liệu và đọc số tiền theo phong cách bảng điều khiển hóa đơn.</p>
                    </div>
                </div>
                <div class="hero-meta">
                    <span class="version-chip">Phiên bản <?php echo APP_VERSION; ?></span>
                </div>
            </div>

            <form id="calcForm" class="console-grid">
                <div class="console-panel input-console">
                    <div class="panel-header">
                        <p class="panel-kicker">Input Console</p>
                        <h2 class="panel-title">Nhập giá trị cần kiểm tra</h2>
                    </div>
                    <div class="field amount-field">
                        <label class="input-label" for="amount">Số tiền</label>
                        <input type="text" id="amount" placeholder="VD: 11.500.000" autocomplete="off" autofocus>
                        <div id="suggestion-dropdown" class="suggestion-dropdown"></div>
                    </div>
                    <div class="helper-row">
                        <span class="helper-chip">Trước thuế / sau thuế đều hỗ trợ</span>
                        <span class="helper-chip">Tự gợi ý mức tiền chẵn nghìn</span>
                    </div>
                </div>

                <div class="console-panel status-console">
                    <div class="panel-header">
                        <p class="panel-kicker">Quick Status</p>
                        <h2 class="panel-title">Chọn chế độ tính</h2>
                    </div>
                    <div class="status-stack">
                        <div class="status-block">
                            <span class="status-label">VAT</span>
                            <div class="vat-group">
                                <label class="vat-option">
                                    <input type="radio" name="vat_rate" value="0.08" checked> 8%
                                </label>
                                <label class="vat-option">
                                    <input type="radio" name="vat_rate" value="0.10"> 10%
                                </label>
                            </div>
                        </div>

                        <label class="checkbox-group">
                            <input type="checkbox" id="is_tax_included">
                            <span>Đã bao gồm VAT</span>
                        </label>
                    </div>
                    <p class="hero-note">Console sẽ ưu tiên gợi ý mức tiền phù hợp để tổng tiền, trước thuế và VAT dễ kiểm tra hơn khi lên hóa đơn.</p>
                    <button type="submit" class="btn-submit">Xem Kết Quả</button>
                </div>
            </form>
        </div>
    </section>

    <section class="content" id="content-start">
        <div class="content-inner">
            <div id="error-msg" class="error-msg"></div>

            <div class="workspace-grid">
                <div class="workspace-main">
                    <div id="empty-state" class="empty-state panel">
                        <p class="panel-kicker">Ready</p>
                        <h2 class="panel-title">Nhập dữ liệu để mở bảng kết quả</h2>
                        <p class="empty-copy">Khi bạn nhập số tiền và chọn trạng thái VAT, console sẽ hiển thị ngay số trước thuế, VAT, tổng thanh toán và khu copy nội dung đọc số.</p>
                    </div>

                    <div id="result-area">
                        <div class="panel summary-panel">
                            <div class="panel-header">
                                <p class="panel-kicker">Result Summary</p>
                                <h2 class="panel-title">Tổng hợp số liệu thanh toán</h2>
                            </div>
                            <div class="summary-grid">
                                <div class="summary-card">
                                    <span class="summary-label">Trước thuế</span>
                                    <strong class="summary-value" id="res-pre"></strong>
                                </div>
                                <div class="summary-card">
                                    <span class="summary-label" id="lbl-vat">VAT (8%)</span>
                                    <strong class="summary-value" id="res-vat"></strong>
                                </div>
                                <div class="summary-card summary-card-strong">
                                    <span class="summary-label">Tổng thanh toán</span>
                                    <strong class="summary-value" id="res-post"></strong>
                                </div>
                            </div>
                        </div>

                        <div class="split-grid">
                            <div id="suggestion-box" class="panel suggestion-panel">
                                <div class="panel-header">
                                    <p class="panel-kicker">Rounding Suggestion</p>
                                    <h2 class="panel-title">Đề xuất làm tròn thông minh</h2>
                                </div>
                                <p class="suggest-copy">Mức tiền phù hợp hơn để ra số đẹp cho hóa đơn:</p>
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
                            </div>

                            <div class="panel output-panel">
                                <div class="panel-header">
                                    <p class="panel-kicker">Text Output</p>
                                    <h2 class="panel-title">Đọc số tiền và copy nhanh</h2>
                                </div>
                                <div class="copy-grid">
                                    <div class="copy-row">
                                        <label>Viết hoa đầu câu</label>
                                        <div class="text-box" id="txt-sentence"></div>
                                        <button class="btn-copy" onclick="copyToClip('txt-sentence', this)">COPY</button>
                                    </div>
                                    <div class="copy-row">
                                        <label>Viết hoa đầu mỗi từ</label>
                                        <div class="text-box" id="txt-title"></div>
                                        <button class="btn-copy" onclick="copyToClip('txt-title', this)">COPY</button>
                                    </div>
                                    <div class="copy-row">
                                        <label>In hoa toàn bộ</label>
                                        <div class="text-box" id="txt-upper"></div>
                                        <button class="btn-copy" onclick="copyToClip('txt-upper', this)">COPY</button>
                                    </div>
                                    <div class="copy-row">
                                        <label>Tiếng Anh</label>
                                        <div class="text-box" id="txt-en"></div>
                                        <button class="btn-copy" onclick="copyToClip('txt-en', this)">COPY</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="history-panel visible">
                    <div class="history-header">
                        <div class="history-title">
                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm.5 5H11v6l5.25 3.15.75-1.23-4.5-2.67z"></path></svg>
                            <div>
                                <p class="panel-kicker">Recent History</p>
                                <h2 class="panel-title">Lịch sử phí gần đây</h2>
                            </div>
                        </div>
                        <button class="btn-clear-history" id="btn-clear-history" style="display: none;">Xóa</button>
                    </div>
                    <p class="history-note">Mỗi lần tính gần đây sẽ được lưu lại để bạn mở nhanh lại thông số và tiếp tục chỉnh.</p>
                    <div class="history-list" id="history-list">
                        <div class="history-empty">Chưa có lịch sử</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="script.js?v=<?php echo APP_VERSION; ?>"></script>

</body>
</html>

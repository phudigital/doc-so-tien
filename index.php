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
        <div class="hero-inner">
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
            <h1 class="hero-title">QuoteCalc<span>+</span></h1>
            <p class="hero-subtitle">Tính VAT và đọc số tiền với giao diện hiện đại, dễ sử dụng.</p>
            <div class="hero-meta">
                <span class="version-chip">Phiên bản <?php echo APP_VERSION; ?></span>
            </div>

            <form id="calcForm" class="hero-form">
                <div class="hero-primary-row">
                    <div class="field grow" style="position: relative;">
                        <label class="input-label" for="amount">Số tiền</label>
                        <input type="text" id="amount" placeholder="VD: 10.000.000" autocomplete="off" autofocus>
                        <div id="suggestion-dropdown" class="suggestion-dropdown"></div>
                    </div>
                    <button type="submit" class="btn-submit">Xem Kết Quả</button>
                </div>

                <div class="controls-row">
                    <div class="vat-group">
                        <span>VAT:</span>
                        <label class="vat-option">
                            <input type="radio" name="vat_rate" value="0.08" checked> 8%
                        </label>
                        <label class="vat-option">
                            <input type="radio" name="vat_rate" value="0.10"> 10%
                        </label>
                    </div>

                    <label class="checkbox-group">
                        <input type="checkbox" id="is_tax_included">
                        <span>Đã bao gồm VAT</span>
                    </label>
                </div>
            </form>

            <p class="hero-note">Nhập số tiền, chọn VAT, hệ thống sẽ tính và lưu lịch sử phí bên dưới.</p>
        </div>
    </section>

    <section class="content" id="content-start">
        <div class="content-inner">
            <div id="error-msg" class="error-msg"></div>

            <div id="empty-state" class="empty-state">
                <p>Nhập thông tin ở trên để hiển thị kết quả tính phí.</p>
            </div>

            <div id="result-area">
                <div id="suggestion-box">
                    <strong>Đề xuất làm tròn:</strong><br>
                    Số tiền phù hợp hơn:<br>
                    <span class="suggest-highlight" id="sug-amount"></span>
                    <div class="suggest-meta">Giảm <span id="sug-diff"></span> so với số nhập.</div>
                    <div class="suggest-meta">Trước thuế: <strong id="sug-pre"></strong> | VAT: <strong id="sug-vat"></strong></div>
                    <button type="button" class="btn-apply" id="btn-apply-suggestion">Áp dụng</button>
                </div>

                <div class="results-container">
                    <table class="tax-table">
                        <tr>
                            <td>Trước thuế:</td>
                            <td id="res-pre"></td>
                        </tr>
                        <tr>
                            <td id="lbl-vat">VAT (8%):</td>
                            <td id="res-vat"></td>
                        </tr>
                        <tr>
                            <td>Tổng thanh toán:</td>
                            <td id="res-post"></td>
                        </tr>
                    </table>

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

            <div class="history-section visible">
                <div class="history-header">
                    <div class="history-title">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm.5 5H11v6l5.25 3.15.75-1.23-4.5-2.67z"></path></svg>
                        Lịch sử phí gần đây
                    </div>
                    <button class="btn-clear-history" id="btn-clear-history" style="display: none;">Xóa</button>
                </div>
                <div class="history-list" id="history-list">
                    <div class="history-empty">Chưa có lịch sử</div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="script.js?v=<?php echo APP_VERSION; ?>"></script>

</body>
</html>

<?php
require_once 'process.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Công cụ Kế toán Online: Đọc số tiền thành chữ, Tính thuế VAT (8%, 10%), Tính ngược và Đề xuất làm tròn số tiền thông minh.">
    <title>Công Cụ Đọc Số Tiền</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h2>Công Cụ Tính Thuế & Đọc Số Tiền</h2>
    
    <div class="main-content">
        <!-- LEFT PANEL -->
        <div class="left-panel">
            <form id="calcForm">
                <div class="form-group">
                    <label class="input-label" for="amount">Nhập số tiền:</label>
                    <input type="text" id="amount" placeholder="VD: 10.000.000" autocomplete="off" autofocus>
                </div>

                <!-- VAT Selection -->
                <div class="vat-group">
                    <span>Mức thuế VAT:</span>
                    <label class="vat-option">
                        <input type="radio" name="vat_rate" value="0.08" checked> 8%
                    </label>
                    <label class="vat-option">
                        <input type="radio" name="vat_rate" value="0.10"> 10%
                    </label>
                </div>
                
                <label class="checkbox-group">
                    <input type="checkbox" id="is_tax_included">
                    <span>Số tiền đã bao gồm thuế</span>
                </label>

                <div id="error-msg" class="error-msg"></div>
                
                <button type="submit" class="btn-submit">Xử Lý & Đọc Số</button>
            </form>
        </div>

        <!-- RIGHT PANEL -->
        <div class="right-panel">
            <div id="result-area">
                
                <!-- Suggestion -->
                <div id="suggestion-box">
                    <div>
                        ⚠ <strong>Đề xuất làm tròn thông minh:</strong><br>
                        Để giá trước thuế và VAT chẵn nghìn, số tiền nên là:<br>
                        <span class="suggest-highlight" id="sug-amount"></span><br>
                        <span style="font-size: 0.9em; color: #c0392b;">(Giảm đi <span id="sug-diff"></span> so với số nhập)</span>
                        <div style="margin-top: 5px; font-size: 0.85em; color: #666;">
                            ➡ Trước thuế: <span id="sug-pre"></span> | VAT: <span id="sug-vat"></span>
                        </div>
                    </div>
                    <button type="button" class="btn-apply" id="btn-apply-suggestion">Áp dụng số này</button>
                </div>

                <!-- Result Table -->
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

                <!-- Copy Blocks -->
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
                        <label>Tiếng Anh (English)</label>
                        <div class="text-box" id="txt-en"></div>
                        <button class="btn-copy" onclick="copyToClip('txt-en', this)">COPY</button>
                    </div>
                </div>

            </div> <!-- End result-area -->
            
            <div id="empty-state" style="text-align: center; color: #999; margin-top: 50px;">
                <p>Nhập số liệu và chọn mức thuế để xem kết quả.</p>
            </div>

        </div>
    </div>
</div>

<script src="script.js"></script>
<div style="position: fixed; bottom: 0; left: 0; right: 0; text-align: center; background: white; padding: 5px; border-top: 1px solid #ccc;">
    Copyright by Phu Digital Vibe Coding | Phiên bản <?php echo $version; ?>
</div>

</body>
</html>
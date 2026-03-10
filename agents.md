# QuoteCalc - Dự Án Tính Thuế & Đọc Số Tiền

## Tổng Quan Dự Án

**QuoteCalc** là một công cụ web giúp tính toán thuế VAT và đọc số tiền thành chữ bằng tiếng Việt và tiếng Anh.

### Thông Tin Dự Án

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên dự án** | QuoteCalc - Tax Calculator & Currency Reader |
| **Phiên bản hiện tại** | 2.1.1 |
| **Ngôn ngữ chính** | PHP, HTML, CSS, JavaScript |
| **Nền tảng** | Web (PHP + MySQL không yêu cầu) |
| **Link sử dụng** | https://app.pdl.vn/doc-so-tien/ |

### Các Tính Năng Chính

1. **Tính Thuế VAT**
   - Tính thuế xuôi (từ giá trước thuế)
   - Tính thuế ngược (từ tổng tiền đã bao gồm thuế)
   - Hỗ trợ VAT 8% và 10%

2. **Đọc Số Thành Chữ**
   - Tiếng Việt (viết hoa đầu câu, viết hoa đầu mỗi từ, in hoa toàn bộ)
   - Tiếng Anh

3. **Đề Xuất Làm Tròn Thông Minh**
   - Gợi ý số tiền gần nhất chia hết cho 13.500 (VAT 8%)
   - Gợi ý số tiền gần nhất chia hết cho 11.000 (VAT 10%)

4. **Lưu Trữ Lịch Sử**
   - Lưu lịch sử tính toán vào file JSON
   - Giới hạn 20 bản ghi gần nhất
   - Tự động xóa bản ghi cũ hơn 1 tuần

### Cấu Trúc File

```
doc-so-tien/
├── index.php          # File chính (HTML + PHP includes)
├── process.php        # Backend xử lý (API, tính toán, đọc số)
├── script.js         # Frontend JavaScript (AJAX, validation)
├── styles.css        # Giao diện CSS
├── version.php       # Quản lý phiên bản (APP_VERSION)
├── history.json      # File lưu lịch sử (tự động tạo)
├── thumbnail.jpg     # Ảnh thumbnail
├── agents.md         # File này - tài liệu dự án
└── README.md         # Tài liệu hướng dẫn sử dụng
```

### Công Nghệ Sử Dụng

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.0+
- **AJAX**: Fetch API
- **Storage**: JSON file (không cần database)

---

## Quy Tắc Phát Triển

### Yêu Cầu Bắt Buộc

1. **Tự động cập nhật commit sau mỗi lần thay đổi**
   - Mỗi khi có thay đổi code (sửa file, thêm file, xóa file), phải tự động tạo commit với thông điệp mô tả thay đổi.
   - Commit message nên ngắn gọn và mô tả chính xác những gì đã thay đổi.
   - Sử dụng format: `type: description` (ví dụ: `feat: thêm chức năng x`, `fix: sửa lỗi y`)

2. **Quản lý phiên bản**
   - Cập nhật `APP_VERSION` trong file `version.php` khi có thay đổi lớn.
   - Format phiên bản: `MAJOR.MINOR.PATCH` (ví dụ: 2.1.1 → 2.1.2)

3. **Cache busting**
   - File CSS và JS trong `index.php` đã được setup với version parameter (`?v=APP_VERSION`).
   - Khi cập nhật version, trình duyệt sẽ tự động tải lại file mới.

### Hướng Dẫn Đóng Góp

1. Tạo branch mới cho mỗi tính năng hoặc bản sửa lỗi.
2. Đảm bảo code chạy đúng trước khi commit.
3. Commit thường xuyên với thông điệp rõ ràng.
4. Merge qua Pull Request.

---

## Thông Tin Liên Hệ

- **Tác giả**: Phu Digital Vibe Coding
- **Website**: https://pdl.vn
- **Email**: contact@pdl.vn

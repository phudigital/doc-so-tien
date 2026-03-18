# QuoteCalc - Dự Án Tính Thuế & Đọc Số Tiền

## Tổng Quan Dự Án

**QuoteCalc** là một công cụ web giúp tính toán thuế VAT và đọc số tiền thành chữ bằng tiếng Việt và tiếng Anh.

### Thông Tin Dự Án

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên dự án** | QuoteCalc - Tax Calculator & Currency Reader |
| **Phiên bản hiện tại** | 3.1.0 |
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
   - Gợi ý số tiền thấp hơn gần nhất để tổng tiền, giá trước thuế và VAT cùng ra đơn vị nghìn
   - VAT 8% dùng bước 27.000
   - VAT 10% dùng bước 11.000

4. **Lưu Trữ Lịch Sử Gọn Nhẹ**
   - Lưu lịch sử tính toán vào file JSON
   - Giới hạn **10 bản ghi** gần nhất để giao diện tối giản
   - Tự động xóa bản ghi cũ hơn 1 tuần

5. **Thiết Kế UI/UX (Minimalist Dark Mode)**
   - UI hoàn toàn theo phong cách dark mode sang trọng, tối giản, gọn gàng.
   - Các gợi ý số tiền hiển thị dạng chips inline ngay dưới ô input.
   - Gợi ý VAT làm tròn hiển thị inline cùng hàng với nút tính nhanh để tiết kiệm không gian.

### Cấu Trúc File

```text
doc-so-tien/
├── index.php          # File chính (HTML + PHP includes + Config APP_VERSION)
├── process.php        # Backend xử lý (API, tính toán, đọc số)
├── script.js          # Frontend JavaScript (AJAX, validation)
├── styles.css         # Giao diện CSS
├── history.json       # File lưu lịch sử (tự động tạo)
├── thumbnail.jpg      # Ảnh thumbnail
├── agents.md          # Tài liệu dự án và quy tắc phát triển
├── SUPERPOWERS.md     # Chỉ dẫn ngắn cho agent/Codex
└── README.md          # Tài liệu hướng dẫn sử dụng
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
   - Cập nhật `APP_VERSION` ngay trong file `index.php` khi có thay đổi lớn.
   - Format phiên bản: `MAJOR.MINOR.PATCH`

3. **Cache busting**
   - File CSS và JS trong `index.php` đã được setup với version parameter (`?v=APP_VERSION`).
   - Khi cập nhật version, trình duyệt sẽ tự động tải lại file mới.

### Workflow Superpowers

Project này dùng Superpowers làm workflow mặc định khi phát triển bằng Codex hoặc agent.

1. Bắt đầu với `using-superpowers` để kiểm tra skill cần dùng trong turn hiện tại.
2. Dùng `brainstorming` trước khi thêm tính năng mới, đổi giao diện, hoặc thay đổi hành vi.
3. Dùng `systematic-debugging` trước khi sửa bug hoặc điều chỉnh công thức tính.
4. Dùng `test-driven-development` cho các thay đổi liên quan đến logic VAT, làm tròn, hoặc dữ liệu trả về.
5. Dùng `verification-before-completion` trước khi kết luận đã xong, commit, hoặc báo deploy.

Nếu cần chỉ dẫn ngắn riêng cho agent, đọc thêm `SUPERPOWERS.md` ở thư mục gốc của project.

### Hướng Dẫn Đóng Góp

1. Tạo branch mới cho mỗi tính năng hoặc bản sửa lỗi.
2. Đảm bảo code chạy đúng trước khi commit.
3. Commit thường xuyên với thông điệp rõ ràng.
4. Merge qua Pull Request.

---

## Thông Tin Liên Hệ

- **Tác giả**: Phu Digital Vibe Coding
- **Website**: https://pdl.vn
- **Email**: info@pdl.vn

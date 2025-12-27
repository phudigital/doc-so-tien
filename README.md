# Công Cụ Tính Thuế & Đọc Số Tiền

## Mô Tả

Đây là một công cụ web đơn giản giúp tính toán thuế VAT (8% hoặc 10%), đọc số tiền thành chữ bằng tiếng Việt và tiếng Anh, đồng thời đề xuất số tiền làm tròn thông minh để giá trước thuế và VAT chẵn nghìn. Phù hợp cho kế toán, hóa đơn, hoặc bất kỳ ai cần xử lý số tiền nhanh chóng.

## Tính Năng Chính

- **Tính Thuế VAT**: Tính thuế xuôi (từ giá trước thuế) hoặc ngược (từ tổng tiền đã bao gồm thuế).
- **Đọc Số Thành Chữ**: Chuyển đổi số tiền thành văn bản bằng tiếng Việt (với nhiều định dạng: viết hoa đầu câu, đầu mỗi từ, in hoa toàn bộ) và tiếng Anh.
- **Đề Xuất Làm Tròn**: Tự động gợi ý số tiền gần nhất chia hết cho 13.500 (VAT 8%) hoặc 11.000 (VAT 10%) để giá chẵn nghìn.
- **Giao Diện Thân Thiện**: Responsive, dễ sử dụng trên desktop và mobile.
- **Copy Nhanh**: Sao chép kết quả đọc số chỉ với một click.

## Cách Sử Dụng

1. **Nhập Số Tiền**: Gõ số tiền vào ô input (tự động format theo định dạng Việt Nam, ví dụ: 10.000.000).
2. **Chọn Mức Thuế**: Chọn 8% hoặc 10% VAT.
3. **Đánh Dấu Nếu Đã Bao Gồm Thuế**: Tick vào checkbox nếu số nhập đã có thuế.
4. **Nhấn "Xử Lý & Đọc Số"**: Xem kết quả tính toán, đọc số, và đề xuất (nếu có).
5. **Copy Kết Quả**: Nhấn nút "COPY" bên cạnh văn bản đọc số.

## Cài Đặt & Chạy

### Yêu Cầu
- Máy chủ web hỗ trợ PHP (ví dụ: Apache, Nginx, hoặc PHP built-in server).
- Trình duyệt web hiện đại.

### Bước Cài Đặt
1. Clone hoặc tải xuống dự án:
   ```bash
   git clone <repository-url>
   cd doc-so-tien
   ```

2. Chạy server PHP:
   ```bash
   php -S localhost:8000
   ```

3. Mở trình duyệt và truy cập: `http://localhost:8000/index.php`

### Cấu Trúc File
- `index.php`: File chính (HTML + include logic).
- `process.php`: Xử lý backend PHP (tính toán, API JSON).
- `styles.css`: Styling CSS.
- `script.js`: JavaScript frontend (AJAX, validation).

## Công Nghệ Sử Dụng

- **Frontend**: HTML5, CSS3, JavaScript (ES6+).
- **Backend**: PHP (không cần database).
- **AJAX**: Fetch API để gửi dữ liệu không reload trang.
- **Responsive**: CSS Grid và Media Queries.

## Ví Dụ

- Nhập: 1.000.000 VNĐ, VAT 8%, chưa bao gồm thuế.
- Kết quả: Trước thuế 1.000.000 VNĐ, VAT 80.000 VNĐ, Tổng 1.080.000 VNĐ.
- Đọc số: "Một triệu đồng" (tiếng Việt), "One million VND" (tiếng Anh).
- Đề xuất: Nếu gần 1.080.000, gợi ý làm tròn xuống 1.062.500 để chẵn nghìn.

## Tác Giả

- **Phu Digital Vibe Coding**: Phát triển và bảo trì.
- Phiên bản: 1.0

## Giấy Phép

Dự án này được phân phối dưới giấy phép MIT. Xem file LICENSE để biết thêm chi tiết.

## Đóng Góp

Mọi đóng góp đều được chào đón! Hãy tạo issue hoặc pull request trên GitHub.

## Liên Hệ

Nếu có câu hỏi, liên hệ qua email hoặc GitHub issues.
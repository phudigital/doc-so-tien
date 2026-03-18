# Công Cụ Tính Thuế & Đọc Số Tiền

## Mô Tả

Đây là một công cụ web đơn giản giúp tính toán thuế VAT (8% hoặc 10%), đọc số tiền thành chữ bằng tiếng Việt và tiếng Anh, đồng thời đề xuất số tiền làm tròn thông minh để giá trước thuế và VAT chẵn nghìn. Phù hợp cho kế toán, hóa đơn, hoặc bất kỳ ai cần xử lý số tiền nhanh chóng.

## Truy Cập Trực Tiếp

Bạn có thể sử dụng công cụ ngay tại: [https://app.pdl.vn/doc-so-tien/](https://app.pdl.vn/doc-so-tien/)

## Tính Năng Chính

- **Tính Thuế VAT**: Tính thuế xuôi (từ giá trước thuế) hoặc ngược (từ tổng tiền đã bao gồm thuế).
- **Đọc Số Thành Chữ**: Chuyển đổi số tiền thành văn bản bằng tiếng Việt (với nhiều định dạng: viết hoa đầu câu, đầu mỗi từ, in hoa toàn bộ) và tiếng Anh.
- **Đề Xuất Làm Tròn**: Tự động gợi ý số tiền thấp hơn gần nhất để tổng tiền, giá trước thuế và VAT cùng ra đơn vị nghìn (hiển thị inline ngay nút tính). Với VAT 8%, hệ thống dùng bước 27.000; với VAT 10%, hệ thống dùng bước 11.000.
- **Giao Diện Tối Giản (Dark Mode)**: Giao diện nền tối cực kỳ hiện đại, tối giản, thân thiện, dễ sử dụng trên màn hình máy tính và thiết bị di động.
- **Tính Năng Gợi Ý Nhanh**: Tự động đưa ra các chip gợi ý (trăm ngàn, triệu, chục triệu) ngay dưới ô nhập số tiền giúp thao tác cực chớp nhoáng thay vì phải gõ nhiều số `0`.
- **Lưu Trữ Lịch Sử Gọn Nhẹ**: Tự động lưu và hiển thị lại 10 kết quả gần nhất.
- **Copy Nhanh**: Sao chép kết quả đọc số hay thông số trước thuế/sau thuế chỉ với một click.
- **Hiển Thị Phiên Bản**: Giao diện luôn hiển thị version để dễ đối chiếu khi deploy và test.

## Cách Sử Dụng

1. **Nhập Số Tiền**: Gõ số tiền vào ô input (tự động format theo định dạng Việt Nam, ví dụ: 10.000.000).
2. **Chọn Mức Thuế**: Chọn 8% hoặc 10% VAT.
3. **Đánh Dấu Nếu Đã Bao Gồm Thuế**: Tick vào checkbox nếu số nhập đã có thuế.
4. **Nhấn "Xem Kết Quả"**: Xem kết quả tính toán, đọc số, và đề xuất (nếu có).
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
- `agents.md`: Tài liệu phát triển và quy ước nội bộ của dự án.
- `SUPERPOWERS.md`: Chỉ dẫn ngắn cho Codex/agent khi làm việc trực tiếp trong repo.

## Công Nghệ Sử Dụng

- **Frontend**: HTML5, CSS3, JavaScript (ES6+).
- **Backend**: PHP (không cần database).
- **AJAX**: Fetch API để gửi dữ liệu không reload trang.
- **Responsive**: CSS Grid và Media Queries.

## Ví Dụ

- Nhập: 1.000.000 VNĐ, VAT 8%, chưa bao gồm thuế.
- Kết quả: Trước thuế 1.000.000 VNĐ, VAT 80.000 VNĐ, Tổng 1.080.000 VNĐ.
- Đọc số: "Một triệu đồng" (tiếng Việt), "One million VND" (tiếng Anh).
- Đề xuất: Hệ thống ưu tiên số gợi ý giúp tổng tiền, giá trước thuế và VAT cùng ra đơn vị nghìn.

## Workflow Superpowers

Project này dùng workflow Superpowers để giữ nhịp phát triển ổn định khi làm việc với Codex.

- Luôn bắt đầu bằng `using-superpowers` để kiểm tra skill phù hợp trước khi hành động.
- Dùng `brainstorming` trước khi thêm tính năng, đổi UI, hoặc thay đổi hành vi.
- Dùng `systematic-debugging` trước khi sửa bug hoặc xử lý kết quả tính toán sai.
- Dùng `test-driven-development` cho thay đổi hành vi quan trọng, đặc biệt là logic VAT và gợi ý.
- Dùng `verification-before-completion` trước khi kết luận đã xong, commit, hoặc báo deploy.

Khi làm việc bằng agent, hãy đọc thêm `SUPERPOWERS.md` và `agents.md` ở thư mục gốc để bám đúng quy ước của project.

## Tác Giả

- **Phu Digital Vibe Coding**: Phát triển và bảo trì.
- Phiên bản hiện tại: 3.1.0

## Giấy Phép

Dự án này được phân phối dưới giấy phép MIT. Xem file LICENSE để biết thêm chi tiết.

## Đóng Góp

Mọi đóng góp đều được chào đón! Hãy tạo issue hoặc pull request trên GitHub.

## Liên Hệ

Nếu có câu hỏi, liên hệ qua email hoặc GitHub issues.

# QuoteCalc+ Google Apps Script

Phiên bản Google Apps Script của QuoteCalc+ — công cụ tính thuế VAT và đọc số tiền thành chữ.

**Ai cũng có thể sử dụng** — chỉ cần deploy lên Google Apps Script và chia sẻ link.

## 📋 Tính năng

- ✅ Tính thuế VAT xuôi/ngược (8% & 10%)
- ✅ Đọc số tiền thành chữ (Tiếng Việt & Tiếng Anh)
- ✅ Đề xuất làm tròn thông minh
- ✅ Lưu lịch sử tính toán (20 bản ghi, tự xóa sau 1 tuần)
- ✅ Copy nhanh kết quả
- ✅ Giao diện dark mode premium
- ✅ Responsive trên mobile
- ✅ Phím tắt Ctrl+Enter để tính nhanh
- ✅ Miễn phí 100%, không cần hosting

## 🚀 Hướng dẫn triển khai

### Bước 1: Tạo Google Apps Script Project

1. Truy cập [script.google.com](https://script.google.com)
2. Click **"Dự án mới"** (New Project)
3. Đặt tên dự án: `QuoteCalc+`

### Bước 2: Copy Code

1. **File `Code.gs`**: Xóa nội dung mặc định, copy toàn bộ nội dung từ `Code.gs` vào
2. **File `Index.html`**: Click dấu **+** > **HTML** > Đặt tên `Index` (không có .html) > Copy nội dung từ `Index.html` vào

### Bước 3: Deploy Web App

1. Click **Triển khai** > **Triển khai mới**
2. Chọn loại: **Ứng dụng web** (Web app)
3. Cấu hình:
   - **Mô tả**: `QuoteCalc+ v1.0.0`
   - **Thực thi với tư cách**: `Tôi` (Me)
   - **Ai có quyền truy cập**: `Bất kỳ ai` (Anyone)
4. Click **Triển khai**
5. Copy link Web App và chia sẻ với mọi người! 🎉

### Bước 4: Cập nhật (khi có thay đổi)

1. Sửa code
2. Click **Triển khai** > **Quản lý bản triển khai**
3. Click **biểu tượng bút chì** > **Phiên bản mới**
4. Click **Triển khai**

## 📁 Cấu trúc file

```
google-appscript/
├── Code.gs        # Backend: tính toán, đọc số, quản lý lịch sử
├── Index.html     # Frontend: giao diện HTML + CSS + JavaScript  
└── README.md      # File này
```

## 🔧 Công nghệ

| Thành phần | Chi tiết |
|-----------|----------|
| Backend | Google Apps Script (JavaScript runtime) |
| Frontend | HTML5 + CSS3 + Vanilla JS |
| Storage | PropertiesService (thay thế JSON file) |
| Font | Google Fonts (Inter) |
| Hosting | Google Cloud (miễn phí) |

## 📝 Khác biệt so với phiên bản PHP

| Tính năng | PHP Version | GAS Version |
|-----------|-------------|-------------|
| Backend | PHP 7.0+ | Google Apps Script |
| Lưu trữ | JSON file | PropertiesService |
| Hosting | Cần VPS/Hosting | Google Cloud (miễn phí) |
| API call | Fetch → process.php | google.script.run |
| Giao diện | Light mode | Dark mode premium |
| Triển khai | Upload lên server | Deploy từ GAS Editor |

## 👤 Tác giả

- **Phu Digital** — [pdl.vn](https://pdl.vn)
- Email: info@pdl.vn

# SUPERPOWERS.md

## Mục Đích

File này là chỉ dẫn ngắn cho Codex/agent khi làm việc trực tiếp trong project `doc-so-tien`.
Tài liệu đầy đủ hơn về dự án nằm ở `agents.md` và `README.md`.

## Workflow Mặc Định

- Luôn bắt đầu bằng `using-superpowers`.
- Dùng `brainstorming` trước khi thêm tính năng, đổi UI, hoặc thay đổi hành vi.
- Dùng `systematic-debugging` khi kết quả tính toán, gợi ý, hoặc test có dấu hiệu sai.
- Dùng `test-driven-development` cho thay đổi liên quan đến VAT, làm tròn, và dữ liệu hiển thị.
- Dùng `verification-before-completion` trước khi báo xong hoặc tạo commit.

## Quy Tắc Project

- Sau mỗi thay đổi có chỉnh sửa file, tạo commit theo format `type: description`.
- Khi có thay đổi đáng kể, cập nhật `APP_VERSION` trong `version.php`.
- Không revert thay đổi không thuộc task hiện tại nếu chưa được yêu cầu.
- Ưu tiên giữ logic làm tròn nhất quán giữa giao diện, backend, và tài liệu.

## Ghi Chú Nghiệp Vụ

- Với VAT 8%, gợi ý hợp lệ phải giữ tổng tiền, trước thuế, và VAT cùng ra đơn vị nghìn.
- Với VAT 10%, bước gợi ý đang là 11.000.
- Khi cập nhật giao diện, nên hiển thị phiên bản hiện tại để thuận tiện deploy và test.

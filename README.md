# Bếp Nhà — Website công thức nấu ăn

Dự án học tập PHP + MySQL + Nginx + Docker Compose, có phpMyAdmin, Prometheus, Grafana, Loki và Grafana Alloy.

## Yêu cầu
- macOS, Docker Desktop đang chạy
- Git (nếu muốn đẩy lên GitHub)

## Khởi chạy nhanh
1. Sao chép `.env.example` thành `.env`.
2. Tạo mật khẩu hash cho trang quản trị (xem hướng dẫn bên dưới), cập nhật `.env`.
3. Chạy `docker compose config` để kiểm tra cấu hình.
4. Chạy `docker compose up -d --build`.
5. Mở website: http://localhost:8080
6. phpMyAdmin: http://localhost:8081 (đăng nhập `recipe_user` hoặc `root`)
7. Prometheus: http://localhost:9090
8. Grafana: http://localhost:3000
9. Loki API chỉ nằm trong mạng Docker; truy vấn log trong Grafana Explore.

## Tạo mật khẩu hash quản trị
Thay `YOUR_ADMIN_PASSWORD` bằng mật khẩu bạn muốn dùng:
```bash
docker run --rm php:8.3-cli-alpine php -r "echo password_hash('YOUR_ADMIN_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
```
Sao chép chuỗi kết quả vào `ADMIN_PASSWORD_HASH` trong `.env`. Không để mật khẩu thật trong mã nguồn.

## Lệnh hữu ích
```bash
docker compose ps
docker compose logs -f nginx
docker compose logs -f app
docker compose logs -f db
docker compose down
```
Dữ liệu MySQL vẫn được giữ trong volume khi dùng `docker compose down`. Lệnh `docker compose down -v` sẽ xóa volume và dữ liệu — chỉ dùng khi muốn reset toàn bộ dữ liệu thử nghiệm.

## Grafana: thêm data source
- Prometheus URL: `http://prometheus:9090`
- Loki URL: `http://loki:3100`
Các URL này dùng bên trong mạng Docker, không phải `localhost`.

## Kiểm tra
- Trang chủ và tìm kiếm: `http://localhost:8080`
- Chi tiết mẫu: `http://localhost:8080/recipe.php?id=1`
- Quản trị: `http://localhost:8080/admin/`
- Nginx exporter: trong Prometheus > Status > Targets, job `nginx` cần UP.
- Log: tạo vài lượt truy cập website, sau đó Grafana > Explore > Loki, thử `{job="nginx"}` hoặc `{job="nginx", log_type="error"}`.

## Bảo mật / giới hạn của bản demo
- Các cổng được bind vào `127.0.0.1`, chỉ truy cập từ máy đang chạy Docker.
- `.env` không được commit.
- Tài khoản admin dùng hash mật khẩu và session; bản demo này chưa có CSRF token và chức năng sửa món ăn. Bổ sung trước khi đưa ra Internet.
- Không mở cổng MySQL ra host.
- Nginx status endpoint chỉ dùng nội bộ; exporter cần truy cập được endpoint này trong mạng Docker.
- Các ảnh mẫu lấy từ URL bên ngoài; cần kết nối Internet để tải ảnh.

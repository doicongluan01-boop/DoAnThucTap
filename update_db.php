<?php
require 'config.php';

try {
    echo "<h1>ĐANG CẬP NHẬT CƠ SỞ DỮ LIỆU...</h1>";

    // 1. THÊM CỘT 'duoc_bao_ve' NẾU CHƯA CÓ (Sửa lỗi in báo cáo)
    try {
        $pdo->query("SELECT duoc_bao_ve FROM danh_sach_sinh_vien LIMIT 1");
        echo "✅ Cột 'duoc_bao_ve' đã tồn tại.<br>";
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE danh_sach_sinh_vien ADD COLUMN duoc_bao_ve TINYINT DEFAULT 1 COMMENT '1: BV, 0: KBV, 2: DC50%'");
        echo "✅ Đã thêm cột 'duoc_bao_ve'.<br>";
    }

    // 2. THÊM CỘT 'hoidong_id' NẾU CHƯA CÓ
    try {
        $pdo->query("SELECT hoidong_id FROM danh_sach_sinh_vien LIMIT 1");
        echo "✅ Cột 'hoidong_id' đã tồn tại.<br>";
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE danh_sach_sinh_vien ADD COLUMN hoidong_id INT DEFAULT NULL");
        echo "✅ Đã thêm cột 'hoidong_id'.<br>";
    }

    // 3. TẠO BẢNG ĐIỂM HỘI ĐỒNG NẾU CHƯA CÓ (Để chấm điểm nhóm)
    $sql_diem = "CREATE TABLE IF NOT EXISTS qlsv_diem_hoidong (
        id INT AUTO_INCREMENT PRIMARY KEY,
        MaSV VARCHAR(20) NOT NULL,
        DiemBaoVe DECIMAL(4,2),
        NhanXet TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(MaSV)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_diem);
    echo "✅ Đã kiểm tra/tạo bảng 'qlsv_diem_hoidong'.<br>";

    // 4. XỬ LÝ LỖI "TRẦN VĂN HÙNG" (QUAN TRỌNG)
    // Bước A: Xóa sạch tên GVHD dạng text cũ của những sinh viên KHÔNG có trong bảng phân công
    // Điều này giúp loại bỏ những tên "ma" lưu cứng trong database
    $sql_clean = "UPDATE danh_sach_sinh_vien 
                  SET gvhd = NULL 
                  WHERE MaSV NOT IN (SELECT MaSV FROM phancong_gvhd)";
    $stmt = $pdo->exec($sql_clean);
    echo "✅ Đã dọn dẹp $stmt dòng tên GVHD cũ (không được phân công).<br>";

    // Bước B: Đồng bộ ngược từ bảng Phân công sang bảng Sinh viên
    // Để khi xem danh sách ở đâu cũng thấy tên đúng
    $sql_sync = "UPDATE danh_sach_sinh_vien sv
                 JOIN phancong_gvhd pc ON sv.MaSV = pc.MaSV
                 JOIN giang_vien gv ON pc.MaGV = gv.MaGV
                 SET sv.gvhd = gv.HoTen";
    $stmt = $pdo->exec($sql_sync);
    echo "✅ Đã đồng bộ tên GVHD chính thức cho $stmt sinh viên.<br>";

    // 5. CẬP NHẬT TRẠNG THÁI 'ĐƯỢC BẢO VỆ' TỰ ĐỘNG
    // Nếu điểm quá trình (diem_50) >= 5 thì set duoc_bao_ve = 1, ngược lại = 0
    $sql_update_bv = "UPDATE danh_sach_sinh_vien 
                      SET duoc_bao_ve = CASE 
                          WHEN diem_50 >= 5 THEN 1 
                          ELSE 0 
                      END 
                      WHERE diem_50 IS NOT NULL";
    $pdo->exec($sql_update_bv);
    echo "✅ Đã tự động xét duyệt điều kiện bảo vệ dựa trên điểm 50%.<br>";

    echo "<hr><h3 style='color:green'>CẬP NHẬT HOÀN TẤT! HỆ THỐNG ĐÃ SẴN SÀNG.</h3>";
    echo "<a href='index.php'>Về trang chủ</a>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>LỖI: " . $e->getMessage() . "</h3>";
}
?>
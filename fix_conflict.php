<?php
require 'config.php';

// SQL: Cập nhật gvpb_id thành NULL nếu nó trùng với giang_vien_huong_dan_id của nhóm
$sql = "UPDATE danh_sach_sinh_vien sv
        JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id
        JOIN nhom n ON ptn.nhom_id = n.id
        SET sv.gvpb_id = NULL
        WHERE sv.gvpb_id = n.giang_vien_huong_dan_id";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $count = $stmt->rowCount();
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa lỗi trùng GV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 text-center">
        <div class="card shadow-sm p-5">
            <?php if ($count > 0): ?>
                <h2 class="text-success fw-bold">Đã sửa xong!</h2>
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i> 
                    Hệ thống đã tìm thấy và gỡ bỏ GVPB cho <strong><?= $count ?></strong> sinh viên/nhóm bị trùng GVHD.
                </div>
                <p>Bây giờ những nhóm này đang trống GVPB. Bạn hãy quay lại trang phân công để phân lại nhé.</p>
            <?php else: ?>
                <h2 class="text-primary fw-bold">Dữ liệu sạch!</h2>
                <div class="alert alert-info mt-3">
                    Không tìm thấy trường hợp nào bị trùng (GVPB = GVHD).
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="phan_cong_gvpb.php" class="btn btn-primary">Quay lại trang Phân công</a>
                <a href="export_excel_stu.php" class="btn btn-success ms-2">Xuất lại Excel</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// get_nhom_by_gvhd.php - SIÊU ỔN ĐỊNH 2025
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

// Lấy GV hiện tại từ GET hoặc session (tùy bạn đang dùng)
$gvhd = $_GET['gvhd'] ?? 'GV001';  // Nếu chưa có login thì test với GV001

try {
    // Lấy danh sách nhóm mà GV đang hướng dẫn
    $sql = "SELECT nhom, gvhd, detai FROM danh_sach_sinh_vien 
            WHERE gvhd = ? AND nhom IS NOT NULL AND nhom > 0 
            GROUP BY nhom ORDER BY nhom";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$gvhd]);
    $nhoms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nếu không có nhóm → trả mảng rỗng (không crash)
    if (empty($nhoms)) {
        echo json_encode([]);
        exit;
    }

    // Bổ sung danh sách sinh viên cho từng nhóm
    foreach ($nhoms as &$n) {
        $stmt2 = $pdo->prepare("SELECT MaSV, HoTen, Lop FROM danh_sach_sinh_vien WHERE nhom = ? ORDER BY MaSV");
        $stmt2->execute([$n['nhom']]);
        $n['sinhvien'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $n['tong_sv'] = count($n['sinhvien']);
    }

    // TRẢ VỀ JSON ĐẸP, KHÔNG LỖI
    echo json_encode($nhoms, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // DÙ LỖI GÌ CŨNG TRẢ VỀ JSON HỢP LỆ
    http_response_code(200);
    echo json_encode(['error' => 'Lỗi server: ' . $e->getMessage()]);
}
?>
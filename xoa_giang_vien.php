<?php
// xoa_giang_vien.php - XÓA GV + BỎ PHÂN CÔNG KHỎI TẤT CẢ SINH VIÊN
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

$magv = $_POST['magv'] ?? '';
if (!$magv) {
    echo json_encode(['success' => false, 'message' => 'Thiếu mã GV']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Bỏ phân công GV khỏi tất cả sinh viên
    $pdo->prepare("UPDATE danh_sach_sinh_vien SET gvhd = NULL WHERE gvhd = ?")
        ->execute([$magv]);

    // 2. Xóa giảng viên khỏi bảng giang_vien
    $del = $pdo->prepare("DELETE FROM giang_vien WHERE MaGV = ?");
    $del->execute([$magv]);

    if ($del->rowCount() === 0) {
        throw new Exception('Không tìm thấy giảng viên để xóa');
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Xóa giảng viên thành công!']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
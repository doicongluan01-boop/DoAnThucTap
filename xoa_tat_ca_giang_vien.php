<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Kiểm tra quyền Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này!']);
    exit;
}

try {
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 3. Thực hiện xóa toàn bộ bảng
    $stmt = $pdo->prepare("TRUNCATE TABLE giang_vien");
    $stmt->execute();

    // Bật lại kiểm tra khóa ngoại
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>
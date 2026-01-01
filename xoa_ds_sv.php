<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Kiểm tra quyền Admin (Cực kỳ quan trọng)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này!']);
    exit;
}

try {

    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");


    $stmt = $pdo->prepare("TRUNCATE TABLE danh_sach_sinh_vien");
    $stmt->execute();

  

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>
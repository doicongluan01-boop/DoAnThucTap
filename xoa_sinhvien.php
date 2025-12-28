<?php
require 'config.php';

if (!isset($_POST['mssv'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu MSSV']);
    exit;
}

$mssv = $_POST['mssv'];

$stmt = $pdo->prepare("DELETE FROM danh_sach_sinh_vien WHERE MaSV = ?");
$ok = $stmt->execute([$mssv]);

echo json_encode(['success' => $ok]);
?>
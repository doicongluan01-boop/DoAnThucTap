<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->query("
        SELECT MaSV, HoTen, Lop, nhom, gvhd 
        FROM danh_sach_sinh_vien 
        WHERE nhom IS NOT NULL AND nhom > 0 
        ORDER BY nhom, MaSV
    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
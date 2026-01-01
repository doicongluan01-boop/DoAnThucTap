<?php
require_once 'config.php';
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

$stmt = $pdo->prepare("UPDATE danh_sach_sinh_vien SET huong_de_tai = ?, gvhd = ? WHERE nhom = ?");

foreach ($data as $item) {
    $stmt->execute([$item['detai'], $item['gvhd'], $item['nhom']]);
}

echo json_encode(['success' => true, 'message' => 'Lưu đề tài thành công!']);
?>
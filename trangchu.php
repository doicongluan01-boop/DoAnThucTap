<?php
require 'config.php'; // Kết nối DB

try {
    // Đếm sinh viên
    $totalSV = $pdo->query("SELECT COUNT(*) FROM qlsv_sinhvien")->fetchColumn();

    // Đếm giảng viên
    $totalGV = $pdo->query("SELECT COUNT(*) FROM qlsv_giangvien")->fetchColumn();

    // Đếm đề tài
    $totalDT = $pdo->query("SELECT COUNT(*) FROM qlsv_detai")->fetchColumn();

    // Ngày hôm nay
    $today = date('d/m/Y'); // Định dạng Việt Nam
} catch (Exception $e) {
    $totalSV = $totalGV = $totalDT = 'Lỗi';
    $today = 'Lỗi';
}

// BẬT LỖI ĐỂ DEBUG (tạm thời)
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require 'config.php'; // Đảm bảo config.php KHÔNG in gì

try {
    $stmt = $pdo->query("SELECT MaSV, HoTen, Email, MaLop AS Lop FROM qlsv_sinhvien ORDER BY MaSV");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

?>

<div style="padding:20px; text-align:center;">
  <h1>Chào mừng đến Hệ thống Quản lý LVTN</h1>
  <p>Hôm nay là: <strong><?php echo $today; ?></strong></p>
  <div style="margin-top:30px; font-size:18px;">
    <p><strong>Tổng sinh viên:</strong> <span style="color:#2196F3; font-size:24px;"><?php echo $totalSV; ?></span></p>
    <p><strong>Tổng giảng viên:</strong> <span style="color:#4CAF50; font-size:24px;"><?php echo $totalGV; ?></span></p>
    <p><strong>Tổng đề tài:</strong> <span style="color:#FF9800; font-size:24px;"><?php echo $totalDT; ?></span></p>
  </div>
</div>
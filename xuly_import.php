<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// === KIỂM TRA ĐĂNG NHẬP ===
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'giangvien'])) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Truy cập bị từ chối. Vui lòng đăng nhập!'], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 300);

try {
    // Kiểm tra file upload
    if (empty($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Không có file hoặc lỗi upload!');
    }

    $fileTmp = $_FILES['file_excel']['tmp_name'];
    $spreadsheet = IOFactory::load($fileTmp);
    $sheet = $spreadsheet->getActiveSheet();

    // Bắt đầu từ dòng 8 (dòng dữ liệu đầu tiên)
    $highestRow = $sheet->getHighestRow();
    $imported = 0;
    $skipped = 0;

    $pdo->beginTransaction();

    for ($row = 2; $row <= $highestRow; $row++) {
        $mssv = trim($sheet->getCell("B{$row}")->getCalculatedValue() ?? '');
        
        // Dừng khi hết dữ liệu (MaSV trống)
        if (empty($mssv) || $mssv === '' || strtolower($mssv) === 'null') {
            continue;
        }

        // Ghép Họ + Tên
        $ho  = trim($sheet->getCell("C{$row}")->getCalculatedValue() ?? '');
        $ten = trim($sheet->getCell("D{$row}")->getCalculatedValue() ?? '');
        $hoTen = trim($ho . ' ' . $ten);
        if (empty($hoTen) || $hoTen === ' ') {
            $skipped++;
            continue;
        }

        $lop     = trim($sheet->getCell("E{$row}")->getCalculatedValue() ?? '');
        $sdt     = trim($sheet->getCell("F{$row}")->getCalculatedValue() ?? '');
        $email   = trim($sheet->getCell("G{$row}")->getCalculatedValue() ?? '');
        $huongDT = trim($sheet->getCell("H{$row}")->getCalculatedValue() ?? '');
        $gvhd    = trim($sheet->getCell("J{$row}")->getCalculatedValue() ?? ''); // Cột J = GVHD
        $ghichu  = trim($sheet->getCell("K{$row}")->getCalculatedValue() ?? '');

        // === BỎ QUA SINH VIÊN KHÔNG HỢP LỆ (theo ghi chú ĐKMH) ===
        if (stripos($ghichu, 'ĐKMH') !== false || stripos($ghichu, 'không có tên') !== false || stripos($ghichu, 'P.ĐT') !== false) {
            $skipped++;
            continue;
        }

        // Chuẩn hóa dữ liệu
        $sdt   = ($sdt === '#N/A' || empty($sdt)) ? null : $sdt;
        $email = ($email === '#N/A' || empty($email)) ? null : $email;
        $gvhd  = empty($gvhd) ? null : $gvhd;
        $ghichu = empty($ghichu) ? null : $ghichu;

        // INSERT hoặc UPDATE (tránh trùng)
        $sql = "INSERT INTO danh_sach_sinh_vien 
                (MaSV, HoTen, Lop, SDT, Email, huong_de_tai, nhom, gvhd, ghichu) 
                VALUES (?, ?, ?, ?, ?, ?, NULL, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    HoTen = VALUES(HoTen),
                    Lop = VALUES(Lop),
                    SDT = VALUES(SDT),
                    Email = VALUES(Email),
                    huong_de_tai = VALUES(huong_de_tai),
                    gvhd = VALUES(gvhd),
                    ghichu = VALUES(ghichu)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$mssv, $hoTen, $lop, $sdt, $email, $huongDT, $gvhd, $ghichu]);
        $imported++;
    }

    // === CHIA NHÓM TỰ ĐỘNG CHO NHỮNG SV CHƯA CÓ NHÓM ===
    $pdo->exec("SET @n := 0;");
    $pdo->exec("UPDATE danh_sach_sinh_vien 
                SET nhom = (@n := @n + 1) DIV 3 + 1 
                WHERE nhom IS NULL OR nhom = '' 
                ORDER BY MaSV");

    // Lấy số nhóm lớn nhất
    $maxGroup = $pdo->query("SELECT COALESCE(MAX(nhom), 0) FROM danh_sach_sinh_vien")->fetchColumn();

    $pdo->commit();

    // Trả về kết quả chi tiết
    echo json_encode([
        'success' => true,
        'message' => "IMPORT HOÀN TẤT!",
        'imported' => $imported,
        'skipped'  => $skipped,
        'groups'   => (int)$maxGroup,
        'detail'   => "Đã thêm/cập nhật: {$imported} sinh viên | Bỏ qua: {$skipped} (không hợp lệ) | Tạo: {$maxGroup} nhóm"
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
<?php
// import_gvhd_real.php - HOÀN HẢO CHO FILE EXCEL CỦA KHOA (Họ + Tên tách cột, Email/SDT chung cột)
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if (!file_exists('vendor/autoload.php')) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thư mục vendor/ - Chưa cài Composer!']);
    exit;
}
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_FILES['file_excel_gv']) || $_FILES['file_excel_gv']['error'] !== 0) {
    echo json_encode(['success' => false, 'message' => 'Không có file hoặc lỗi upload!']);
    exit;
}

$file = $_FILES['file_excel_gv']['tmp_name'];

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();

    $pdo->beginTransaction();
    $added = $updated = 0;

    for ($row = 7; $row <= 200; $row++) {
        // CỘT B = HỌ, CỘT C = TÊN ĐỆM + TÊN
        $ho = trim($sheet->getCell("B{$row}")->getValue() ?? '');
        $tenDemTen = trim($sheet->getCell("C{$row}")->getValue() ?? '');

        if (empty($ho) && empty($tenDemTen)) break; // hết dữ liệu

        // Ghép lại thành họ tên đầy đủ
        $hoTen = trim($ho . ' ' . $tenDemTen);

        // Bỏ chức danh (PGS., TS., ThS.,...)
        $hoTen = preg_replace('/^(PGS\.?\s*|TS\.?\s*|ThS\.?\s*|GV\.?\s*)/i', '', $hoTen);
        $hoTen = trim($hoTen);

        // Lấy họ để tạo mã GV (Nguyễn, Trần, Lê, Phạm...)
        $arr = explode(' ', $hoTen);
        $hoDeTaoMa = end($arr); // phần tử cuối là tên → lấy họ từ tên đầy đủ
        $hoDeTaoMa = strtoupper(vn_str_filter($hoDeTaoMa));

        // Tạo base 4 ký tự đầu của họ
        $base = substr($hoDeTaoMa, 0, 4); // NGUY, TRAN, PHAM, LE..

        // Tìm số lớn nhất hiện có với base này
        $stmt = $pdo->prepare("SELECT MaGV FROM giang_vien WHERE MaGV LIKE ? ORDER BY MaGV DESC LIMIT 1");
        $stmt->execute(["{$base}%"]);
        $last = $stmt->fetchColumn();

        $newNum = '001';
        if ($last) {
            $num = (int)substr($last, 4);
            $newNum = str_pad($num + 1, 3, '0', STR_PAD_LEFT);
        }
        $maGV = $base . $newNum; // NGUY001, TRAN001, PHAM001...

        // Xử lý cột I: Email hoặc SĐT (có thể lẫn lộn)
        $contact = trim($sheet->getCell("I{$row}")->getValue() ?? '');
        $email = $sdt = null;

        if (!empty($contact)) {
            if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
                $email = $contact;
            } elseif (preg_match('/\d{9,11}/', $contact)) {
                $sdt = preg_replace('/\D/', '', $contact);
                if (strlen($sdt) >= 9) {
                    $sdt = '0' . substr($sdt, -9); // chuẩn hóa về 10 số
                }
            } else {
                // Trường hợp có cả email và số (ví dụ: abc@stu.edu.vn 0901234567)
                if (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $contact, $m)) {
                    $email = $m[1];
                }
                if (preg_match('/0\d{9}/', $contact, $m)) {
                    $sdt = $m[0];
                }
            }
        }

        // Insert hoặc Update
        $sql = "INSERT INTO giang_vien (MaGV, HoTen, Email, DienThoai, Khoa, ChucVu) 
                VALUES (?, ?, ?, ?, 'CNTT', 'Giảng viên')
                ON DUPLICATE KEY UPDATE 
                    HoTen = VALUES(HoTen),
                    Email = COALESCE(VALUES(Email), Email),
                    DienThoai = COALESCE(VALUES(DienThoai), DienThoai)";

        $stmt = $pdo->prepare($sql);
        $executed = $stmt->execute([$maGV, $hoTen, $email, $sdt]);

        if ($stmt->rowCount()) $added++; else $updated++;
    }

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => "HOÀN TẤT! Thêm $added giảng viên mới, cập nhật $updated giảng viên. Trang sẽ reload..."
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . addslashes($e->getMessage())]);
}

// Hàm bỏ dấu tiếng Việt
function vn_str_filter($str) {
    $str = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $str);
    $str = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $str);
    $str = preg_replace('/[ìíịỉĩ]/u', 'i', $str);
    $str = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $str);
    $str = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $str);
    $str = preg_replace('/[ỳýỵỷỹ]/u', 'y', $str);
    $str = preg_replace('/đ/u', 'd', $str);
    return strtoupper($str);
}
?>
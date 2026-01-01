<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    // Kiểm tra tên bảng thực tế của bạn (thường là 1 trong 4 cái này)
    $possible_tables = ['giang_vien', 'giangvien', 'giao_vien', 'lecturers'];
    $query = null;

    foreach ($possible_tables as $table) {
        $check = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount();
        if ($check > 0) {
            $query = "SELECT MaGV, HoTen, Email FROM $table ORDER BY HoTen";
            break;
        }
    }

    // Nếu không tìm thấy → dùng tên mặc định (bạn sửa lại nếu cần)
    if (!$query) {
        $query = "SELECT MaGV, HoTen, Email FROM giang_vien ORDER BY HoTen";
    }

    $stmt = $pdo->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nếu bảng rỗng → trả về vài GV mẫu để test
    if (empty($result)) {
        $result = [
            ['MaGV' => 'GV001', 'HoTen' => 'Nguyễn Văn A', 'Email' => 'nva@stu.edu.vn'],
            ['MaGV' => 'GV002', 'HoTen' => 'Trần Thị B', 'Email' => 'ttb@stu.edu.vn'],
            ['MaGV' => 'GV003', 'HoTen' => 'Lê Văn C', 'Email' => 'lvc@stu.edu.vn']
        ];
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi database: ' . $e->getMessage()]);
}
?>
<?php
require 'config.php';

// Kiểm tra xem có ID được gửi lên không
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 1. Chuẩn bị câu lệnh xóa
        $stmt = $pdo->prepare("DELETE FROM hoidong WHERE id = :id");
        
        // 2. Thực thi
        if ($stmt->execute([':id' => $id])) {
            // Xóa thành công -> Quay lại trang danh sách hội đồng
            header('Location: danhsach_hd.php?success=deleted');
            exit();
        } else {
            // Xóa thất bại (do lỗi SQL)
            echo "Lỗi khi xóa hội đồng.";
        }
    } catch (PDOException $e) {
        // Lỗi thường gặp: Hội đồng này đã có sinh viên hoặc giáo viên liên kết (ràng buộc khóa ngoại)
        if ($e->getCode() == '23000') {
            echo "<script>
                    alert('Không thể xóa! Hội đồng này đang có sinh viên hoặc dữ liệu liên quan.');
                    window.location.href = 'danhsach_hd.php';
                  </script>";
        } else {
            echo "Lỗi hệ thống: " . $e->getMessage();
        }
    }
} else {
    // Không có ID thì quay về danh sách
    header('Location: danhsach_hd.php');
    exit();
}
?>
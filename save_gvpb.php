<?php
require 'config.php';

$action = $_POST['action'] ?? '';

if ($action === 'group') {
    // === XỬ LÝ GÁN 1 NHÓM CỤ THỂ ===
    $nhom_id = $_POST['nhom_id'];
    $gvpb_id = $_POST['gvpb_id'];

    // 1. Kiểm tra xem GVPB có trùng với GVHD của nhóm này không?
    $stmt_check = $pdo->prepare("SELECT giang_vien_huong_dan_id FROM nhom WHERE id = ?");
    $stmt_check->execute([$nhom_id]);
    $gvhd_id = $stmt_check->fetchColumn();

    if ($gvpb_id == $gvhd_id) {
        die("Lỗi: GV Phản biện không được trùng với GV Hướng dẫn!");
    }

    // 2. Cập nhật cho TẤT CẢ sinh viên trong nhóm
    // Tìm các sinh viên thuộc nhóm này
    $sql_update = "UPDATE danh_sach_sinh_vien 
                   SET gvpb_id = ? 
                   WHERE id IN (SELECT sinh_vien_id FROM phan_thuoc_nhom WHERE nhom_id = ?)";
    
    try {
        $stmt = $pdo->prepare($sql_update);
        $stmt->execute([$gvpb_id, $nhom_id]);
        echo "OK";
    } catch (Exception $e) {
        echo "Lỗi DB: " . $e->getMessage();
    }
} 
elseif ($action === 'auto_group') {
    // === XỬ LÝ TỰ ĐỘNG PHÂN CÔNG THEO NHÓM ===
    
    // 1. Lấy tất cả các nhóm chưa có GVPB (kiểm tra qua 1 sinh viên đại diện)
    // Giả sử nếu SV chưa có GVPB thì nhóm đó chưa có
    $sql_groups = "SELECT DISTINCT n.id as nhom_id, n.giang_vien_huong_dan_id 
                   FROM nhom n
                   JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
                   JOIN danh_sach_sinh_vien sv ON ptn.sinh_vien_id = sv.id
                   WHERE sv.gvpb_id IS NULL OR sv.gvpb_id = ''";
    
    $groups = $pdo->query($sql_groups)->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Lấy danh sách Giảng viên để random
    $teachers = $pdo->query("SELECT MaGV FROM giang_vien")->fetchAll(PDO::FETCH_COLUMN);
    
    $count_updated = 0;

    foreach ($groups as $grp) {
        $nhom_id = $grp['nhom_id'];
        $gvhd_id = $grp['giang_vien_huong_dan_id'];
        
        // Tìm GVPB hợp lệ (Khác GVHD)
        $candidates = array_diff($teachers, [$gvhd_id]); // Loại bỏ GVHD ra khỏi danh sách chọn
        
        if (!empty($candidates)) {
            // Chọn ngẫu nhiên 1 người
            $random_key = array_rand($candidates);
            $selected_gvpb = $candidates[$random_key];

            // Cập nhật cho cả nhóm
            $sql_upd = "UPDATE danh_sach_sinh_vien 
                        SET gvpb_id = ? 
                        WHERE id IN (SELECT sinh_vien_id FROM phan_thuoc_nhom WHERE nhom_id = ?)";
            $stmt = $pdo->prepare($sql_upd);
            $stmt->execute([$selected_gvpb, $nhom_id]);
            
            $count_updated++;
        }
    }
    
    echo "OK|" . $count_updated;
}
?>
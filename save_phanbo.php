<?php
// File: save_phanbo_nhom.php

// 1. KHỞI TẠO SESSION NGAY ĐẦU TIÊN
session_start(); 

require 'config.php'; // File kết nối CSDL

// 2. KIỂM TRA ĐĂNG NHẬP (Nếu chưa đăng nhập thì chặn ngay)
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    // Tùy vào cách bạn lưu session, hãy sửa lại điều kiện if cho đúng với hệ thống của bạn
    // Trả về lỗi rõ ràng để Javascript biết
    echo "Lỗi: Phiên đăng nhập hết hạn. Vui lòng F5 và đăng nhập lại!";
    exit;
}

if (isset($_POST['nhom_id'])) {
    $nhom_id = $_POST['nhom_id'];
    $hoidong_input = $_POST['hoidong_id'] ?? '';

    // Xử lý giá trị NULL khi kéo về kho chưa phân
    if (empty($hoidong_input) || $hoidong_input === 'chua' || $hoidong_input === 'null') {
        $val_hoidong = null; 
    } else {
        $val_hoidong = $hoidong_input;
    }

    try {
        // Lấy danh sách SV trong nhóm
        $stmtGet = $pdo->prepare("SELECT sinh_vien_id FROM phan_thuoc_nhom WHERE nhom_id = ?");
        $stmtGet->execute([$nhom_id]);
        $ids = $stmtGet->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($ids)) {
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            
            // Cập nhật
            $sql = "UPDATE danh_sach_sinh_vien SET hoidong_id = ? WHERE id IN ($inQuery)";
            $params = array_merge([$val_hoidong], $ids);
            
            $stmtUp = $pdo->prepare($sql);
            if ($stmtUp->execute($params)) {
                echo "Success";
            } else {
                echo "Error: Lỗi SQL Update";
            }
        } else {
            // Nhóm rỗng vẫn báo Success để không lỗi giao diện
            echo "Success";
        }
    } catch (Exception $e) {
        echo "Lỗi hệ thống: " . $e->getMessage();
    }
}
?>
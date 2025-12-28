<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ']);
    exit;
}

try {
    // Admin
    if ($username === 'admin' && $password === '123456') {
        $_SESSION['role'] = 'admin';
        $_SESSION['hoten'] = 'Quản trị viên';
        $_SESSION['username'] = 'admin';
        echo json_encode(['success' => true, 'role' => 'admin', 'hoten' => 'Quản trị viên']);
        exit;
    }

    // Giảng viên
    // Giảng viên
$stmt = $pdo->prepare("SELECT MaGV, HoTen FROM giang_vien WHERE MaGV = ? AND MatKhau = ?");
$stmt->execute([$username, $password]);

if ($gv = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $_SESSION['role'] = 'giangvien';
    $_SESSION['MaGV'] = $gv['MaGV'];
    $_SESSION['hoten'] = $gv['HoTen'];

    // ===== THÊM DUY NHẤT KHÚC NÀY =====
    $stmt_pb = $pdo->prepare("
        SELECT COUNT(*) 
        FROM danh_sach_sinh_vien 
        WHERE gvpb_id = ?
    ");
    $stmt_pb->execute([$gv['MaGV']]);
    $_SESSION['is_gvpb'] = $stmt_pb->fetchColumn() > 0;
    // ===== HẾT =====

    echo json_encode([
        'success' => true,
        'role' => 'giangvien',
        'hoten' => $gv['HoTen'],
        'username' => $gv['MaGV'],
        'is_gvpb' => $_SESSION['is_gvpb']
    ]);
    exit;
}

    // Sinh viên
    $stmt = $pdo->prepare("SELECT MaSV, HoTen FROM danh_sach_sinh_vien WHERE MaSV = ? AND MatKhau = ?");
    $stmt->execute([$username, $password]);
    if ($sv = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['role'] = 'sinhvien';
        $_SESSION['MaSV'] = $sv['MaSV'];
        $_SESSION['hoten'] = $sv['HoTen'];
        echo json_encode([
            'success' => true,
            'role' => 'sinhvien',
            'hoten' => $sv['HoTen'],
            'username' => $sv['MaSV']
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Sai tên đăng nhập hoặc mật khẩu!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
}

<?php
session_start();
require_once 'config.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['role'])) {
    echo '<div class="alert alert-danger m-5">Vui lòng đăng nhập lại!</div>';
    exit;
}

$role = $_SESSION['role'];
$user_name = $_SESSION['HoTen'] ?? 'Người dùng';
$mssv_magv = $role == 'sinhvien' ? ($_SESSION['MaSV'] ?? '') : ($_SESSION['MaGV'] ?? 'ADMIN');

// 2. THỐNG KÊ SỐ LIỆU
$stats = ['total_sv' => 0, 'total_gv' => 0, 'total_nhom' => 0, 'total_hd' => 0];

if ($role == 'admin' || $role == 'giangvien') {
    try {
        $stats['total_sv']   = $pdo->query("SELECT COUNT(*) FROM danh_sach_sinh_vien")->fetchColumn();
        $stats['total_gv']   = $pdo->query("SELECT COUNT(*) FROM giang_vien")->fetchColumn();
        $stats['total_nhom'] = $pdo->query("SELECT COUNT(*) FROM nhom")->fetchColumn();
        $stats['total_hd']   = $pdo->query("SELECT COUNT(*) FROM hoidong")->fetchColumn();
    } catch (Exception $e) {
        // Xử lý lỗi nhẹ nhàng nếu chưa có bảng
    }
}

// 3. THÔNG BÁO MỚI (Giữ nguyên)
$notifications = [
    ['date' => '20/12', 'title' => 'Lịch bảo vệ Khóa luận đợt 1', 'type' => 'danger'],
    ['date' => '18/12', 'title' => 'Hạn nộp báo cáo tuần 15', 'type' => 'warning'],
    ['date' => '15/12', 'title' => 'Danh sách phân công GVPB', 'type' => 'info']
];
?>

<style>
    /* CSS Riêng cho Dashboard */
    .hero-section {
        background: linear-gradient(135deg, #0050aa, #007bff);
        color: white; padding: 40px 30px;
        border-radius: 0 0 30px 30px;
        box-shadow: 0 10px 30px rgba(0, 80, 170, 0.2);
        margin-bottom: 40px; position: relative; overflow: hidden;
    }
    .hero-section::before {
        content: ''; position: absolute; top: -50px; right: -50px;
        width: 300px; height: 300px; background: rgba(255,255,255,0.1); border-radius: 50%;
    }
    
    .stat-card {
        background: white; border-radius: 20px; padding: 25px; border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s;
        height: 100%; display: flex; align-items: center; justify-content: space-between;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
    
    .stat-icon {
        width: 60px; height: 60px; border-radius: 15px;
        display: flex; align-items: center; justify-content: center; font-size: 1.8rem;
    }
    .icon-blue { background: #e3f2fd; color: #0050aa; }
    .icon-green { background: #e8f5e9; color: #2e7d32; }
    .icon-orange { background: #fff3e0; color: #ef6c00; }
    .icon-red { background: #ffebee; color: #da251c; }
    
    .stat-info h3 { margin: 0; font-size: 2rem; font-weight: 800; color: #2c3e50; }
    .stat-info p { margin: 0; color: #6c757d; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; }

    .feature-card {
        background: white; border-radius: 15px; padding: 20px; text-align: center;
        transition: 0.3s; border: 1px solid rgba(0,0,0,0.05); height: 100%;
        text-decoration: none; display: block; color: #2c3e50;
    }
    .feature-card:hover { background: #0050aa; color: white; transform: translateY(-5px); }
    .feature-card i { font-size: 2.5rem; margin-bottom: 15px; color: #0050aa; transition: 0.3s; }
    .feature-card:hover i { color: white; }
    .feature-card h6 { font-weight: 700; margin: 0; }

    .section-title {
        font-weight: 800; color: #0050aa; margin-bottom: 20px;
        border-left: 5px solid #da251c; padding-left: 15px; text-transform: uppercase; font-size: 1.2rem;
    }
    .notif-item { display: flex; align-items: start; padding: 15px 0; border-bottom: 1px solid #eee; }
    .notif-date {
        background: #f3f6f9; padding: 5px 10px; border-radius: 8px; font-weight: 700;
        font-size: 0.8rem; color: #0050aa; min-width: 80px; text-align: center; margin-right: 15px;
    }
</style>

<div class="dashboard-wrapper">
    
    <div class="hero-section">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-warning text-dark mb-2 fw-bold">HK1 - 2024-2025</span>
                    <h2 class="fw-bold mb-1">Xin chào, <?= htmlspecialchars($user_name) ?>! 👋</h2>
                    <p class="mb-0 opacity-75">
                        Vai trò: <strong><?= strtoupper($role) ?></strong> 
                        <?php if($mssv_magv) echo " | ID: $mssv_magv"; ?>
                    </p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <i class="fas fa-graduation-cap fa-5x" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pb-5">
        
        <?php if ($role !== 'sinhvien'): ?>
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-info">
                        <p>Tổng Sinh Viên</p>
                        <h3><?= $stats['total_sv'] ?></h3>
                    </div>
                    <div class="stat-icon icon-blue"><i class="fas fa-user-graduate"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-info">
                        <p>Nhóm Đề Tài</p>
                        <h3><?= $stats['total_nhom'] ?></h3>
                    </div>
                    <div class="stat-icon icon-green"><i class="fas fa-layer-group"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-info">
                        <p>Giảng Viên</p>
                        <h3><?= $stats['total_gv'] ?></h3>
                    </div>
                    <div class="stat-icon icon-orange"><i class="fas fa-chalkboard-teacher"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-info">
                        <p>Hội Đồng</p>
                        <h3><?= $stats['total_hd'] ?></h3>
                    </div>
                    <div class="stat-icon icon-red"><i class="fas fa-gavel"></i></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            
            <div class="col-lg-8">
                <h4 class="section-title">Chức năng quản lý</h4>
                <div class="row g-3">
                    
                    <?php if ($role === 'admin'): ?>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'danhsach_sinhvien.php')" class="feature-card">
                                <i class="fas fa-users"></i><h6>QL Sinh viên</h6>
                            </a>
                        </div>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'phancong_gvhd.php')" class="feature-card">
                                <i class="fas fa-user-check"></i><h6>Phân công GVHD</h6>
                            </a>
                        </div>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'danhsach_hd.php')" class="feature-card">
                                <i class="fas fa-users-cog"></i><h6>Tạo Hội đồng</h6>
                            </a>
                        </div>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'phancong_gvpb.php')" class="feature-card">
                                <i class="fas fa-exchange-alt"></i><h6>Phân GVPB</h6>
                            </a>
                        </div>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'nhap_diem_nhom.php')" class="feature-card">
                                <i class="fas fa-edit"></i><h6>Nhập điểm</h6>
                            </a>
                        </div>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'diem.php')" class="feature-card">
                                <i class="fas fa-chart-bar"></i><h6>Tổng hợp Điểm</h6>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($role === 'giangvien'): ?>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'danhsach_sinhvien.php')" class="feature-card">
                                <i class="fas fa-user-graduate"></i><h6>Sinh viên HD</h6>
                            </a>
                        </div>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'cham_diem.php')" class="feature-card">
                                <i class="fas fa-pen-nib"></i><h6>Chấm điểm</h6>
                            </a>
                        </div>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'lich_hoidong.php')" class="feature-card">
                                <i class="fas fa-calendar-alt"></i><h6>Lịch hội đồng</h6>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($role === 'sinhvien'): ?>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'thongtin_detai.php')" class="feature-card">
                                <i class="fas fa-book"></i><h6>Thông tin Đề tài</h6>
                            </a>
                        </div>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'xem_diem.php')" class="feature-card">
                                <i class="fas fa-star"></i><h6>Xem kết quả</h6>
                            </a>
                        </div>
                        <div class="col-md-4 col-6">
                            <a href="#" onclick="loadFile(this, 'nop_bao_cao.php')" class="feature-card">
                                <i class="fas fa-file-upload"></i><h6>Nộp báo cáo</h6>
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="col-lg-4">
                <h4 class="section-title">Thông báo mới</h4>
                <div class="bg-white rounded-4 p-3 shadow-sm h-100" style="min-height: 300px;">
                    <?php foreach($notifications as $notif): ?>
                        <div class="notif-item">
                            <div class="notif-date"><?= $notif['date'] ?></div>
                            <div class="notif-content">
                                <h6 class="text-<?= $notif['type'] ?>"><?= $notif['title'] ?></h6>
                                <span class="text-muted small">Phòng Đào tạo</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center mt-3">
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-4">Xem tất cả</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer class="text-center py-4 mt-5 text-muted small border-top bg-white">
        <strong>TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN (STU)</strong><br>
        Hệ thống Quản lý Khóa luận Tốt nghiệp &copy; <?= date('Y') ?>
    </footer>
</div>
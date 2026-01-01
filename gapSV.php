<?php
// Bắt đầu session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// 1. KIỂM TRA QUYỀN TRUY CẬP
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'giangvien') {
    // Nếu bạn muốn Admin cũng xem được file này để test thì mở comment dòng dưới
    // if ($_SESSION['role'] !== 'admin') 
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}

// 2. KHỞI TẠO BIẾN $MaGV (SỬA LỖI UNDEFINED VARIABLE)
// Kiểm tra xem hệ thống đăng nhập lưu mã GV vào key nào
if (isset($_SESSION['MaGV'])) {
    $MaGV = $_SESSION['MaGV'];
} elseif (isset($_SESSION['user_id'])) {
    $MaGV = $_SESSION['user_id']; // Đa số hệ thống lưu ID vào đây
} elseif (isset($_SESSION['username'])) {
    $MaGV = $_SESSION['username'];
} else {
    $MaGV = ''; // Gán rỗng để không bị lỗi crash trang
}

// Nếu không tìm thấy mã GV thì báo lỗi
if (empty($MaGV)) {
    die('<div class="alert alert-warning m-5">Lỗi: Không tìm thấy mã Giảng viên trong phiên đăng nhập. Vui lòng đăng xuất và đăng nhập lại.</div>');
}

// ===================================================
// 3. XỬ LÝ TOGGLE ĐÃ GẶP
// ===================================================
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $nhom_id = (int)$_GET['toggle'];

    // Kiểm tra GV hiện tại có phải GVHD của nhóm này không
    $check = $pdo->prepare("SELECT da_gap_gv FROM nhom WHERE id = ? AND giang_vien_huong_dan_id = ?");
    $check->execute([$nhom_id, $MaGV]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $new = $row['da_gap_gv'] ? 0 : 1;
        $pdo->prepare("UPDATE nhom SET da_gap_gv = ? WHERE id = ?")->execute([$new, $nhom_id]);
    }

    // Redirect để tránh resubmit form
    $q = $_GET;
    unset($q['toggle']);
    header("Location: gapSV.php?" . http_build_query($q));
    exit;
}

// ===================================================
// 4. LẤY DANH SÁCH NHÓM
// ===================================================
$search = trim($_GET['search'] ?? '');

// Logic tìm kiếm
$params = [$MaGV, $MaGV]; // Tham số cho WHERE (GVHD hoặc GVPB)
$where_search = "";

if ($search) {
    $where_search = " AND (n.ten_nhom LIKE ? OR n.id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// SQL Query: Lấy nhóm mà GV này là GVHD HOẶC GVPB
$sql = "
SELECT
    n.id AS nhom_id,
    n.ten_nhom,
    n.da_gap_gv,
    n.giang_vien_huong_dan_id,
    gv_hd.HoTen AS ten_gvhd,
    (
        SELECT GROUP_CONCAT(gv.HoTen SEPARATOR ', ')
        FROM phan_cong_phan_bien pc
        JOIN giang_vien gv ON gv.MaGV = pc.giang_vien_phan_bien_id
        WHERE pc.nhom_id = n.id
    ) AS ten_gvpb
FROM nhom n
LEFT JOIN giang_vien gv_hd ON gv_hd.MaGV = n.giang_vien_huong_dan_id
WHERE
    (n.giang_vien_huong_dan_id = ? 
    OR EXISTS (
        SELECT 1 FROM phan_cong_phan_bien pc 
        WHERE pc.nhom_id = n.id AND pc.giang_vien_phan_bien_id = ?
    ))
    $where_search
ORDER BY n.da_gap_gv ASC, n.id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$nhom_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===================================================
// 5. THỐNG KÊ
// ===================================================
$stat_sql = "
SELECT
    COUNT(DISTINCT n.id) AS tong,
    COUNT(DISTINCT CASE WHEN n.da_gap_gv = 1 THEN n.id END) AS da_gap
FROM nhom n
LEFT JOIN phan_cong_phan_bien pc ON pc.nhom_id = n.id
WHERE n.giang_vien_huong_dan_id = ? OR pc.giang_vien_phan_bien_id = ?
";

$stat = $pdo->prepare($stat_sql);
$stat->execute([$MaGV, $MaGV]);
$s = $stat->fetch(PDO::FETCH_ASSOC);

$tong_nhom = $s['tong'] ?? 0;
$da_gap    = $s['da_gap'] ?? 0;
$chua_gap  = $tong_nhom - $da_gap;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đánh dấu gặp nhóm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .table thead { background-color: #6c5ce7; color: white; }
        .badge-status { min-width: 100px; padding: 8px; }
        tr.da-gap { background-color: #f0fff4 !important; }
        tr.chua-gap { background-color: #fff5f5 !important; }
    </style>
</head>
<body>

<div class="container py-4">
    <h3 class="text-center text-primary fw-bold mb-4" style="color: #6c5ce7 !important;">ĐÁNH DẤU ĐÃ GẶP NHÓM</h3>
    
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <form method="get" class="d-flex shadow-sm rounded-pill overflow-hidden bg-white">
                <input type="text" name="search" class="form-control border-0 px-4 py-2 shadow-none" 
                       placeholder="Nhập tên nhóm..." value="<?=htmlspecialchars($search)?>">
                <button class="btn btn-primary px-4 rounded-0" style="background: #6c5ce7;">Tìm</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-center">
                        <tr>
                            <th>STT</th>
                            <th>Tên nhóm</th>
                            <th>GVHD</th>
                            <th>GVPB</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($nhom_list)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">Không tìm thấy nhóm nào</td></tr>
                        <?php else: ?>
                            <?php $stt=1; foreach($nhom_list as $n): ?>
                            <tr class="<?= $n['da_gap_gv'] ? 'da-gap' : 'chua-gap' ?>">
                                <td class="text-center fw-bold text-muted"><?=$stt++?></td>
                                <td class="fw-bold text-primary"><?=htmlspecialchars($n['ten_nhom'])?></td>
                                <td><?=htmlspecialchars($n['ten_gvhd'] ?? '')?></td>
                                <td><small><?=htmlspecialchars($n['ten_gvpb'] ?? '')?></small></td>
                                <td class="text-center">
                                    <?php if($n['da_gap_gv']): ?>
                                        <span class="badge bg-success badge-status rounded-pill">Đã gặp</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger badge-status rounded-pill">Chưa gặp</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($n['giang_vien_huong_dan_id'] == $MaGV): ?>
                                        <a href="?toggle=<?=$n['nhom_id']?>&search=<?=urlencode($search)?>" 
                                           class="btn btn-sm <?= $n['da_gap_gv']?'btn-outline-secondary':'btn-primary' ?> rounded-pill px-3">
                                            <?= $n['da_gap_gv'] ? 'Hủy đánh dấu' : 'Xác nhận gặp' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Chỉ xem (GVPB)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 text-center">
        <div class="col-md-4">
            <div class="card p-3 border-0 bg-white">
                <small class="text-muted text-uppercase fw-bold">Tổng nhóm</small>
                <h2 class="mb-0 text-dark"><?= $tong_nhom ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 bg-white">
                <small class="text-muted text-uppercase fw-bold">Đã gặp</small>
                <h2 class="mb-0 text-success"><?= $da_gap ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 bg-white">
                <small class="text-muted text-uppercase fw-bold">Chưa gặp</small>
                <h2 class="mb-0 text-danger"><?= $chua_gap ?></h2>
            </div>
        </div>
    </div>
</div>

</body>
</html>
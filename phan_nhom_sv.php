<?php
session_start();
require_once 'config.php';

// === CHẶN ADMIN, CHỈ CHO GIẢNG VIÊN VÀO ===
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'giangvien') {
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}

$MaGV = $_SESSION['MaGV'];

// =======================
// XỬ LÝ GÁN NHÓM CHO NHIỀU SV
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'gan_nhom') {
    header('Content-Type: application/json; charset=utf-8');

    $sinhvien_ids = $_POST['sinhvien_ids'] ?? [];
    $ten_nhom = trim($_POST['ten_nhom'] ?? '');

    // --- SỬA ĐỔI: Kiểm tra xem tên nhóm có phải là số không ---
    if (empty($sinhvien_ids)) {
        echo json_encode(['success' => false, 'msg' => 'Chưa chọn sinh viên!']);
        exit;
    }
    
    // Kiểm tra rỗng hoặc không phải số
    if ($ten_nhom === '' || !is_numeric($ten_nhom)) {
        echo json_encode(['success' => false, 'msg' => 'Tên nhóm phải là một số (Ví dụ: 1, 2, 3)!']);
        exit;
    }
    // ---------------------------------------------------------

    try {
        $pdo->beginTransaction();

        // Lấy hoặc tạo nhóm cho giảng viên này
        $stmt = $pdo->prepare("SELECT id FROM nhom WHERE giang_vien_huong_dan_id = ? AND ten_nhom = ?");
        $stmt->execute([$MaGV, $ten_nhom]);
        $nhom_id = $stmt->fetchColumn();

        if (!$nhom_id) {
            $stmt = $pdo->prepare("INSERT INTO nhom (ten_nhom, giang_vien_huong_dan_id) VALUES (?, ?)");
            $stmt->execute([$ten_nhom, $MaGV]);
            $nhom_id = $pdo->lastInsertId();
        }

        // Xóa phân nhóm cũ của các SV này (nếu có)
        $placeholders = str_repeat('?,', count($sinhvien_ids) - 1) . '?';
        $pdo->prepare("DELETE FROM phan_thuoc_nhom WHERE sinh_vien_id IN ($placeholders)")
             ->execute($sinhvien_ids);

        // Gán vào nhóm mới
        $stmt = $pdo->prepare("INSERT INTO phan_thuoc_nhom (nhom_id, sinh_vien_id) VALUES (?, ?)");
        foreach ($sinhvien_ids as $sv_id) {
            $stmt->execute([$nhom_id, $sv_id]);
        }

        $pdo->commit();
        echo json_encode([
            'success' => true,
            'msg' => "Đã gán thành công " . count($sinhvien_ids) . " sinh viên vào nhóm số <strong>$ten_nhom</strong>!"
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'msg' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// =======================
// LẤY DANH SÁCH SV CỦA GIẢNG VIÊN NÀY
// =======================
// Lưu ý: CAST(n.ten_nhom AS UNSIGNED) để sắp xếp số nhóm chính xác (1, 2, 10 thay vì 1, 10, 2)
$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.MaSV,
        s.HoTen,
        n.ten_nhom AS nhom_hien_tai
    FROM danh_sach_sinh_vien s
    INNER JOIN phan_thuoc_nhom ptn ON s.id = ptn.sinh_vien_id
    INNER JOIN nhom n ON ptn.nhom_id = n.id
    WHERE n.giang_vien_huong_dan_id = ?
    ORDER BY CAST(n.ten_nhom AS UNSIGNED), s.HoTen
");
$stmt->execute([$MaGV]);
$sinhvien = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Phân nhóm sinh viên - Giảng viên</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    body {
        background: linear-gradient(135deg, #667eea, #764ba2);
        min-height: 100vh;
        font-family: 'Segoe UI', sans-serif;
    }
    .container { padding: 30px 0; }
    .card {
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        overflow: hidden;
    }
    .card-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
    }
    .table th {
        background: #5a67d8;
        color: white;
        text-align: center;
    }
    .btn-gan-nhom {
        background: linear-gradient(45deg, #11998e, #38ef7d);
        border: none;
        font-weight: bold;
        border-radius: 12px;
        padding: 12px;
    }
    .btn-gan-nhom:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    .badge-nhom {
        font-size: 1rem;
        padding: 8px 16px;
        border-radius: 50px;
    }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header text-center">
            PHÂN NHÓM SINH VIÊN
            <small class="d-block mt-2 opacity-75">
                Giảng viên: <strong><?= htmlspecialchars($_SESSION['HoTen'] ?? 'N/A') ?></strong>
            </small>
        </div>
        <div class="card-body p-4">

            <div class="row g-4">
                <div class="col-lg-8">
                    <h5 class="fw-bold text-primary mb-3">
                        Sinh viên đang hướng dẫn (<?= count($sinhvien) ?> người)
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="checkAll"></th>
                                    <th>Mã SV</th>
                                    <th>Họ tên</th>
                                    <th>Nhóm hiện tại</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sinhvien as $sv): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="chk-sv" value="<?= $sv['id'] ?>">
                                    </td>
                                    <td><strong><?= htmlspecialchars($sv['MaSV']) ?></strong></td>
                                    <td><?= htmlspecialchars($sv['HoTen']) ?></td>
                                    <td>
                                        <?php if ($sv['nhom_hien_tai']): ?>
                                            <span class="badge bg-success badge-nhom"><?= htmlspecialchars($sv['nhom_hien_tai']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Chưa có nhóm</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="bg-white rounded-3 shadow p-4" style="border: 2px dashed #667eea;">
                        <h5 class="text-center text-primary fw-bold mb-4">GÁN NHÓM MỚI</h5>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nhập Số Nhóm:</label>
                            <input type="number" id="ten-nhom" class="form-control form-control-lg text-center" 
                                   placeholder="VD: 1, 2, 3..." min="1" step="1" required>
                        </div>

                        <button onclick="ganNhom()" class="btn btn-gan-nhom btn-lg w-100 text-white shadow">
                            GÁN NHÓM CHO CÁC SV ĐÃ CHỌN
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Check all
document.getElementById('checkAll').onclick = function() {
    document.querySelectorAll('.chk-sv').forEach(c => c.checked = this.checked);
};

// Gán nhóm
function ganNhom() {
    const tenNhom = document.getElementById('ten-nhom').value.trim();
    
    // --- SỬA ĐỔI: Kiểm tra client side ---
    if (!tenNhom) {
        Swal.fire('Lỗi', 'Vui lòng nhập số nhóm!', 'error');
        return;
    }
    if (isNaN(tenNhom) || parseInt(tenNhom) <= 0) {
         Swal.fire('Lỗi', 'Tên nhóm phải là số dương hợp lệ!', 'error');
         return;
    }
    // ------------------------------------

    const checked = document.querySelectorAll('.chk-sv:checked');
    if (checked.length === 0) {
        Swal.fire('Lỗi', 'Chưa chọn sinh viên nào!', 'warning');
        return;
    }

    const svIds = Array.from(checked).map(c => c.value);

    Swal.fire({
        title: 'Xác nhận?',
        text: `Gán ${checked.length} sinh viên vào nhóm số: ${tenNhom}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý'
    }).then(result => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('action', 'gan_nhom');
            fd.append('ten_nhom', tenNhom);
            svIds.forEach(id => fd.append('sinhvien_ids[]', id));

            fetch('', {method: 'POST', body: fd})
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Swal.fire('Thành công!', d.msg, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Lỗi', d.msg, 'error');
                    }
                });
        }
    });
}
</script>

</body>
</html>
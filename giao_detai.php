<?php
session_start();
require_once 'config.php';

// Chỉ giảng viên mới được vào
if (!isset($_SESSION['role']) ||$_SESSION['role'] !== 'giangvien')
{
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}

$MaGV = $_SESSION['MaGV'];

// Xử lý giao đề tài
if ($_POST['action'] ?? '' === 'giao_detai') {
    header('Content-Type: application/json; charset=utf-8');

    $nhom_ids = $_POST['nhom_ids'] ?? [];
    $detai = trim($_POST['detai'] ?? '');

    if (empty($nhom_ids) || empty($detai)) {
        echo json_encode(['success' => false, 'msg' => 'Chưa chọn nhóm hoặc chưa nhập đề tài!']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $placeholders = str_repeat('?,', count($nhom_ids) - 1) . '?';

        // Cập nhật đề tài cho tất cả sinh viên trong các nhóm được chọn
        $pdo->prepare("
            UPDATE danh_sach_sinh_vien s
            INNER JOIN phan_thuoc_nhom ptn ON s.id = ptn.sinh_vien_id
            SET s.huong_de_tai = ?
            WHERE ptn.nhom_id IN ($placeholders)
        ")->execute(array_merge([$detai], $nhom_ids));

        $pdo->commit();
        echo json_encode([
            'success' => true,
            'msg' => "Đã giao đề tài thành công cho " . count($nhom_ids) . " nhóm!"
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'msg' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}

// Lấy danh sách nhóm + số lượng SV + đề tài hiện tại của giảng viên này
$stmt = $pdo->prepare("
    SELECT 
        n.id AS nhom_id,
        n.ten_nhom,
        COUNT(ptn.sinh_vien_id) AS so_sv,
        s.huong_de_tai AS detai_hien_tai
    FROM nhom n
    LEFT JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
    LEFT JOIN danh_sach_sinh_vien s ON ptn.sinh_vien_id = s.id
    WHERE n.giang_vien_huong_dan_id = ?
    GROUP BY n.id, n.ten_nhom
    ORDER BY n.ten_nhom
");
$stmt->execute([$MaGV]);
$nhom_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Giao Đề Tài Cho Nhóm - Giảng Viên</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    body {
        background: linear-gradient(135deg, #667eea, #764ba2);
        min-height: 100vh;
        font-family: 'Segoe UI', sans-serif;
    }
    .container { padding: 40px 0; }
    .card {
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        overflow: hidden;
    }
    .card-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        font-size: 1.6rem;
        font-weight: bold;
        text-align: center;
        padding: 25px;
    }
    .table th {
        background: #5a67d8;
        color: white;
        text-align: center;
    }
    .btn-giao-detai {
        background: linear-gradient(45deg, #ff6b6b, #ee5a52);
        border: none;
        font-weight: bold;
        border-radius: 15px;
        padding: 15px;
        font-size: 1.1rem;
    }
    .btn-giao-detai:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(255,107,107,0.4);
    }
    .group-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: all 0.3s;
    }
    .group-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }
    .badge-group {
        font-size: 1.3rem;
        padding: 12px 25px;
        border-radius: 50px;
        background: linear-gradient(135deg, #11998e, #38ef7d);
        color: white;
        font-weight: bold;
    }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            GIAO ĐỀ TÀI CHO CÁC NHÓM
            <div class="mt-2 opacity-90">
                Giảng viên: <strong><?= htmlspecialchars($_SESSION['HoTen'] ?? 'N/A') ?></strong>
            </div>
        </div>

        <div class="card-body p-5">

            <form id="formGiaoDeTai">
                <input type="hidden" name="action" value="giao_detai">

                <div class="row g-4 align-items-end mb-5">
                    <div class="col-lg-8">
                        <label class="form-label text-primary fw-bold fs-5">
                            Nhập đề tài cần giao
                        </label>
                        <textarea name="detai" id="detai" class="form-control form-control-lg" 
                                  rows="3" placeholder="VD: Xây dựng website quản lý thư viện trực tuyến..." required></textarea>
                    </div>
                    <div class="col-lg-4">
                        <button type="submit" class="btn btn-giao-detai btn-lg w-100 text-white shadow">
                            GIAO ĐỀ TÀI NGAY
                        </button>
                    </div>
                </div>
            </form>

            <h4 class="text-center text-primary mb-4 fw-bold">
                DANH SÁCH NHÓM CỦA BẠN (<?= count($nhom_list) ?> nhóm)
            </h4>

            <div class="row g-4">
                <?php foreach ($nhom_list as $nhom): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="group-card text-center">
                        <div class="form-check">
                            <input class="form-check-input chk-nhom" type="checkbox" 
                                   value="<?= $nhom['nhom_id'] ?>" id="nhom<?= $nhom['nhom_id'] ?>">
                            <label class="form-check-label d-block" for="nhom<?= $nhom['nhom_id'] ?>">
                                <div class="badge-group mb-3">
                                    <?= htmlspecialchars($nhom['ten_nhom']) ?>
                                </div>
                                <div class="fs-5 mb-2">
                                    <strong><?= $nhom['so_sv'] ?></strong> sinh viên
                                </div>
                                <div class="text-muted small">
                                    Đề tài hiện tại:
                                </div>
                                <div class="fw-bold text-primary mt-1" style="min-height:50px;">
                                    <?= $nhom['detai_hien_tai'] ?: '<em class="text-danger">Chưa có đề tài</em>' ?>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($nhom_list)): ?>
            <div class="text-center py-5">
                <h5 class="text-muted">Bạn chưa có nhóm nào để giao đề tài</h5>
                <p>Hãy phân nhóm sinh viên trước trong trang <strong>Phân nhóm</strong></p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Check all nhóm
document.querySelectorAll('.chk-nhom').forEach(chk => {
    chk.addEventListener('click', function() {
        if ([...document.querySelectorAll('.chk-nhom:checked')].length > 0) {
            document.getElementById('detai').focus();
        }
    });
});

// Giao đề tài
document.getElementById('formGiaoDeTai').onsubmit = function(e) {
    e.preventDefault();

    const detai = document.getElementById('detai').value.trim();
    if (!detai) {
        Swal.fire('Lỗi', 'Vui lòng nhập đề tài!', 'error');
        return;
    }

    const checked = document.querySelectorAll('.chk-nhom:checked');
    if (checked.length === 0) {
        Swal.fire('Lỗi', 'Chưa chọn nhóm nào!', 'warning');
        return;
    }

    const nhomIds = Array.from(checked).map(c => c.value);

    Swal.fire({
        title: 'Xác nhận giao đề tài?',
        text: `Giao đề tài cho ${checked.length} nhóm đã chọn?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    }).then(result => {
        if (result.isConfirmed) {
            const fd = new FormData(this);
            nhomIds.forEach(id => fd.append('nhom_ids[]', id));

            fetch('', {method: 'POST', body: fd})
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Swal.fire('Thành công!', d.msg, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Lỗi', d.msg, 'error');
                    }
                });
        }
    });
};
</script>

</body>
</html>
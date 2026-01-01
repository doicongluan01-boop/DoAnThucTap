<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}

// ================= XỬ LÝ PHP =================

// 1. Xử lý Phân công hàng loạt (Chọn checkbox -> Chọn 1 GV ở trên -> Lưu)
if (($_POST['action'] ?? '') === 'phancong') {
    header('Content-Type: application/json; charset=utf-8');
    $sinhvien_ids = $_POST['sinhvien_ids'] ?? [];
    $magv = trim($_POST['magv'] ?? '');

    if (empty($sinhvien_ids) || empty($magv)) {
        echo json_encode(['success' => false, 'msg' => 'Vui lòng chọn sinh viên và giảng viên!']);
        exit;
    }
    phanCongSinhVien($pdo, $sinhvien_ids, $magv);
}

// 2. Xử lý Cập nhật tất cả (Quét từng dòng dropdown -> Lưu)
if (($_POST['action'] ?? '') === 'update_all') {
    header('Content-Type: application/json; charset=utf-8');
    $assignments = $_POST['assignments'] ?? []; // Mảng [sv_id => magv]

    $count = 0;
    try {
        $pdo->beginTransaction();
        foreach ($assignments as $sv_id => $magv) {
            // Chỉ xử lý nếu mã GV không rỗng
            if (!empty($magv)) {
                // Gọi hàm xử lý logic tách biệt
                if (phanCongSinhVienSingle($pdo, $sv_id, $magv)) {
                    $count++;
                }
            }
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'msg' => "Đã cập nhật dữ liệu cho $count sinh viên!"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'msg' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}

// --- Hàm hỗ trợ logic phân công ---
function phanCongSinhVien($pdo, $sinhvien_ids, $magv) {
    try {
        $pdo->beginTransaction();
        // Xóa phân công cũ
        $placeholders = str_repeat('?,', count($sinhvien_ids) - 1) . '?';
        $pdo->prepare("DELETE FROM phan_thuoc_nhom WHERE sinh_vien_id IN ($placeholders)")->execute($sinhvien_ids);

        // Lấy/Tạo nhóm
        $nhom_id = getOrCreateNhom($pdo, $magv);

        // Insert mới
        $stmt = $pdo->prepare("INSERT IGNORE INTO phan_thuoc_nhom (nhom_id, sinh_vien_id) VALUES (?, ?)");
        foreach ($sinhvien_ids as $sv_id) {
            $stmt->execute([$nhom_id, $sv_id]);
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'msg' => "Đã phân công xong " . count($sinhvien_ids) . " sinh viên!"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'msg' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}

// Hàm hỗ trợ cập nhật đơn lẻ (dùng trong vòng lặp update all)
function phanCongSinhVienSingle($pdo, $sv_id, $magv) {
    // 1. Kiểm tra xem SV này hiện tại đang thuộc nhóm của GV nào
    // Nếu trùng với GV mới thì bỏ qua để tối ưu
    // (Ở đây làm đơn giản: Xóa cũ -> Thêm mới luôn cho chắc)
    
    $pdo->prepare("DELETE FROM phan_thuoc_nhom WHERE sinh_vien_id = ?")->execute([$sv_id]);
    $nhom_id = getOrCreateNhom($pdo, $magv);
    $pdo->prepare("INSERT IGNORE INTO phan_thuoc_nhom (nhom_id, sinh_vien_id) VALUES (?, ?)")->execute([$nhom_id, $sv_id]);
    return true;
}

function getOrCreateNhom($pdo, $magv) {
    $stmt = $pdo->prepare("SELECT id FROM nhom WHERE giang_vien_huong_dan_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$magv]);
    $nhom_id = $stmt->fetchColumn();
    if (!$nhom_id) {
        $stmt = $pdo->prepare("INSERT INTO nhom (ten_nhom, giang_vien_huong_dan_id) VALUES (?, ?)");
        $stmt->execute(['Nhóm mặc định', $magv]);
        $nhom_id = $pdo->lastInsertId();
    }
    return $nhom_id;
}

// ================= LẤY DỮ LIỆU HIỂN THỊ =================
$giangvien_list = $pdo->query("SELECT MaGV, HoTen FROM giang_vien ORDER BY HoTen")->fetchAll(PDO::FETCH_ASSOC);

$sinhvien_list = $pdo->query("
    SELECT 
        s.id, s.MaSV, s.HoTen, s.Lop, 
        COALESCE(s.huong_de_tai, 'Chưa có') AS detai,
        n.giang_vien_huong_dan_id AS current_magv
    FROM danh_sach_sinh_vien s
    LEFT JOIN phan_thuoc_nhom ptn ON s.id = ptn.sinh_vien_id
    LEFT JOIN nhom n ON ptn.nhom_id = n.id
    ORDER BY s.Lop, s.MaSV
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phân Công GVHD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.05); }
        .card-header { background: #4e73df; color: white; border-radius: 15px 15px 0 0 !important; }
        .table th { background: #f8f9fc; color: #4e73df; vertical-align: middle; }
        .form-select-sm { min-width: 180px; border-color: #d1d3e2; }
        .form-select-sm:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78,115,223,0.25); }
        .row-highlight { background-color: #e8f0fe; } /* Màu khi tick chọn */
        
        /* Floating Update Button */
        .btn-update-all {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            padding: 15px 25px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        .btn-update-all:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.3); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card mb-5">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h4 class="m-0 fw-bold">PHÂN CÔNG GIẢNG VIÊN HƯỚNG DẪN</h4>
            <span class="badge bg-light text-primary"><?= count($sinhvien_list) ?> Sinh viên</span>
        </div>
        
        <div class="card-body">
            <form id="mainForm">
                <input type="hidden" name="action" id="actionType" value="">

                <div class="row g-3 mb-4 bg-light p-3 rounded border">
                    <div class="col-md-8">
                        <label class="small text-muted fw-bold mb-1">PHÂN CÔNG NHANH (Cho các mục đã chọn):</label>
                        <select name="magv" class="form-select">
                            <option value="">-- Chọn giảng viên để gán hàng loạt --</option>
                            <?php foreach ($giangvien_list as $gv): ?>
                                <option value="<?= $gv['MaGV'] ?>"><?= $gv['HoTen'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" onclick="submitBulkAssign()" class="btn btn-primary w-100">
                            <i class="bi bi-check2-all"></i> Áp dụng cho mục đã chọn
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead>
                            <tr>
                                <th width="40" class="text-center"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                <th width="50">STT</th>
                                <th>Thông tin Sinh viên</th>
                                <th>Đề tài</th>
                                <th width="250">GVHD Hiện tại (Cập nhật trực tiếp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1; foreach ($sinhvien_list as $sv): ?>
                            <tr class="<?= !empty($sv['current_magv']) ? 'table-light' : '' ?>">
                                <td class="text-center">
                                    <input type="checkbox" name="sinhvien_ids[]" value="<?= $sv['id'] ?>" class="form-check-input chk">
                                </td>
                                <td><?= $stt++ ?></td>
                                <td>
                                    <div class="fw-bold text-primary"><?= htmlspecialchars($sv['HoTen']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($sv['MaSV']) ?> - <?= htmlspecialchars($sv['Lop']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($sv['detai']) ?></td>
                                <td>
                                    <select name="assignments[<?= $sv['id'] ?>]" class="form-select form-select-sm gv-select" 
                                            onchange="highlightRow(this)">
                                        <option value="" class="text-muted">-- Chưa phân công --</option>
                                        <?php foreach ($giangvien_list as $gv): ?>
                                            <option value="<?= $gv['MaGV'] ?>" 
                                                <?= ($sv['current_magv'] == $gv['MaGV']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($gv['HoTen']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <button type="button" onclick="submitUpdateAll()" class="btn btn-warning text-dark btn-update-all">
                    💾 CẬP NHẬT TẤT CẢ THAY ĐỔI
                </button>

            </form>
        </div>
    </div>
</div>

<script>
    // 1. Xử lý Checkbox All
    document.getElementById('checkAll').onclick = function() {
        document.querySelectorAll('.chk').forEach(c => c.checked = this.checked);
    };

    // 2. Hiệu ứng khi thay đổi dropdown (đổi màu dòng để biết đã sửa)
    function highlightRow(selectElement) {
        selectElement.closest('tr').classList.add('table-warning');
    }

    // 3. Hàm gửi form: Phân công hàng loạt (Checkbox)
    function submitBulkAssign() {
        const checked = document.querySelectorAll('.chk:checked').length;
        const magv = document.querySelector('select[name="magv"]').value;

        if (checked === 0) return Swal.fire('Chưa chọn SV', 'Vui lòng tích chọn ít nhất 1 sinh viên!', 'warning');
        if (!magv) return Swal.fire('Chưa chọn GV', 'Vui lòng chọn giảng viên ở mục Phân công nhanh!', 'warning');

        Swal.fire({
            title: 'Xác nhận phân công?',
            text: `Gán ${checked} sinh viên đã chọn cho giảng viên này?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Đồng ý'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('actionType').value = 'phancong';
                submitForm();
            }
        });
    }

    // 4. Hàm gửi form: Cập nhật tất cả (Quét dropdown)
    function submitUpdateAll() {
        Swal.fire({
            title: 'Lưu toàn bộ?',
            text: "Hệ thống sẽ cập nhật GVHD cho tất cả sinh viên theo danh sách bạn đã chọn trong bảng.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Lưu thay đổi',
            confirmButtonColor: '#f6c23e'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('actionType').value = 'update_all';
                submitForm();
            }
        });
    }

    // Hàm gửi AJAX chung
    function submitForm() {
        const form = document.getElementById('mainForm');
        const formData = new FormData(form);

        // Hiện loading
        Swal.fire({ title: 'Đang xử lý...', didOpen: () => Swal.showLoading() });

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Thành công!', data.msg, 'success').then(() => location.reload());
            } else {
                Swal.fire('Lỗi!', data.msg, 'error');
            }
        })
        .catch(error => {
            console.error(error);
            Swal.fire('Lỗi hệ thống', 'Không thể kết nối đến server', 'error');
        });
    }
</script>

</body>
</html>
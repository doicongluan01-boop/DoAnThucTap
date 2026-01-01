<?php
session_start();
require 'config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}

// =========================================================================
// PHẦN 1: XỬ LÝ AJAX (Kiểm tra trùng lịch THEO NGÀY)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'check_conflict') {
    error_reporting(0);
    header('Content-Type: application/json');

    $gv = $_POST['gv_name'] ?? '';
    $thoigian = $_POST['thoigian'] ?? '';
    $current_id = $_POST['id'] ?? 0;

    if (empty($gv) || empty($thoigian)) {
        echo json_encode(['status' => 'ok']);
        exit;
    }

    // Chuyển format input (T) sang format chuẩn để dùng hàm DATE() trong SQL
    $time_db = str_replace('T', ' ', $thoigian);

    try {
        // CÂU TRUY VẤN MỚI: Dùng hàm DATE() để so sánh ngày
        // Logic: Nếu giảng viên nằm trong hội đồng khác mà có NGÀY trùng với ngày đang chọn -> Báo lỗi
        $sql = "SELECT ten_hoidong FROM hoidong 
                WHERE (chu_tich = :gv OR thu_ky = :gv OR uy_vien1 = :gv OR uy_vien2 = :gv) 
                AND DATE(thoigian) = DATE(:thoigian) 
                AND id != :id 
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':gv' => $gv,
            ':thoigian' => $time_db,
            ':id' => $current_id
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode([
                'status' => 'conflict',
                'message' => "Giảng viên $gv đã tham gia hội đồng \"{$result['ten_hoidong']}\" trong ngày này rồi!"
            ]);
        } else {
            echo json_encode(['status' => 'ok']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit; 
}

// =========================================================================
// PHẦN 2: GIAO DIỆN HTML
// =========================================================================
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Danh sách Hội đồng bảo vệ LVTN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .table th { background: #0d6efd; color: white; }
    </style>
</head>

<body>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Thành công!</strong> Đã lưu thông tin hội đồng.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="container-fluid py-4">
        <h2 class="mb-4 text-primary"><i class="bi bi-building"></i> HỘI ĐỒNG LVTN KHOA CNTT</h2>

        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">DANH SÁCH HỘI ĐỒNG</h5>
                <div>
                    <button class="btn btn-light me-2" onclick="location.href='exportexcel_hd.php'">Xuất Excel</button>
                    <button class="btn btn-warning" onclick="window.open('inAll_giaymoi_hd.php','_blank')">In tất cả giấy mời</button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="resetForm()">+ Tạo hội đồng mới</button>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="search" class="form-control" placeholder="Tìm kiếm hội đồng...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tableHoiDong">
                        <thead>
                            <tr>
                                <th width="50">STT</th>
                                <th>Tên hội đồng</th>
                                <th>Chủ tịch</th>
                                <th>Thư ký</th>
                                <th>Ủy viên 1</th>
                                <th>Ủy viên 2</th>
                                <th>Phòng</th>
                                <th>Thời gian</th>
                                <th width="80">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM hoidong ORDER BY ten_hoidong");
                            $stt = 1;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                                <tr>
                                    <td><?= $stt++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['ten_hoidong']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['chu_tich']) ?></td>
                                    <td><?= htmlspecialchars($row['thu_ky']) ?></td>
                                    <td><?= htmlspecialchars($row['uy_vien1']) ?></td>
                                    <td><?= htmlspecialchars($row['uy_vien2']) ?></td>
                                    <td><?= htmlspecialchars($row['phong']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['thoigian'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick='edit(<?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-danger" onclick="xoa(<?= $row['id'] ?>)"><i class="bi bi-trash"></i></button>
                                        <button class="btn btn-sm btn-info text-white" onclick="window.open('in_giaymoi_hd.php?id=<?= $row['id'] ?>','_blank')"><i class="bi bi-printer"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalForm" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Tạo hội đồng mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formHoiDong" method="post" action="them_hd.php">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên hội đồng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ten_hoidong" id="ten_hoidong" required onblur="formatTenHoiDong(this)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phòng bảo vệ</label>
                                <input type="text" class="form-control" name="phong" id="phong">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ngày giờ bảo vệ <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="thoigian" id="thoigian" required>
                            </div>
                        </div>
                        <hr>
                        <div class="row g-3">
                            <?php 
                            $gv = $pdo->query("SELECT HoTen FROM giang_vien ORDER BY HoTen")->fetchAll(); 
                            $positions = ['chu_tich' => 'Chủ tịch', 'thu_ky' => 'Thư ký', 'uy_vien1' => 'Ủy viên 1', 'uy_vien2' => 'Ủy viên 2'];
                            foreach($positions as $key => $label):
                            ?>
                            <div class="col-md-6">
                                <label><?= $label ?> <?= ($key=='chu_tich' || $key=='thu_ky') ? '<span class="text-danger">*</span>' : '' ?></label>
                                <select class="form-select select-gv" name="<?= $key ?>" id="<?= $key ?>" <?= ($key=='chu_tich' || $key=='thu_ky') ? 'required' : '' ?>>
                                    <option value="">-- Chọn giảng viên --</option>
                                    <?php foreach ($gv as $g): ?>
                                        <option value="<?= htmlspecialchars($g['HoTen']) ?>"><?= htmlspecialchars($g['HoTen']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu hội đồng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- 1. HÀM FORMAT TÊN HỘI ĐỒNG TỰ ĐỘNG ---
        function formatTenHoiDong(input) {
            let val = input.value.trim();
            if (val === "") return;

            // Chuyển về chữ thường để kiểm tra
            let lowerVal = val.toLowerCase();

            // Nếu chưa có chữ "hội đồng" ở đầu thì thêm vào
            if (!lowerVal.startsWith('hội đồng')) {
                // Viết hoa chữ cái đầu và thêm nội dung người dùng nhập
                input.value = "Hội đồng " + val;
            } else {
                // Nếu người dùng lỡ nhập "hội đồng 1" (chữ thường) -> chuẩn hóa thành "Hội đồng 1"
                // Logic: Cắt bỏ chữ hội đồng cũ, nối lại
                let content = val.substring(8).trim(); // 8 là độ dài chuỗi "hội đồng" (ước lượng) hoặc dùng regex
                // Cách đơn giản hơn: Dùng Regex thay thế
                input.value = val.replace(/^hội đồng/i, "Hội đồng");
            }
        }

        // --- 2. HÀM KIỂM TRA TRÙNG (THEO NGÀY) ---
        async function checkDatabaseConflict() {
            const timeVal = document.getElementById('thoigian').value;
            const currentId = document.getElementById('id').value;
            const selects = document.querySelectorAll('.select-gv');
            const errorDiv = document.getElementById('error-message');
            const btnSubmit = document.querySelector('#formHoiDong button[type="submit"]');

            errorDiv.style.display = 'none';
            btnSubmit.disabled = false;

            if (!timeVal) return;

            for (let select of selects) {
                let gvName = select.value;
                if (gvName && gvName !== "-- Chọn giảng viên --") {
                    
                    const formData = new FormData();
                    formData.append('action', 'check_conflict');
                    formData.append('gv_name', gvName);
                    formData.append('thoigian', timeVal);
                    formData.append('id', currentId);

                    try {
                        const response = await fetch('danhsach_hd.php', { method: 'POST', body: formData });
                        const data = await response.json();

                        if (data.status === 'conflict') {
                            errorDiv.style.display = 'block';
                            errorDiv.innerHTML = `<strong>LỖI:</strong> ${data.message}`;
                            btnSubmit.disabled = true;
                            return; 
                        }
                    } catch (error) { console.error(error); }
                }
            }
        }

        // --- 3. KIỂM TRA TRÙNG NỘI BỘ (1 người 2 chức vụ) ---
        function checkInternalConflict() {
            const selects = document.querySelectorAll('.select-gv');
            let selected = [];
            selects.forEach(s => {
                if (s.value && s.value !== "-- Chọn giảng viên --") selected.push(s.value);
            });

            let unique = new Set(selected);
            const errorDiv = document.getElementById('error-message');
            const btnSubmit = document.querySelector('#formHoiDong button[type="submit"]');

            if (selected.length !== unique.size) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = '<strong>LỖI:</strong> Một giảng viên không thể giữ nhiều chức vụ trong cùng một hội đồng!';
                btnSubmit.disabled = true;
                return false;
            } else {
                if (errorDiv.innerHTML.includes('cùng một hội đồng')) {
                    errorDiv.style.display = 'none';
                    btnSubmit.disabled = false;
                }
                return true;
            }
        }

        // Event Listeners
        document.getElementById('thoigian').addEventListener('change', checkDatabaseConflict);
        document.querySelectorAll('.select-gv').forEach(item => {
            item.addEventListener('change', async function() {
                if(checkInternalConflict()) await checkDatabaseConflict();
            });
        });

        // Utils
        function resetForm() {
            document.getElementById('formHoiDong').reset();
            document.getElementById('modalTitle').innerText = 'Tạo hội đồng mới';
            document.getElementById('formHoiDong').action = 'them_hd.php';
            document.getElementById('id').value = '';
            document.getElementById('error-message').style.display = 'none';
            document.querySelector('#formHoiDong button[type="submit"]').disabled = false;
        }

        function edit(data) {
            resetForm();
            document.getElementById('modalTitle').innerText = 'Sửa hội đồng';
            document.getElementById('formHoiDong').action = 'sua_hd.php';
            document.getElementById('id').value = data.id;
            document.getElementById('ten_hoidong').value = data.ten_hoidong;
            document.getElementById('phong').value = data.phong;
            document.getElementById('thoigian').value = data.thoigian.replace(' ', 'T');
            document.getElementById('chu_tich').value = data.chu_tich;
            document.getElementById('thu_ky').value = data.thu_ky;
            document.getElementById('uy_vien1').value = data.uy_vien1;
            document.getElementById('uy_vien2').value = data.uy_vien2;
            new bootstrap.Modal(document.getElementById('modalForm')).show();
        }

        function xoa(id) {
            if (confirm('Bạn chắc chắn muốn xóa hội đồng này?')) location.href = 'xoa_hd.php?id=' + id;
        }

        document.getElementById('search').addEventListener('keyup', function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll('#tableHoiDong tbody tr').forEach(tr => {
                tr.style.display = tr.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
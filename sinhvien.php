<?php
session_start();
require_once 'config.php';

// 1. KIỂM TRA QUYỀN (GIỮ NGUYÊN)
if (!isset($_SESSION['role'])) {
    http_response_code(403);
    echo '<div class="alert alert-danger text-center p-5">
            <h4>Truy cập bị từ chối</h4>
            Vui lòng <a href="login.php">đăng nhập</a> để xem danh sách.
          </div>';
    exit;
}

$isAdmin     = $_SESSION['role'] === 'admin';
$isGiangVien = $_SESSION['role'] === 'giangvien';
$MaGV        = $isGiangVien ? $_SESSION['MaGV'] : null;

// 2. TRUY VẤN DỮ LIỆU (GIỮ NGUYÊN)
if ($isGiangVien) {
    $sql = "
        SELECT DISTINCT
            s.*,
            n.id AS nhom_id,
            n.ten_nhom AS nhom,
            gv.HoTen AS gvhd_ten
        FROM danh_sach_sinh_vien s
        INNER JOIN phan_thuoc_nhom ptn ON ptn.sinh_vien_id = s.id
        INNER JOIN nhom n ON n.id = ptn.nhom_id
        LEFT JOIN giang_vien gv ON n.giang_vien_huong_dan_id = gv.MaGV
        WHERE
            n.giang_vien_huong_dan_id = ?
            OR EXISTS (
                SELECT 1
                FROM phan_cong_phan_bien pc
                WHERE pc.nhom_id = n.id
                  AND pc.giang_vien_phan_bien_id = ?
            )
        ORDER BY n.id, s.HoTen
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$MaGV, $MaGV]);
} else {
    $sql = "
        SELECT
            s.*,
            n.id AS nhom_id,
            n.ten_nhom AS nhom,
            gv.HoTen AS gvhd_ten
        FROM danh_sach_sinh_vien s
        LEFT JOIN phan_thuoc_nhom ptn ON ptn.sinh_vien_id = s.id
        LEFT JOIN nhom n ON n.id = ptn.nhom_id
        LEFT JOIN giang_vien gv ON n.giang_vien_huong_dan_id = gv.MaGV
        ORDER BY 
            CASE WHEN n.id IS NULL THEN 1 ELSE 0 END,
            n.id,
            s.HoTen
    ";
    $stmt = $pdo->query($sql);
}

$sv = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. THỐNG KÊ (GIỮ NGUYÊN)
$totalSV = count($sv);
$ungrouped = 0;
$nhomSet = [];

foreach ($sv as $s) {
    if (!empty($s['nhom_id'])) {
        $nhomSet[$s['nhom_id']] = true;
    } else {
        $ungrouped++;
    }
}
$totalNhom = count($nhomSet);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách sinh viên</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .content-area {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin: 20px auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            max-width: 1400px;
        }
        .stat-cards { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 25px; }
        .stat-big {
            flex: 1; min-width: 200px; padding: 20px; border-radius: 10px; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
        }
        .bg-primary { background-color: #4e54c8 !important; }
        .bg-success { background-color: #10b981 !important; }
        .bg-warning { background-color: #f59e0b !important; color: #fff !important; }
        
        .stat-big h2 { margin: 0; font-size: 2rem; font-weight: 700; }
        .stat-big h5 { margin: 0; font-size: 1rem; }
        .stat-big .icon { font-size: 2.5rem; opacity: 0.8; }

        .table thead th {
            background: #4e54c8; color: white; border-bottom: none; white-space: nowrap;
        }
        .table tbody tr:hover { background-color: #f8f9fa; }
        .badge { padding: 6px 12px; border-radius: 20px; font-weight: 500; }
        .btn-action { border: none; background: transparent; padding: 5px 10px; color: #dc3545; transition: 0.2s; }
        .btn-action:hover { transform: scale(1.2); }
    </style>
</head>
<body>

<div class="content-area">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-primary">DANH SÁCH SINH VIÊN</h4>
            <?php if ($isGiangVien): ?>
                <small class="text-muted">Đang hướng dẫn <strong class="text-primary"><?= $totalSV ?></strong> sinh viên</small>
            <?php endif; ?>
        </div>
         <?php if ($isAdmin): ?>
        <div class="btn-group">
            <button type="button" class="btn btn-success text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="fas fa-file-excel me-2"></i>Import Excel
            </button>
            <button class="btn btn-info btn-sm text-white shadow-sm" onclick="exportExcel()">
                <i class="fas fa-download me-2"></i>Excel
            </button>
            <button class="btn btn-primary btn-sm shadow-sm" onclick="exportWord()">
                <i class="fas fa-file-word me-2"></i>Word
            </button>
            <button class="btn btn-danger btn-sm shadow-sm ms-2 rounded" onclick="deleteAllStudents()">
                <i class="fas fa-trash me-2"></i>Xóa danh sách
            </button>
            </div>
            <?php endif; ?>
    </div>

    <div class="stat-cards">
        <div class="stat-big bg-primary">
            <div><h5>Tổng SV</h5><h2><?= $totalSV ?></h2></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
        <div class="stat-big bg-success">
            <div><h5>Nhóm</h5><h2><?= $totalNhom ?></h2></div>
            <div class="icon"><i class="fas fa-layer-group"></i></div>
        </div>
        <div class="stat-big bg-warning">
            <div><h5>Chưa nhóm</h5><h2><?= $ungrouped ?></h2></div>
            <div class="icon"><i class="fas fa-user-slash"></i></div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="mainTable" class="table table-hover align-middle w-100">
            <thead>
                <tr>
                    <th width="50" class="text-center">STT</th>
                    <th width="100">Mã SV</th>
                    <th>Họ tên</th>
                    <th>Lớp</th>
                    <th>Email / SDT</th>
                    <th>Nhóm</th>
                    <th>Hướng đề tài</th>
                    <th>GVHD</th>
                    <th>Ghi chú</th>
                    <?php if ($isAdmin): ?><th width="60" class="text-center">Xóa</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $stt = 1; foreach ($sv as $s): ?>
                <tr>
                    <td class="text-center fw-bold"><?= $stt++ ?></td>
                    <td class="text-primary fw-bold"><?= htmlspecialchars($s['MaSV']) ?></td>
                    <td><?= htmlspecialchars($s['HoTen']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['Lop'] ?? '') ?></span></td>
                    
                    <td>
                        <div class="small"><?= htmlspecialchars($s['Email'] ?? '') ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($s['SDT'] ?? '') ?></div>
                    </td>
                    
                    <td class="text-center">
                        <?php if (!empty($s['nhom'])): ?>
                            <span class="badge bg-success"><?= htmlspecialchars($s['nhom']) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Chưa có</span>
                        <?php endif; ?>
                    </td>
                    
                    <td class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($s['huong_de_tai']) ?>">
                        <?= htmlspecialchars($s['huong_de_tai'] ?: '*') ?>
                    </td>

                    <td class="fw-bold text-success">
                        <?php 
                            if (!empty($s['gvhd_ten'])) {
                                echo htmlspecialchars($s['gvhd_ten']);
                            } else {
                                echo '<span class="text-muted fw-normal small">Chưa phân công</span>';
                            }
                        ?>
                    </td>

                    <td><?= htmlspecialchars($s['ghichu'] ?: '') ?></td>

                    <?php if ($isAdmin): ?>
                    <td class="text-center">
                        <button type="button" class="btn-action delete-real" data-mssv="<?= $s['MaSV'] ?>" title="Xóa">
                            <i class="fas fa-trash-alt fa-lg"></i>
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="script.js"></script>
<script src="export_excel.js"></script>
<script src="export_word.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#mainTable').DataTable({
            language: {
                search: "Tìm kiếm:",
                lengthMenu: "Hiện _MENU_ dòng",
                info: "Trang _PAGE_ / _PAGES_",
                paginate: { first: "<<", last: ">>", next: ">", previous: "<" },
                zeroRecords: "Không tìm thấy kết quả",
                infoEmpty: "Không có dữ liệu"
            },
            pageLength: 25,
            ordering: true,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });

        // XỬ LÝ XÓA TỪNG SV
        $(document).on('click', '.delete-real', function() {
            const btn = $(this);
            const mssv = btn.data('mssv');
            const row = btn.closest('tr');
            const ten = row.find('td:eq(2)').text(); 

            if (!confirm(`XÓA sinh viên:\n${mssv} - ${ten}\n\nChắc chắn không?`)) return;

            const fd = new FormData();
            fd.append('mssv', mssv);

            fetch('xoa_sinhvien.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        table.row(row).remove().draw();
                        alert('Đã xóa thành công!');
                    } else {
                        alert('Lỗi: ' + (res.message || 'Không xác định'));
                    }
                })
                .catch(err => alert('Lỗi kết nối server'));
        });
    });

    // ===== MỚI THÊM: HÀM XÓA TOÀN BỘ DANH SÁCH =====
    function deleteAllStudents() {
        Swal.fire({
            title: 'CẢNH BÁO NGUY HIỂM!',
            text: "Bạn có chắc chắn muốn xóa TOÀN BỘ sinh viên không? Hành động này sẽ xóa sạch dữ liệu và KHÔNG THỂ khôi phục!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Vâng, xóa sạch!',
            cancelButtonText: 'Hủy bỏ'
        }).then((result) => {
            if (result.isConfirmed) {
                // Hiển thị loading
                Swal.fire({
                    title: 'Đang xử lý...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('xoa_ds_sv.php', { method: 'POST' })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            Swal.fire({
                                title: 'Đã xóa!',
                                text: 'Danh sách sinh viên đã được làm trống.',
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Lỗi!', res.message || 'Có lỗi xảy ra', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Lỗi mạng!', 'Không thể kết nối đến server.', 'error');
                    });
            }
        });
    }
</script>

<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2"></i>LƯU Ý KHI IMPORT DỮ LIỆU</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="alert alert-warning border-start border-5 border-warning">
                    <strong>Quan trọng:</strong> Vui lòng đọc kỹ các quy định dưới đây để tránh lỗi dữ liệu.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">1. Cấu trúc file Excel:</h6>
                        <ul class="small text-muted">
                            <li><strong>Dòng 1:</strong> Tiêu đề cột (Hệ thống sẽ bỏ qua dòng này).</li>
                            <li><strong>Dòng 2 trở đi:</strong> Dữ liệu sinh viên.</li>
                            <li>Định dạng file chấp nhận: <code>.xls</code>, <code>.xlsx</code></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">2. Quy định các cột:</h6>
                        <ul class="small text-muted">
                            <li>Cột B: <strong>Mã SV</strong> (Bắt buộc, không trùng).</li>
                            <li>Cột C: <strong>Họ lót</strong>.</li>
                            <li>Cột D: <strong>Tên</strong>.</li>
                            <li>Cột E: <strong>Lớp</strong>.</li>
                            <li>Cột J: <strong>GVHD</strong> (Nếu có).</li>
                        </ul>
                    </div>
                    
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="./file_mau/Danh_Sach_SV.xlsx" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download me-1"></i> Tải file mẫu chuẩn
                        </a>
                    </div>
                    <div>
                        <label class="btn btn-success btn-lg shadow">
                            <i class="fas fa-upload me-2"></i> CHỌN FILE ĐỂ IMPORT
                            <input type="file" id="real_import_input" accept=".xls,.xlsx" hidden onchange="handleImport(this)">
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
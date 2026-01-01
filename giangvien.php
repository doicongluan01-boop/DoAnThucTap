<?php
session_start();
require_once 'config.php';

// CHẶN GIẢNG VIÊN VÀ NGƯỜI CHƯA ĐĂNG NHẬP – CHỈ ADMIN MỚI VÀO ĐƯỢC
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Giảng viên Hướng dẫn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container-fluid { padding: 20px; }
        .page-header {
            background: linear-gradient(90deg, #4e73df, #224abe);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .page-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        .table thead {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
        }
        .table th {
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.8px;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            transition: 0.2s;
        }
        
        /* Nút Import */
        .btn-import {
            background: linear-gradient(45deg, #11998e, #38ef7d);
            border: none;
            border-radius: 50px;
            padding: 12px 28px;
            font-weight: 700;
            box-shadow: 0 6px 20px rgba(17,153,142,0.35);
            transition: all 0.3s;
        }
        .btn-import:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(17,153,142,0.5);
        }

        /* Nút Xóa tất cả (Mới) */
        .btn-delete-all {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            border: none;
            border-radius: 50px;
            padding: 12px 28px;
            font-weight: 700;
            color: white;
            box-shadow: 0 6px 20px rgba(255, 65, 108, 0.35);
            transition: all 0.3s;
            margin-left: 10px;
        }
        .btn-delete-all:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(255, 65, 108, 0.5);
            background: linear-gradient(45deg, #ff4b2b, #ff416c);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #ff416c, #ff4757);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 8px 16px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255,65,108,0.4);
            transition: all 0.3s;
        }
        .badge-sv {
            font-size: 1rem;
            padding: 10px 18px;
            border-radius: 50px;
            font-weight: bold;
        }
        .modal-content { border-radius: 20px; }
        .toast-container {
            position: fixed; top: 20px; right: 20px; z-index: 9999;
        }
        .toast {
            min-width: 320px; border-radius: 15px; overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3); animation: slideIn 0.5s ease;
        }
        .toast-success { background: linear-gradient(135deg, #11998e, #38ef7d); color: white; }
        .toast-error { background: linear-gradient(135deg, #ff6b6b, #ee5a52); color: white; }
        .toast .toast-body { padding: 18px; font-size: 1.1rem; font-weight: 600; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="page-header">
        <h3><i class="fas fa-chalkboard-teacher"></i> Quản lý Giảng viên Hướng dẫn</h3>
    </div>

    <div class="row mb-4">
        <div class="col-md-12 d-flex align-items-center">
            <button class="btn btn-import text-white shadow-lg" data-bs-toggle="modal" data-bs-target="#importGVModal">
                <i class="fas fa-file-excel"></i> Import GVHD từ Excel
            </button>

            <button class="btn btn-delete-all shadow-lg" onclick="deleteAllLecturers()">
                <i class="fas fa-trash"></i> Xóa tất cả danh sách
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Mã GV</th>
                    <th>Họ tên</th>
                    <th>Khoa</th>
                    <th>Chức vụ</th>
                    <th>Liên hệ</th>
                    <th>Số SV đang HD</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // LẤY SỐ SINH VIÊN ĐANG HƯỚNG DẪN CHUẨN
                $stmt = $pdo->query("
                    SELECT 
                        g.MaGV, g.HoTen, g.Khoa, g.ChucVu, g.Email, g.DienThoai,
                        COALESCE(COUNT(ptn.sinh_vien_id), 0) AS so_sv_hd
                    FROM giang_vien g
                    LEFT JOIN nhom n ON g.MaGV = n.giang_vien_huong_dan_id
                    LEFT JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
                    GROUP BY g.MaGV
                    ORDER BY g.HoTen
                ");

                while ($gv = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $contact = $gv['Email'] ?: ($gv['DienThoai'] ?: 'Chưa có');
                    $soSV = (int)$gv['so_sv_hd'];
                    $badgeClass = $soSV > 0 ? 'bg-success' : 'bg-secondary';
                    $badgeText = $soSV > 0 ? "$soSV SV" : "Chưa có SV";
                ?>
                <tr>
                    <td><strong class="text-primary"><?= htmlspecialchars($gv['MaGV']) ?></strong></td>
                    <td class="fw-bold"><?= htmlspecialchars($gv['HoTen']) ?></td>
                    <td><span class="badge bg-info"><?= htmlspecialchars($gv['Khoa'] ?? 'Chưa có') ?></span></td>
                    <td><?= htmlspecialchars($gv['ChucVu'] ?? '') ?></td>
                    <td><small class="text-muted"><?= htmlspecialchars($contact) ?></small></td>
                    <td>
                        <span class="badge <?= $badgeClass ?> badge-sv">
                            <?= $badgeText ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-delete shadow delete-gv" 
                                data-magv="<?= $gv['MaGV'] ?>" 
                                data-ten="<?= htmlspecialchars($gv['HoTen']) ?>">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="importGVModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-file-excel"></i> Import GVHD từ Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formImportGV" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn file Excel</label>
                        <input type="file" name="file_excel_gv" accept=".xlsx,.xls" required class="form-control form-control-lg">
                        <div class="form-text mt-2">
                            File Excel cần có các cột: <strong>Họ tên, Mã GV, Khoa, Chức vụ, Email, SĐT</strong>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="fas fa-upload"></i> Import ngay
                        </button>
                    </div>
                </form>
                <div id="ketqua" class="mt-4"></div>
            </div>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toast đẹp
function showToast(type, message) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="toast-body">
            <i class="fas ${type==='success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-3"></i>
            ${message}
        </div>
    `;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// Import Excel
document.getElementById('formImportGV')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const oldText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    fetch('import_gvhd_real.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            document.getElementById('ketqua').innerHTML = 
                `<div class="alert alert-${res.success?'success':'danger'} mt-3">${res.message}</div>`;
            showToast(res.success ? 'toast-success' : 'toast-error', res.message);
            if (res.success) setTimeout(() => location.reload(), 2000);
        })
        .catch(() => showToast('toast-error', 'Lỗi kết nối server!'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = oldText;
        });
});

// XÓA 1 GIẢNG VIÊN (Logic cũ)
document.querySelectorAll('.delete-gv').forEach(btn => {
    btn.addEventListener('click', function () {
        const maGV = this.dataset.magv;
        const tenGV = this.dataset.ten;
        if (!confirm(`Xóa giảng viên ${maGV} - ${tenGV}?`)) return;

        this.disabled = true;
        this.innerHTML = 'Đang xóa...';
        fetch('xoa_giang_vien.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'magv=' + encodeURIComponent(maGV)
        }).then(r => r.json()).then(res => {
            if (res.success) {
                this.closest('tr').remove();
                showToast('toast-success', 'Đã xóa thành công');
            } else {
                alert(res.message || 'Xóa thất bại');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-trash-alt"></i> Xóa';
            }
        });
    });
});

// ===== CHỨC NĂNG XÓA TẤT CẢ (MỚI) =====
function deleteAllLecturers() {
    Swal.fire({
        title: 'CẢNH BÁO NGUY HIỂM!',
        text: "Bạn có chắc chắn muốn xóa TOÀN BỘ danh sách giảng viên? Hành động này sẽ không thể khôi phục!",
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

            fetch('xoa_tat_ca_giang_vien.php', { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            title: 'Đã xóa!',
                            text: 'Danh sách giảng viên đã được làm trống.',
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

</body>
</html>
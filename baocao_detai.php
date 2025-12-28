<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Báo cáo đề tài LVTN đã giao</title>
    <?php
    require 'config.php';
    session_start();

    if (!isset($_SESSION['role'])) {
        header("Location: login.php");
        exit;
    }

    $role = $_SESSION['role'];
    $MaGV = $_SESSION['MaGV'] ?? null;
    $HoTenGV = $_SESSION['HoTen'] ?? 'Giảng viên';

    // Điều kiện lọc theo giảng viên
    $where = "";
    $params = [];
    if ($role === 'giangvien') {
        $where = "AND n.giang_vien_huong_dan_id = ?";
        $params[] = $MaGV;
    }

    // CÂU SQL CHUẨN CHO MYSQL
    $sql = "
        SELECT 
            n.id AS nhom_id,
            COALESCE(n.ten_nhom, CONCAT('Nhóm ', n.id)) AS ten_nhom,
            sv.MaSV,
            sv.HoTen AS hoten,
            sv.Lop,
            COALESCE(sv.huong_de_tai, '(Chưa có đề tài)') AS de_tai,
            gv.HoTen AS ten_gvhd
        FROM nhom n
        LEFT JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
        LEFT JOIN danh_sach_sinh_vien sv ON ptn.sinh_vien_id = sv.id
        LEFT JOIN giang_vien gv ON n.giang_vien_huong_dan_id = gv.MaGV
        WHERE sv.huong_de_tai IS NOT NULL 
          AND sv.huong_de_tai != ''
          $where
        ORDER BY n.ten_nhom, sv.HoTen
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Đếm số nhóm có đề tài
    $count_sql = "
        SELECT COUNT(DISTINCT n.id)
        FROM nhom n
        LEFT JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
        LEFT JOIN danh_sach_sinh_vien sv ON ptn.sinh_vien_id = sv.id
        WHERE sv.huong_de_tai IS NOT NULL 
          AND sv.huong_de_tai != ''
          $where
    ";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $totalGroups = $count_stmt->fetchColumn();
    ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {--success:#27ae60;--light:#d5f5e3}
        body{background:linear-gradient(135deg,#e8f5e9,#c8e6c9);min-height:100vh;font-family:'Segoe UI',sans-serif}
        .card{border:none;border-radius:20px;overflow:hidden;box-shadow:0 20px 40px rgba(39,174,96,.25)}
        .card-header{background:linear-gradient(135deg,#27ae60,#219653);color:white;padding:40px;text-align:center}
        .card-header h1{font-size:2.6rem;font-weight:700;text-shadow:0 3px 12px rgba(0,0,0,.4)}
        .icon{font-size:5rem;opacity:.9}
        .table thead{background:#27ae60;color:white}
        .table tbody tr:hover{background:var(--light)!important;transform:scale(1.01);transition:.3s}
        .badge-group{background:var(--success);color:white;padding:10px 20px;border-radius:50px;font-weight:bold;font-size:1.1rem}
        .summary-box{background:white;padding:30px;border-radius:20px;box-shadow:0 12px 30px rgba(0,0,0,.1);text-align:center}
        .summary-box h3{color:var(--success);font-size:3rem;margin:10px 0}
        .summary-box p{font-weight:bold;color:#2c3e50;font-size:1.1rem}
    </style>
</head>
<body>
<div class="container my-5">
    <div class="card">
        <div class="card-header">
            <div class="icon"><i class="bi bi-journal-check"></i></div>
            <h1>BÁO CÁO ĐỀ TÀI LUẬN VĂN TỐT NGHIỆP ĐÃ GIAO</h1>
            <p class="fs-4 mt-3">
                <?php if ($role === 'giangvien'): ?>
                    Giảng viên: <strong class="text-warning"><?=htmlspecialchars($HoTenGV)?></strong>
                <?php else: ?>
                    <span class="text-info">Quản trị viên – Xem toàn bộ hệ thống</span>
                <?php endif; ?>
            </p>
        </div>

        <div class="card-body p-5">
            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="summary-box">
                        <h3><?= count($results) ?></h3>
                        <p>Sinh viên đã được giao đề tài</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="summary-box">
                        <h3><?= $totalGroups ?></h3>
                        <p>Nhóm đồ án có đề tài</p>
                    </div>
                </div>
            </div>

            <?php if (empty($results)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-emoji-neutral display-1 text-muted"></i>
                    <h3 class="mt-4 text-muted">Chưa có sinh viên nào được giao đề tài</h3>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-bordered" id="tbl">
                        <thead>
                            <tr>
                                <th>Nhóm</th>
                                <th>MSSV</th>
                                <th>Họ và tên</th>
                                <th>Lớp</th>
                                <th>Đề tài được giao</th>
                                <th>GV Hướng dẫn</th>
                            </tr>
                        </thead>
<tbody>
<?php 
$current_group = '';
foreach ($results as $r):
    $group_name = $r['ten_nhom'] ?? ("Nhóm " . $r['nhom_id']);

    // Nếu đổi nhóm -> tạo 1 hàng group nhưng phải đủ 6 cột
    if ($current_group !== $group_name):
        $current_group = $group_name;
?>
<tr class="table-success fw-bold">
    <td><?= htmlspecialchars($group_name) ?></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
<?php endif; ?>

<tr>
    <td><?= htmlspecialchars($group_name) ?></td>
    <td><strong><?= htmlspecialchars($r['MaSV']) ?></strong></td>
    <td><?= htmlspecialchars($r['hoten']) ?></td>
    <td><?= htmlspecialchars($r['Lop']) ?></td>
    <td class="fw-bold text-primary"><?= htmlspecialchars($r['de_tai']) ?></td>
    <td><em class="text-success fw-bold"><?= htmlspecialchars($r['ten_gvhd']) ?></em></td>
</tr>

<?php endforeach; ?>
</tbody>


                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#tbl').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rtip',
        buttons: [
            {extend:'excel', text:'Xuất Excel', className:'btn btn-success btn-sm', title:'Báo cáo đề tài LVTN'},
            {extend:'pdf',   text:'Xuất PDF',   className:'btn btn-danger btn-sm', title:'Báo cáo đề tài LVTN'},
            {extend:'print', text:'In trang',   className:'btn btn-info btn-sm text-white', title:'Báo cáo đề tài LVTN'}
        ],
        language: {url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'},
        pageLength: 50,
        order: [[0, 'asc']],
        responsive: true
    });
});


</script>
</body>
</html>
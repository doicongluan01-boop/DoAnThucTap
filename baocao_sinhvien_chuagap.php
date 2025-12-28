<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Bạn có thể mở lại dòng này khi chạy thực tế
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     die('Không có quyền truy cập');
// }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Báo cáo SV chưa gặp GVHD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff0f0; }
        .header-box {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white; padding: 20px; border-radius: 15px;
            box-shadow: 0 10px 20px rgba(231, 76, 60, 0.2);
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .header-box { background: none; color: black; border: 1px solid #000; box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between mb-3 no-print">
        
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> In báo cáo</button>
    </div>

    <div class="header-box text-center mb-4">
        <h2 class="fw-bold m-0">DANH SÁCH SINH VIÊN CHƯA GẶP GVHD</h2>
        <small>Cập nhật: <?= date('d/m/Y H:i') ?></small>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php
            // Query lấy danh sách SV thuộc nhóm có da_gap_gv = 0
            $sql = "
                SELECT
                    s.MaSV, s.HoTen, s.Lop,
                    n.ten_nhom,
                    gv.HoTen AS gvhd
                FROM nhom n
                INNER JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
                INNER JOIN danh_sach_sinh_vien s ON s.id = ptn.sinh_vien_id
                LEFT JOIN giang_vien gv ON gv.MaGV = n.giang_vien_huong_dan_id
                WHERE n.da_gap_gv = 0
                ORDER BY n.ten_nhom ASC, s.HoTen ASC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (count($results) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 align-middle">
                        <thead class="table-danger text-center">
                            <tr>
                                <th>STT</th>
                                <th>MSSV</th>
                                <th>Họ tên</th>
                                <th>Lớp</th>
                                <th>Nhóm</th>
                                <th>GVHD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt=1; foreach($results as $row): ?>
                            <tr>
                                <td class="text-center"><?= $stt++ ?></td>
                                <td class="fw-bold text-center"><?= htmlspecialchars($row['MaSV']) ?></td>
                                <td><?= htmlspecialchars($row['HoTen']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['Lop']) ?></td>
                                <td class="text-center"><span class="badge bg-secondary"><?= htmlspecialchars($row['ten_nhom']) ?></span></td>
                                <td class="text-danger fw-bold">
                                    <?= !empty($row['gvhd']) ? htmlspecialchars($row['gvhd']) : 'Chưa phân công / Lỗi mã' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 text-center fw-bold text-danger">
                    Tổng cộng: <?= count($results) ?> sinh viên
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-check-circle text-success fs-1"></i>
                    <h4 class="mt-2">Tuyệt vời! Tất cả sinh viên đã gặp GVHD.</h4>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
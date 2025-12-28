<?php
require 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Thiếu ID Hội đồng");

// 1. Lấy thông tin Hội đồng
$stmt = $pdo->prepare("SELECT * FROM hoidong WHERE id = ?");
$stmt->execute([$id]);
$hd = $stmt->fetch();

// =================================================================================
// SQL THÔNG MINH: LẤY THEO NHÓM (Fix lỗi thiếu sinh viên)
// Logic: 
// Bước 1: Tìm xem có những Nhóm (nhom_id) nào đang nằm trong hội đồng này.
// Bước 2: Lấy TẤT CẢ sinh viên thuộc các nhóm đó.
// =================================================================================
$sql = "SELECT sv.MaSV, sv.HoTen, sv.Lop, 
               n.ten_nhom, n.huong_de_tai,
               -- Ưu tiên lấy đề tài từ Nhóm, nếu không có thì lấy của Sinh viên
                COALESCE(n.huong_de_tai, sv.huong_de_tai) as de_tai_chinh_thuc,
               -- Lấy tên GVHD chuẩn nhất
               COALESCE(gv.HoTen, n.giang_vien_huong_dan_id, sv.gvhd) AS gvhd_final
        FROM danh_sach_sinh_vien sv
        JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id
        JOIN nhom n ON ptn.nhom_id = n.id
        LEFT JOIN giang_vien gv ON n.giang_vien_huong_dan_id = gv.MaGV
        WHERE n.id IN (
            -- Sub-query: Tìm tất cả các nhóm có dính dáng tới hội đồng này
            SELECT DISTINCT ptn2.nhom_id
            FROM danh_sach_sinh_vien sv2
            JOIN phan_thuoc_nhom ptn2 ON sv2.id = ptn2.sinh_vien_id
            WHERE sv2.hoidong_id = ?
        )
        ORDER BY n.id ASC, sv.MaSV ASC"; 
        // Sắp xếp theo ID nhóm trước (để các bạn cùng nhóm đứng gần nhau), sau đó tới MSSV

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hàm xử lý tên
function tachHoTen($full_name) {
    $parts = explode(' ', trim($full_name ?? ''));
    $ten = array_pop($parts);
    $ho_lot = implode(' ', $parts);
    return ['ho' => $ho_lot, 'ten' => $ten];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh Sách Hội Đồng</title>
    <style>
        /* GIỮ NGUYÊN FORM CHUẨN CỦA BẠN */
        body { font-family: "Times New Roman", Times, serif; font-size: 13pt; margin: 0; padding: 20px; }
        @page { size: A4 landscape; margin: 1cm; }
        @media print { .no-print { display: none !important; } body { padding: 0; } }

        .header-table { width: 100%; border: none; margin-bottom: 20px; }
        .header-left { text-align: left; font-weight: bold; text-transform: uppercase; }
        .header-right { text-align: right; font-weight: bold; }
        
        .main-title { text-align: center; font-weight: bold; font-size: 16pt; margin-top: 5px; text-transform: uppercase; }
        .sub-title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 15px; text-transform: uppercase; }
        
        .info-line { margin-bottom: 5px; font-weight: bold; margin-left: 20px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid black; padding: 5px; vertical-align: middle; }
        table.data-table th { background: #f2f2f2; text-align: center; font-weight: bold; }
        
        .text-center { text-align: center; }
        .col-stt { width: 40px; }
        .col-mssv { width: 100px; }
        .col-lop { width: 80px; }
        .col-gv { width: 160px; }
        .col-note { width: 60px; }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <span style="color: red; font-weight: bold;">Tổng số SV tìm thấy: <?= count($students) ?></span>
        <button onclick="window.print()">🖨️ In Ngay</button>
    </div>

    <table class="header-table">
        <tr>
            <td class="header-left">TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN</td>
            <td class="header-right">Phụ lục 3</td>
        </tr>
    </table>

    <div class="main-title">DANH SÁCH SINH VIÊN BẢO VỆ ĐỒ ÁN/KHÓA LUẬN</div>
    <div class="sub-title">NGÀNH: CÔNG NGHỆ THÔNG TIN</div>

    <div class="info-line">- Hội đồng: <?= htmlspecialchars($hd['ten_hoidong']) ?></div>
    <div class="info-line">- Thời gian: <?= date('H:i', strtotime($hd['thoigian'])) ?> - Ngày <?= date('d/m/Y', strtotime($hd['thoigian'])) ?></div>
    <div class="info-line">- Địa điểm: Phòng <?= htmlspecialchars($hd['phong']) ?></div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="col-stt">STT</th>
                <th class="col-mssv">MSSV</th>
                <th>Họ Lót</th>
                <th style="width: 70px;">Tên</th>
                <th class="col-lop">Lớp</th>
                <th class="col-gv">GVHD</th>
                <th>Tên Đề Tài</th>
                <th class="col-note">Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $stt = 1;
            $current_group = null;
            
            foreach ($students as $sv): 
                $name = tachHoTen($sv['HoTen']);
                
                // Logic xử lý gom nhóm đề tài (để đẹp mắt - tùy chọn)
                // Nếu cùng nhóm thì không cần in lại đề tài (nhưng ở đây in hết cho chắc)
            ?>
            <tr>
                <td class="text-center"><?= $stt++ ?></td>
                <td class="text-center"><?= htmlspecialchars($sv['MaSV']) ?></td>
                <td><?= htmlspecialchars($name['ho']) ?></td>
                <td class="text-center" style="font-weight: bold;"><?= htmlspecialchars($name['ten']) ?></td>
                <td class="text-center"><?= htmlspecialchars($sv['Lop']) ?></td>
                <td><?= htmlspecialchars($sv['gvhd_final']) ?></td>
                <td><?= htmlspecialchars($sv['de_tai_chinh_thuc'] ?? '') ?></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
            
            <?php if(empty($students)): ?>
            <tr><td colspan="8" class="text-center">Chưa có dữ liệu sinh viên</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right; padding-right: 50px;">
        <p><i>Tp. Hồ Chí Minh, ngày ...... tháng ...... năm 20......</i></p>
        <p><b>THƯ KÝ HỘI ĐỒNG</b></p>
    </div>

</body>
</html>
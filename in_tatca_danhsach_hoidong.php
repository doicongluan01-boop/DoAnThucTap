<?php
require 'config.php';

// =================================================================================
// Logic: Lấy sinh viên nếu:
// 1. Bản thân sinh viên đó có mã hội đồng.
// 2. HOẶC sinh viên đó nằm trong nhóm mà có bạn khác đã có mã hội đồng.
// =================================================================================
$sql = "SELECT DISTINCT 
            sv.MaSV, sv.HoTen, sv.Lop, 
            hd.ten_hoidong, hd.phong, hd.thoigian,
            
            -- Lấy GVHD & Đề tài (Ưu tiên bảng Nhóm -> Sinh viên)
            COALESCE(gv.HoTen, n.giang_vien_huong_dan_id, sv.gvhd) AS gvhd_final,
            COALESCE(n.huong_de_tai, sv.huong_de_tai) AS de_tai_final

        FROM danh_sach_sinh_vien sv 
        
        -- Kết nối bảng Nhóm
        LEFT JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id
        LEFT JOIN nhom n ON ptn.nhom_id = n.id
        LEFT JOIN giang_vien gv ON n.giang_vien_huong_dan_id = gv.MaGV

        -- [QUAN TRỌNG NHẤT] KẾT NỐI HỘI ĐỒNG BẰNG ĐIỀU KIỆN 'HOẶC'
        JOIN hoidong hd ON (
            -- Trường hợp 1: Sinh viên có sẵn ID hội đồng
            sv.hoidong_id = hd.id
            OR
            -- Trường hợp 2: 'Ăn theo' nhóm (Tìm xem nhóm này có ai khác có hội đồng không)
            (
                n.id IS NOT NULL AND n.id IN (
                    SELECT sub_ptn.nhom_id
                    FROM phan_thuoc_nhom sub_ptn
                    JOIN danh_sach_sinh_vien sub_sv ON sub_ptn.sinh_vien_id = sub_sv.id
                    WHERE sub_sv.hoidong_id = hd.id
                )
            )
        )
        
        -- Sắp xếp: Ngày -> Tên HĐ -> Nhóm (để các bạn cùng nhóm đứng cạnh nhau)
        ORDER BY hd.thoigian ASC, hd.ten_hoidong ASC, n.id ASC, sv.MaSV ASC";

$stmt = $pdo->query($sql);
$all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- CÁC PHẦN XỬ LÝ HTML GIỮ NGUYÊN ---
$grouped_data = [];
foreach ($all_data as $row) {
    $time = $row['thoigian'] ? strtotime($row['thoigian']) : 0;
    $ngay_key = $time ? date('Y-m-d', $time) : 'unknown';
    $grouped_data[$ngay_key][] = $row;
}

function tachHoTen($full_name) {
    $parts = explode(' ', trim($full_name ?? ''));
    $ten = array_pop($parts);
    $ho_lot = implode(' ', $parts);
    return ['ho' => $ho_lot, 'ten' => $ten];
}

function getNgayTiengViet($date_key) {
    if ($date_key == 'unknown') return "Chưa xếp lịch";
    $days = ['Monday'=>'Thứ Hai','Tuesday'=>'Thứ Ba','Wednesday'=>'Thứ Tư','Thursday'=>'Thứ Năm','Friday'=>'Thứ Sáu','Saturday'=>'Thứ Bảy','Sunday'=>'Chủ Nhật'];
    $ts = strtotime($date_key);
    return $days[date('l', $ts)] . " Ngày " . date('d/m/Y', $ts);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh Sách Tổng Hợp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 13pt; margin: 0; padding: 20px; }
        @page { size: A4 landscape; margin: 1cm; }
        @media print { 
            .no-print { display: none !important; } 
            .page-break { page-break-before: always; }
        }
        
        .header-table { width: 100%; border: none; margin-bottom: 10px; }
        .header-left { text-align: left; font-weight: bold; text-transform: uppercase; }
        .header-right { text-align: right; font-weight: bold; }
        
        .main-title { text-align: center; font-weight: bold; color: #C00000; font-size: 16pt; margin-top: 10px; text-transform: uppercase; }
        .sub-title { text-align: center; font-weight: bold; color: #002060; font-size: 14pt; margin-bottom: 5px; text-transform: uppercase; }
        .major-title { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 15px; text-transform: uppercase; }
        
        .info-block { margin-left: 20px; margin-bottom: 10px; }
        .info-row { font-weight: bold; margin-bottom: 5px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.data-table th, table.data-table td { border: 1px solid black; padding: 5px; vertical-align: middle; }
        table.data-table th { text-align: center; font-weight: bold; background-color: #f2f2f2; }
        
        .text-center { text-align: center; }
        .col-stt { width: 40px; }
        .col-hd { width: 100px; }
        .col-mssv { width: 90px; }
        .col-lop { width: 70px; }
        .col-phong { width: 80px; color: #0070C0; font-weight: bold; }
        
        .hd-name { font-weight: bold; display: block; }
        .hd-time { font-size: 0.9em; color: #555; font-style: italic; }
    </style>
</head>
<body>

    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
        <div style="background: #fff; padding: 5px 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 5px; font-weight: bold; color: red;">
            Tổng SV tìm thấy: <?= count($all_data) ?>
        </div>
        <button onclick="window.print()" style="padding: 10px 20px; background: #0d6efd; color: white; border: none; border-radius: 5px; cursor: pointer;">
            <i class="bi bi-printer-fill"></i> IN NGAY
        </button>
    </div>

    <?php 
    $is_first_page = true;
    foreach ($grouped_data as $ngay => $students_in_day): 
        $unique_hds = array_unique(array_column($students_in_day, 'ten_hoidong'));
        $count_hd = count($unique_hds);
        $page_class = $is_first_page ? '' : 'page-break';
        $is_first_page = false;
    ?>

    <div class="<?= $page_class ?>">
        <table class="header-table">
            <tr><td class="header-left">TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN</td><td class="header-right">Phụ lục 3</td></tr>
        </table>

        <div class="main-title">DANH SÁCH THỨ TỰ SINH VIÊN BẢO VỆ TẠI HỘI ĐỒNG</div>
        <div class="sub-title">ĐẠI HỌC 2021_HỆ CHÍNH QUY TẬP TRUNG</div>
        <div class="major-title">NGÀNH: CÔNG NGHỆ THÔNG TIN</div>

        <div class="info-block">
            <div class="info-row">- Ngày bảo vệ: <?= getNgayTiengViet($ngay) ?></div>
            <div class="info-row">- Tổng số hội đồng: <?= sprintf("%02d", $count_hd) ?> hội đồng</div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-stt">STT</th>
                    <th class="col-hd">Hội Đồng</th>
                    <th class="col-mssv">MSSV</th>
                    <th>Họ Lót</th>
                    <th style="width: 70px;">Tên</th>
                    <th class="col-lop">Lớp</th>
                    <th style="width: 150px;">GVHD</th>
                    <th>Tên Đề Tài</th>
                    <th class="col-phong">Phòng</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                foreach ($students_in_day as $row): 
                    $name = tachHoTen($row['HoTen']);
                    $gio_bao_ve = $row['thoigian'] ? date('H:i', strtotime($row['thoigian'])) : '';
                    $ten_hd_ngan = str_ireplace('Hội đồng', '', $row['ten_hoidong']);
                ?>
                <tr>
                    <td class="text-center"><?= $stt++ ?></td>
                    <td class="text-center">
                        <span class="hd-name"><?= htmlspecialchars($ten_hd_ngan) ?></span>
                        <span class="hd-time">(<?= $gio_bao_ve ?>)</span>
                    </td>
                    <td class="text-center"><?= htmlspecialchars($row['MaSV']) ?></td>
                    <td style="padding-left: 5px;"><?= htmlspecialchars($name['ho']) ?></td>
                    <td class="text-center" style="font-weight: bold;"><?= htmlspecialchars($name['ten']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['Lop']) ?></td>
                    <td><?= htmlspecialchars($row['gvhd_final'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['de_tai_final'] ?? '') ?></td>
                    <td class="text-center col-phong"><?= htmlspecialchars($row['phong']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px; text-align: right; padding-right: 50px;">
            <p><i>Tp. Hồ Chí Minh, ngày ...... tháng ...... năm 20......</i></p>
            <p style="margin-right: 40px;"><b>THƯ KÝ KHOA</b></p>
        </div>
    </div>
    <?php endforeach; ?>

</body>
</html>
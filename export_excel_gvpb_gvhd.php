<?php
session_start();
require 'config.php';

// Cấu hình header để trình duyệt hiểu đây là file Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=DanhSach_SV_GVHD_GVPB_STU.xls");
header("Pragma: no-cache");
header("Expires: 0");

// --- SQL LẤY DỮ LIỆU ĐẦY ĐỦ ---
// Lấy thông tin Sinh viên, Nhóm, GVHD, GVPB
$sql = "SELECT 
            sv.MaSV, sv.HoTen, sv.Lop, 
            COALESCE(n.ten_nhom, n.id) as ten_nhom,
            COALESCE(n.huong_de_tai, sv.huong_de_tai) as ten_de_tai,
            
            gvhd.HoTen as gvhd_ten, 
            
            -- [ĐÃ SỬA] Thay thế cột thiếu bằng chuỗi rỗng để không bị lỗi
            '' as gvhd_hocvi,
            '' as gvhd_donvi,
            
            gvpb.HoTen as gvpb_ten,
            sv.diem_50 as tien_do
            
        FROM danh_sach_sinh_vien sv
        LEFT JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id
        LEFT JOIN nhom n ON ptn.nhom_id = n.id
        LEFT JOIN giang_vien gvhd ON n.giang_vien_huong_dan_id = gvhd.MaGV
        LEFT JOIN giang_vien gvpb ON sv.gvpb_id = gvpb.MaGV
        ORDER BY sv.Lop ASC, sv.MaSV ASC";

$stmt = $pdo->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hàm tách Họ và Tên
function splitName($fullName) {
    $parts = explode(' ', trim($fullName ?? ''));
    $ten = array_pop($parts);
    $ho = implode(' ', $parts);
    return ['ho' => $ho, 'ten' => $ten];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <style>
        /* CSS để định dạng bảng trong Excel */
        table { border-collapse: collapse; width: 100%; font-family: 'Times New Roman', serif; font-size: 11pt; }
        th, td { border: 1px solid black; padding: 5px; vertical-align: middle; }
        .header-text { font-weight: bold; text-align: left; border: none; }
        .title-text { font-weight: bold; text-align: center; font-size: 14pt; border: none; color: #C00000; }
        .yellow-bg { background-color: #FFFF00; font-weight: bold; text-align: center; }
        .center { text-align: center; }
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="4" class="header-text" style="border:none;">TRƯỜNG ĐH CÔNG NGHỆ SÀI GÒN</td>
        <td colspan="8" style="border:none;"></td>
    </tr>
    <tr>
        <td colspan="4" class="header-text" style="border:none; text-decoration: underline;">KHOA CÔNG NGHỆ THÔNG TIN</td>
        <td colspan="8" style="border:none;"></td>
    </tr>
    <tr><td colspan="12" style="border:none;">&nbsp;</td></tr>

    <tr>
        <td colspan="12" class="title-text">DANH SÁCH SINH VIÊN _ GIÁO VIÊN HƯỚNG DẪN _ GIÁO VIÊN PHẢN BIỆN</td>
    </tr>
    <tr>
        <td colspan="12" class="title-text" style="color: #002060; font-size: 12pt;">ĐẠI HỌC 2021 VÀ KHÓA CŨ LÀM LẠI</td>
    </tr>
    <tr>
        <td colspan="12" class="title-text" style="color: black; font-size: 12pt;">NGÀNH : CÔNG NGHỆ THÔNG TIN</td>
    </tr>
    <tr><td colspan="12" style="border:none;">&nbsp;</td></tr>

    <tr>
        <th class="yellow-bg" style="width: 40px;">STT</th>
        <th class="yellow-bg" style="width: 100px;">MSSV</th>
        <th class="yellow-bg" colspan="2" style="width: 200px;">HỌ TÊN SINH VIÊN</th> <th class="yellow-bg" style="width: 80px;">LỚP</th>
        <th class="yellow-bg" style="width: 60px;">Nhóm</th>
        <th class="yellow-bg" style="width: 200px;">GV HƯỚNG DẪN</th>
        <th class="yellow-bg" style="width: 80px;">HH-HV</th>
        <th class="yellow-bg" style="width: 150px;">Nơi công tác</th>
        <th class="yellow-bg" style="width: 300px;">Tên đề tài<br>(GVHD nhập thông tin)</th>
        <th class="yellow-bg" style="width: 200px;">GV PHẢN BIỆN</th>
        <th class="yellow-bg" style="width: 80px;">Khối lượng<br>hoàn thành<br>giữa kỳ (%)</th>
        <th class="yellow-bg" style="width: 100px;">Cảnh cáo / Ghi chú</th>
    </tr>

    <?php 
    $stt = 1;
    foreach ($data as $row): 
        $name = splitName($row['HoTen']);
        // Xử lý dữ liệu rỗng để tránh lỗi hiển thị
        $hocvi = $row['gvhd_hocvi'] ?? ''; // Nếu DB chưa có cột này thì để trống
        $donvi = $row['gvhd_donvi'] ?? ''; // Nếu DB chưa có cột này thì để trống
        
        // Mặc định nơi công tác nếu là GV trong trường
        if (empty($donvi) && !empty($row['gvhd_ten'])) {
             $donvi = "STU"; 
        }
    ?>
    <tr>
        <td class="center"><?= $stt++ ?></td>
        <td class="center" style="mso-number-format:'\@';"><?= $row['MaSV'] ?></td> <td style="border-right: none;"><?= $name['ho'] ?></td>
        <td style="border-left: none; font-weight: bold;"><?= $name['ten'] ?></td>
        <td class="center"><?= $row['Lop'] ?></td>
        <td class="center"><?= $row['ten_nhom'] ?></td>
        
        <td style="font-weight: bold;"><?= $row['gvhd_ten'] ?></td>
        <td class="center"><?= $hocvi ?></td>
        <td class="center"><?= $donvi ?></td>
        
        <td><?= $row['ten_de_tai'] ?></td>
        
        <td style="font-weight: bold; color: green;"><?= $row['gvpb_ten'] ?></td>
        
        <td class="center"><?= $row['tien_do'] ?></td> <td></td> </tr>
    <?php endforeach; ?>

</table>

<br>
<div style="text-align: right; font-style: italic; font-family: 'Times New Roman';">
    Tp. Hồ Chí Minh, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?>
</div>

</body>
</html>
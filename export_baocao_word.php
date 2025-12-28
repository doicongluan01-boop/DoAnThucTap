<?php
require 'config.php';

// Header để xuất file Word
header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=Bien_Ban_Bao_Ve_LVTN.doc");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head><meta charset="utf-8"><title>Biên bản bảo vệ</title></head>
<body style="font-family:Times New Roman;font-size:14pt;">
<div style="text-align:center;">
    <h1><u>BIÊN BẢN BẢO VỆ ĐỒ ÁN TỐT NGHIỆP</u></h1>
    <p><i>Khoa Công nghệ Thông tin - Trường Đại học Công Nghệ Sài Gòn</i></p>
</div>
<table border="1" cellspacing="0" cellpadding="5" width="100%" style="border-collapse:collapse;font-size:13pt;">
    <tr style="background:#f0f0f0;font-weight:bold;text-align:center;">
        <td>STT</td>
        <td>MSSV</td>
        <td>Họ tên</td>
        <td>Đề tài</td>
        <td>GVHD</td>
        <td>GV Phản biện</td>
        <td>Hội đồng</td>
        <td>Điểm HD</td>
        <td>Điểm PB</td>
        <td>Điểm TB</td>
        <td>Xếp loại</td>
    </tr>
    <?php
    $stt = 1;
    $stmt = $pdo->query("
        SELECT s.*, h.ten_hoidong, h.phong,
               gvhd.HoTen as gvhd_ten, gvpb.HoTen as gvpb_ten,
               d.diem_hd, d.diem_pb
        FROM danh_sach_sinh_vien s
        LEFT JOIN hoidong h ON s.hoidong_id = h.id
        LEFT JOIN giang_vien gvhd ON s.gvhd = gvhd.MaGV
        LEFT JOIN giang_vien gvpb ON s.gvpb_id = gvpb.MaGV
        LEFT JOIN qlsv_diem d ON s.id = d.sinhvien_id
        ORDER BY h.ten_hoidong, s.MaSV
    ");
    while($sv = $stmt->fetch()){
        $diem_tb = ($sv['diem_hd'] + $sv['diem_pb']) / 2;
        $xeploai = $diem_tb >= 9 ? 'Xuất sắc' : ($diem_tb >= 8 ? 'Giỏi' : ($diem_tb >= 6.5 ? 'Khá' : ($diem_tb >= 5 ? 'Trung bình' : 'Yếu')));
        echo "<tr>
                <td align='center'>$stt</td>
                <td>{$sv['MaSV']}</td>
                <td>{$sv['HoTen']}</td>
                <td>{$sv['DeTai']}</td>
                <td>{$sv['gvhd_ten']}</td>
                <td>{$sv['gvpb_ten']}</td>
                <td>{$sv['ten_hoidong']}</td>
                <td align='center'>".number_format($sv['diem_hd'],1)."</td>
                <td align='center'>".number_format($sv['diem_pb'],1)."</td>
                <td align='center'><strong>".number_format($diem_tb,2)."</strong></td>
                <td align='center'><strong>$xeploai</strong></td>
              </tr>";
        $stt++;
    }
    ?>
</table>
<br><br>
<table width="100%">
    <tr>
        <td width="50%" align="center">
            <i>Ngày ... tháng ... năm 2025</i><br><br><br><br>
            <strong>CHỦ TỊCH HỘI ĐỒNG</strong><br>
            (Ký, ghi rõ họ tên)
        </td>
        <td width="50%" align="center">
            <i>Ngày ... tháng ... năm 2025</i><br><br><br><br>
            <strong>TRƯỞNG KHOA</strong><br>
            (Ký, ghi rõ họ tên)
        </td>
    </tr>
</table>
</body>
</html>
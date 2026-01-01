<?php
require 'config.php';

// BẮT BUỘC phải để header đầu tiên, không có khoảng trắng nào trước đó
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Diem_Tong_Ket_LVTN_" . date('d-m-Y') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Điểm Tổng Kết LVTN</title>
    <style>
        table {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
        }

        .header {
            background: #0056b3;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .excellent {
            background: #d4edda;
        }

        .good {
            background: #fff3cd;
        }

        .average {
            background: #f8d7da;
        }
    </style>
</head>

<body>

    <h2 style="text-align:center;">BẢNG ĐIỂM TỔNG KẾT ĐỒ ÁN TỐT NGHIỆP</h2>
    <h3 style="text-align:center;">Khoa Công nghệ Thông tin - Năm học 2024-2025</h3>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <tr class="header">
            <th>STT</th>
            <th>MSSV</th>
            <th>Họ và tên</th>
            <th>Đề tài</th>
            <th>GV Hướng dẫn</th>
            <th>GV Phản biện</th>
            <th>Hội đồng</th>
            <th>Điểm GVHD<br>(50%)</th>
            <th>Điểm Hội đồng<br>(50%)</th>
            <th>Điểm TB</th>
            <th>Xếp loại</th>
        </tr>

        <?php
        $stt = 1;
        $sql = "
        SELECT 
            s.MaSV, s.HoTen, s.huong_de_tai,
            gvhd.HoTen AS gvhd_ten,
            gvpb.HoTen AS gvpb_ten,
            h.ten_hoidong,
            COALESCE(d.DiemLan1, 0) AS DiemGVHD,
            COALESCE(hd.DiemBaoVe, d.DiemLan2, 0) AS DiemHoiDong
        FROM danh_sach_sinh_vien s
        LEFT JOIN giang_vien gvhd ON s.gvhd = gvhd.MaGV
        LEFT JOIN giang_vien gvpb ON s.gvpb_id = gvpb.MaGV
        LEFT JOIN hoidong h ON s.hoidong_id = h.id
        LEFT JOIN qlsv_diem d ON s.MaSV = d.MaSV AND d.MaMH = 'LVTN'
        LEFT JOIN qlsv_diem_hoidong hd ON s.MaSV = hd.MaSV
        WHERE s.huong_de_tai IS NOT NULL AND s.huong_de_tai != ''
        ORDER BY s.MaSV
    ";

        $stmt = $pdo->query($sql);
        while ($sv = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $diem_gvhd = round($sv['DiemGVHD'], 2);
            $diem_hd   = round($sv['DiemHoiDong'], 2);
            $diem_tb   = round(($diem_gvhd + $diem_hd) / 2, 2);

            // Xếp loại
            if ($diem_tb >= 9.0) {
                $xl = "Xuất sắc";
                $class = "excellent";
            } else if ($diem_tb >= 8.0) {
                $xl = "Giỏi";
                $class = "good";
            } else if ($diem_tb >= 6.5) {
                $xl = "Khá";
                $class = "good";
            } else if ($diem_tb >= 5.0) {
                $xl = "Trung bình";
                $class = "average";
            } else {
                $xl = "Yếu";
                $class = "average";
            }

            echo "<tr class='$class'>
                <td class='center'>$stt</td>
                <td>{$sv['MaSV']}</td>
                <td>{$sv['HoTen']}</td>
                <td>{$sv['huong_de_tai']}</td>
                <td>{$sv['gvhd_ten']}</td>
                <td>{$sv['gvpb_ten']}</td>
                <td>{$sv['ten_hoidong']}</td>
                <td class='center bold text-primary'>$diem_gvhd</td>
                <td class='center bold text-danger'>$diem_hd</td>
                <td class='center bold' style='font-size:14pt;'>$diem_tb</td>
                <td class='center bold' style='color:#d39e00;'>$xl</td>
              </tr>";
            $stt++;
        }
        ?>
    </table>

    <br><br>
    <table width="100%">
        <tr>
            <td width="50%" style="text-align:center;">
                <i>Ngày ... tháng ... năm 2025</i><br><br><br><br><br>
                <strong>TRƯỞNG KHOA</strong><br>
                (Ký, ghi rõ họ tên)
            </td>
            <td width="50%" style="text-align:center;">
                <i>Ngày ... tháng ... năm 2025</i><br><br><br><br><br>
                <strong>CHỦ TỊCH HỘI ĐỒNG</strong><br>
                (Ký, ghi rõ họ tên)
            </td>
        </tr>
    </table>

</body>

</html>
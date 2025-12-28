<?php
require 'config.php';

// Cấu hình header để trình duyệt hiểu đây là file Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=BangDiem_LVTN_STU.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Lấy tham số lọc (nếu có)
$masv   = $_GET['masv']   ?? '';
$namhoc = $_GET['namhoc'] ?? '';
$hocky  = $_GET['hocky']  ?? '';

$where = [];
if ($masv)   $where[] = "sv.MaSV LIKE '%$masv%'";
if ($namhoc) $where[] = "d.NamHoc = '$namhoc'";
if ($hocky)  $where[] = "d.HocKy = '$hocky'";
$cond = $where ? "WHERE " . implode(" AND ", $where) : "";

// SQL lấy dữ liệu
$sql = "
    SELECT 
        sv.MaSV, sv.HoTen, sv.Lop, 
        COALESCE(n.ten_nhom, '') AS Nhom,
        COALESCE(gv.HoTen, '') AS GVHD,
        COALESCE(sv.huong_de_tai, '') AS DeTai,
        d.DiemLan1, d.DiemLan2,
        ROUND((IFNULL(d.DiemLan1, 0) + IFNULL(d.DiemLan2, 0)) / 2, 1) AS DiemCK
    FROM qlsv_diem d
    JOIN danh_sach_sinh_vien sv ON sv.MaSV = d.MaSV
    LEFT JOIN phan_thuoc_nhom ptn ON ptn.sinh_vien_id = sv.id
    LEFT JOIN nhom n ON n.id = ptn.nhom_id
    LEFT JOIN giang_vien gv ON gv.MaGV = n.giang_vien_huong_dan_id
    $cond
    ORDER BY sv.HoTen
";
$data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// --- BẮT ĐẦU VẼ BẢNG EXCEL ---
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"></head>';
echo '<body>';

// HEADER TRƯỜNG STU
echo '<table border="0" style="font-family: Times New Roman;">';
echo '<tr>
        <td colspan="4" style="font-weight:bold; font-size:12pt;">TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN</td>
        <td colspan="4" style="text-align:right; font-style:italic;">Mẫu số: 01-STU</td>
      </tr>';
echo '<tr><td colspan="8">&nbsp;</td></tr>';

// TIÊU ĐỀ CHÍNH
echo '<tr>
        <td colspan="8" style="text-align:center; font-size:16pt; font-weight:bold; color:#C00000;">
            BẢNG ĐIỂM TỔNG HỢP LUẬN VĂN TỐT NGHIỆP
        </td>
      </tr>';
echo '<tr>
        <td colspan="8" style="text-align:center; font-size:14pt; font-weight:bold; color:#002060;">
            KHOA CÔNG NGHỆ THÔNG TIN
        </td>
      </tr>';

// THÔNG TIN HỌC KỲ
$hk_hien_thi = $hocky ? "Học kỳ $hocky" : "Tất cả học kỳ";
$nh_hien_thi = $namhoc ? "Năm học $namhoc" : "";
echo '<tr>
        <td colspan="8" style="text-align:center; font-style:italic;">
            ' . $hk_hien_thi . ' - ' . $nh_hien_thi . '
        </td>
      </tr>';
echo '<tr><td colspan="8">&nbsp;</td></tr>';
echo '</table>';

// BẢNG DỮ LIỆU CHÍNH
echo '<table border="1" style="border-collapse:collapse; font-family: Times New Roman; font-size:12pt;">';
echo '<tr style="background-color:#FFFF00; font-weight:bold; text-align:center; height:40px;">
        <th width="50">STT</th>
        <th width="100">MSSV</th>
        <th width="200">Họ và Tên</th>
        <th width="80">Lớp</th>
        <th width="200">GV Hướng Dẫn</th>
        <th width="250">Đề tài</th>
        <th width="80">Điểm L1</th>
        <th width="80">Điểm L2</th>
        <th width="80">Tổng kết</th>
        <th width="100">Ghi chú</th>
      </tr>';

$stt = 1;
foreach ($data as $row) {
    $kq = ($row['DiemCK'] >= 5) ? 'Đạt' : 'Không đạt';
    $color = ($kq == 'Đạt') ? '#000000' : '#FF0000'; // Rớt màu đỏ

    echo '<tr>
            <td style="text-align:center;">' . $stt++ . '</td>
            <td style="text-align:center;">' . $row['MaSV'] . '</td>
            <td style="font-weight:bold;">' . $row['HoTen'] . '</td>
            <td style="text-align:center;">' . $row['Lop'] . '</td>
            <td>' . $row['GVHD'] . '</td>
            <td>' . $row['DeTai'] . '</td>
            <td style="text-align:center;">' . number_format($row['DiemLan1'], 1) . '</td>
            <td style="text-align:center;">' . ($row['DiemLan2'] ? number_format($row['DiemLan2'], 1) : '-') . '</td>
            <td style="text-align:center; font-weight:bold;">' . number_format($row['DiemCK'], 1) . '</td>
            <td style="text-align:center; color:' . $color . ';">' . $kq . '</td>
          </tr>';
}

echo '</table>';

// FOOTER CHỮ KÝ
echo '<br><br>';
echo '<table border="0" style="font-family: Times New Roman; font-size:12pt;">';
echo '<tr>
        <td colspan="4"></td>
        <td colspan="4" style="text-align:center; font-style:italic;">
            Tp. Hồ Chí Minh, ngày ' . date("d") . ' tháng ' . date("m") . ' năm ' . date("Y") . '
        </td>
      </tr>';
echo '<tr>
        <td colspan="4" style="text-align:center; font-weight:bold;">TRƯỞNG KHOA</td>
        <td colspan="4" style="text-align:center; font-weight:bold;">NGƯỜI LẬP BẢNG</td>
      </tr>';
echo '<tr><td colspan="8" style="height:80px;">&nbsp;</td></tr>'; // Khoảng trống ký tên
echo '<tr>
        <td colspan="4" style="text-align:center; font-weight:bold;">TS. Lương An Vinh</td>
        <td colspan="4" style="text-align:center; font-weight:bold;">Thư ký Giáo vụ</td>
      </tr>';
echo '</table>';

echo '</body></html>';
exit;
?>
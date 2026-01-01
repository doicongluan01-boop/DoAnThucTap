<?php
require 'config.php';

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=DS_KetQua_QuaTrinh_LVTN.xls");
header("Pragma: no-cache");
header("Expires: 0");

// SQL giống báo cáo
$sql = "
    SELECT 
        sv.MaSV,
        sv.HoTen,
        sv.Lop,
        n.ten_nhom,
        gv_hd.HoTen AS TenGVHD,
        gv_pb.HoTen AS TenGVPB,
        sv.duoc_bao_ve
    FROM danh_sach_sinh_vien sv
    LEFT JOIN phan_thuoc_nhom ptn ON ptn.sinh_vien_id = sv.id
    LEFT JOIN nhom n ON n.id = ptn.nhom_id
    LEFT JOIN giang_vien gv_hd ON gv_hd.MaGV = n.giang_vien_huong_dan_id
    LEFT JOIN giang_vien gv_pb ON gv_pb.MaGV = sv.gvpb_id
    ORDER BY sv.Lop, sv.MaSV
";

$data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Xuất Excel (HTML table)
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '<table border="1" cellspacing="0" cellpadding="6">';
echo '
<tr style="background:#FFFF00;font-weight:bold;text-align:center">
    <th>STT</th>
    <th>MSSV</th>
    <th>HỌ TÊN</th>
    <th>LỚP</th>
    <th>GV HƯỚNG DẪN</th>
    <th>GV PHẢN BIỆN</th>
    <th>KẾT QUẢ</th>
</tr>';

$stt = 1;
foreach ($data as $r) {

    // Map kết quả
    $kq = '';
    if ($r['duoc_bao_ve'] == 1) $kq = 'BV';
    elseif ($r['duoc_bao_ve'] == 2) $kq = 'DC50%';
    elseif ($r['duoc_bao_ve'] == 0) $kq = 'KBV';

    echo '<tr>
        <td align="center">'.$stt++.'</td>
        <td>'.$r['MaSV'].'</td>
        <td>'.$r['HoTen'].'</td>
        <td align="center">'.$r['Lop'].'</td>
        <td>'.$r['TenGVHD'].'</td>
        <td>'.$r['TenGVPB'].'</td>
        <td align="center">'.$kq.'</td>
    </tr>';
}

echo '</table>';

<?php
session_start();
require_once 'config.php';

/* ================== KIỂM TRA ĐĂNG NHẬP ================== */
if (!isset($_SESSION['role'])) {
    http_response_code(403);
    echo '<h3 style="text-align:center;color:red;margin-top:40px;">
            Vui lòng <a href="login.php">đăng nhập</a>
          </h3>';
    exit;
}

$isAdmin     = $_SESSION['role'] === 'admin';
$isGiangVien = $_SESSION['role'] === 'giangvien';
$MaGV        = $isGiangVien ? $_SESSION['MaGV'] : null;

/* ================== LẤY FILTER ================== */
$masv   = $_GET['masv']   ?? '';
$namhoc = $_GET['namhoc'] ?? '';
$hocky  = $_GET['hocky']  ?? '';

/* ================== WHERE + PARAMS ================== */
$where  = [];
$params = [];

if ($isGiangVien) {
    $where[]  = "n.giang_vien_huong_dan_id = ?";
    $params[] = $MaGV;
}

if ($masv) {
    $where[]  = "sv.MaSV LIKE ?";
    $params[] = "%$masv%";
}

if ($namhoc) {
    $where[]  = "d.NamHoc = ?";
    $params[] = $namhoc;
}

if ($hocky) {
    $where[]  = "d.HocKy = ?";
    $params[] = $hocky;
}

$cond = $where ? "WHERE " . implode(" AND ", $where) : "";

/* ================== SQL CHÍNH (ĐÃ FIX LẶP PB) ================== */
$sql = "
SELECT
    sv.MaSV,
    sv.HoTen,
    sv.Lop,

    COALESCE(gv.HoTen, 'Chưa có GVHD') AS GVHD,
    COALESCE(sv.huong_de_tai, 'Chưa giao đề tài') AS DeTai,

    d.HocKy,
    d.NamHoc,
    d.DiemLan1,
    d.DiemLan2,

    pb.DiemPB

FROM qlsv_diem d
JOIN danh_sach_sinh_vien sv ON sv.MaSV = d.MaSV

LEFT JOIN phan_thuoc_nhom ptn ON ptn.sinh_vien_id = sv.id
LEFT JOIN nhom n ON n.id = ptn.nhom_id
LEFT JOIN giang_vien gv ON gv.MaGV = n.giang_vien_huong_dan_id

/* ===== SUBQUERY TỔNG HỢP ĐIỂM PHẢN BIỆN ===== */
LEFT JOIN (
    SELECT 
        nhom_id,
        ROUND(
            AVG(
                (IFNULL(diem_noi_dung,0)
               + IFNULL(diem_hinh_thuc,0)
               + IFNULL(diem_sang_tao,0)) / 3
            )
        ,1) AS DiemPB
    FROM qlsv_diem_gvpb
    GROUP BY nhom_id
) pb ON pb.nhom_id = n.id

$cond
ORDER BY sv.HoTen, d.NamHoc DESC, d.HocKy
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Bảng điểm sinh viên</title>
<style>
body{
    font-family:Segoe UI,Arial;
    background:#eef2ff;
    padding:20px
}
h2{text-align:center;color:#0d47a1}
.search-box{
    background:#fff;padding:15px;border-radius:10px;
    display:flex;gap:10px;flex-wrap:wrap;justify-content:center;
    box-shadow:0 4px 12px rgba(0,0,0,.1)
}
input,select,button,a{
    padding:10px;border-radius:8px;border:1px solid #ccc
}
button{background:#0d47a1;color:#fff;font-weight:bold;cursor:pointer}
table{
    width:100%;margin-top:20px;border-collapse:collapse;
    background:#fff;border-radius:12px;overflow:hidden;
    box-shadow:0 6px 20px rgba(0,0,0,.1)
}
th{
    background:#0d47a1;color:#fff;padding:12px
}
td{
    padding:10px;border-bottom:1px solid #eee;text-align:center
}
tr:hover{background:#f5f9ff}
td:nth-child(3),td:nth-child(6){text-align:left}
</style>
</head>

<body>

<h2>BẢNG ĐIỂM SINH VIÊN (GVHD + PHẢN BIỆN)</h2>

<div class="search-box">
<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
    <input type="text" name="masv" placeholder="Mã SV" value="<?=$masv?>">
    <select name="namhoc">
        <option value="">Năm học</option>
        <?php
        $nh = $pdo->query("SELECT DISTINCT NamHoc FROM qlsv_diem ORDER BY NamHoc DESC");
        foreach ($nh as $r) {
            $sel = ($namhoc == $r['NamHoc']) ? 'selected' : '';
            echo "<option value='{$r['NamHoc']}' $sel>{$r['NamHoc']}</option>";
        }
        ?>
    </select>
    <select name="hocky">
        <option value="">Học kỳ</option>
        <option value="1" <?=$hocky=='1'?'selected':''?>>1</option>
        <option value="2" <?=$hocky=='2'?'selected':''?>>2</option>
    </select>
    <button type="submit">Lọc</button>
    <a href="diem.php">Xóa lọc</a>
</form>
</div>

<?php if (!$data): ?>
<h3 style="text-align:center;color:red;margin-top:40px">Không có dữ liệu</h3>
<?php else: ?>

<table>
<thead>
<tr>
    <th>STT</th>
    <th>Mã SV</th>
    <th>Họ tên</th>
    <th>Lớp</th>
    <th>GVHD</th>
    <th>Đề tài</th>
    <th>HK</th>
    <th>Năm học</th>
    <th>L1</th>
    <th>L2</th>
    <th>PB</th>
</tr>
</thead>
<tbody>

<?php
$i = 1;
foreach ($data as $r):
?>
<tr>
    <td><?=$i++?></td>
    <td><b><?=$r['MaSV']?></b></td>
    <td><?=$r['HoTen']?></td>
    <td><?=$r['Lop']?></td>
    <td><?=$r['GVHD']?></td>
    <td><?=$r['DeTai']?></td>
    <td><?=$r['HocKy']?></td>
    <td><?=$r['NamHoc']?></td>
    <td><?=number_format($r['DiemLan1'],1)?></td>
    <td><?=$r['DiemLan2']!==null?number_format($r['DiemLan2'],1):'-'?></td>
    <td><b><?=$r['DiemPB']!==null ? number_format($r['DiemPB'],1) : '-'?></b></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<?php endif; ?>

</body>
</html>

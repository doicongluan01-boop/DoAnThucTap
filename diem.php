<?php
session_start();
require_once 'config.php';

/* ================== 1. KIỂM TRA ĐĂNG NHẬP ================== */
if (!isset($_SESSION['role'])) {
    http_response_code(403);
    echo '<h3 style="text-align:center;color:red;margin-top:40px;">Vui lòng <a href="login.php">đăng nhập</a></h3>';
    exit;
}

$isAdmin     = $_SESSION['role'] === 'admin';
$isGiangVien = $_SESSION['role'] === 'giangvien';
$MaGV        = $isGiangVien ? $_SESSION['MaGV'] : null;

/* ================== 2. LẤY FILTER ================== */
$masv   = $_GET['masv']   ?? '';
$namhoc = $_GET['namhoc'] ?? '';
$hocky  = $_GET['hocky']  ?? '';
$export = $_GET['export'] ?? null; // Kiểm tra có lệnh xuất file không

/* ================== 3. WHERE + PARAMS ================== */
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

/* ================== 4. SQL CHÍNH ================== */
$sql = "
SELECT
    sv.MaSV, sv.HoTen, sv.Lop,
    COALESCE(gv.HoTen, 'Chưa có GVHD') AS GVHD,
    COALESCE(sv.huong_de_tai, 'Chưa giao đề tài') AS DeTai,
    d.HocKy, d.NamHoc, d.DiemLan1, d.DiemLan2, pb.DiemPB
FROM qlsv_diem d
JOIN danh_sach_sinh_vien sv ON sv.MaSV = d.MaSV
LEFT JOIN phan_thuoc_nhom ptn ON ptn.sinh_vien_id = sv.id
LEFT JOIN nhom n ON n.id = ptn.nhom_id
LEFT JOIN giang_vien gv ON gv.MaGV = n.giang_vien_huong_dan_id
LEFT JOIN (
    SELECT nhom_id, ROUND(AVG((IFNULL(diem_noi_dung,0)+ IFNULL(diem_hinh_thuc,0)+ IFNULL(diem_sang_tao,0)) / 3),1) AS DiemPB
    FROM qlsv_diem_gvpb GROUP BY nhom_id
) pb ON pb.nhom_id = n.id
$cond
ORDER BY sv.HoTen, d.NamHoc DESC, d.HocKy
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================== 5. XỬ LÝ XUẤT FILE (WORD/EXCEL) ================== */
if ($export) {
    $filename = "BangDiem_STU_" . date('d_m_Y');
    
    if ($export == 'excel') {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename.xls\"");
    } elseif ($export == 'word') {
        header("Content-Type: application/msword");
        header("Content-Disposition: attachment; filename=\"$filename.doc\"");
    }
    
    // Bắt đầu nội dung file (Dùng HTML Table để định dạng chuẩn STU)
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta charset="utf-8"><style>
            body { font-family: "Times New Roman", serif; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid black; padding: 5px; text-align: center; }
            .header-tbl { border: none; margin-bottom: 20px; }
            .header-tbl td { border: none; text-align: center; vertical-align: top; }
            .bold { font-weight: bold; }
            .upper { text-transform: uppercase; }
          </style></head><body>';
    
    // Header chuẩn form STU
    echo '<table class="header-tbl">';
    echo '<tr>
            <td style="width: 40%;">
                UBND TP. HỒ CHÍ MINH<br>
                <b>TRƯỜNG ĐH CÔNG NGHỆ SÀI GÒN</b><br>
                -----------------
            </td>
            <td style="width: 60%;">
                <b>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</b><br>
                Độc lập - Tự do - Hạnh phúc<br>
                -----------------------------------
            </td>
          </tr>';
    echo '</table>';
    
    echo '<h2 style="text-align:center; margin-top:20px;">BẢNG ĐIỂM TỔNG HỢP SINH VIÊN</h2>';
    if($namhoc) echo '<p style="text-align:center;">Năm học: '.$namhoc . ($hocky ? " - Học kỳ: $hocky" : "") . '</p>';

    // Bảng dữ liệu
    echo '<table>
            <thead>
                <tr style="background-color: #ccc;">
                    <th>STT</th>
                    <th>Mã SV</th>
                    <th>Họ và Tên</th>
                    <th>Lớp</th>
                    <th>GV Hướng Dẫn</th>
                    <th>Tên Đề Tài</th>
                    <th>GVHD</th>
                    <th>GVPB</th>
                    <th>Hội Đồng</th>
                </tr>
            </thead>
            <tbody>';
    
    $i = 1;
    foreach ($data as $r) {
        $dpb = $r['DiemPB'] !== null ? number_format($r['DiemPB'], 1) : '-';
        $d1  = number_format($r['DiemLan1'], 1);
        $d2  = $r['DiemLan2'] !== null ? number_format($r['DiemLan2'], 1) : '-';
        
        echo "<tr>
                <td>{$i}</td>
                <td style='mso-number-format:\"\@\"'>{$r['MaSV']}</td> <td style='text-align:left;'>{$r['HoTen']}</td>
                <td>{$r['Lop']}</td>
                <td style='text-align:left;'>{$r['GVHD']}</td>
                <td style='text-align:left;'>{$r['DeTai']}</td>
                <td>{$d1}</td>
                <td>{$dpb}</td>
                <td>{$d2}</td>
              </tr>";
        $i++;
    }
    echo '</tbody></table>';

    // Footer chữ ký
    echo '<table class="header-tbl" style="margin-top: 30px;">
            <tr>
                <td></td>
                <td>TP. Hồ Chí Minh, ngày ... tháng ... năm ...</td>
            </tr>
            <tr>
                <td><b>NGƯỜI LẬP BẢNG</b><br>(Ký và ghi rõ họ tên)</td>
                <td><b>XÁC NHẬN CỦA KHOA</b><br>(Ký và ghi rõ họ tên)</td>
            </tr>
            <tr style="height: 80px;"><td></td><td></td></tr>
          </table>';
    
    echo '</body></html>';
    exit; // Dừng code tại đây để trình duyệt tải file về
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Bảng điểm sinh viên</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body{font-family:Segoe UI,Arial;background:#eef2ff;padding:20px}
    h2{text-align:center;color:#0d47a1}
    .search-box{background:#fff;padding:15px;border-radius:10px;display:flex;gap:10px;flex-wrap:wrap;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,.1)}
    input,select,button,a.btn-reset{padding:10px;border-radius:8px;border:1px solid #ccc; text-decoration: none; color: black; display: inline-block;}
    button{color:#fff;font-weight:bold;cursor:pointer; border: none;}
    
    /* Màu nút */
    .btn-filter { background: #0d47a1; }
    .btn-excel { background: #217346; } /* Màu xanh Excel */
    .btn-word { background: #2b579a; }  /* Màu xanh Word */
    .btn-reset { background: #f0f0f0; border: 1px solid #ccc; }
    
    button:hover { opacity: 0.9; }

    table{width:100%;margin-top:20px;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,.1)}
    th{background:#0d47a1;color:#fff;padding:12px}
    td{padding:10px;border-bottom:1px solid #eee;text-align:center}
    tr:hover{background:#f5f9ff}
    td:nth-child(3),td:nth-child(6){text-align:left}
</style>
</head>

<body>

<h2>BẢNG ĐIỂM SINH VIÊN (GVHD + PHẢN BIỆN)</h2>

<div class="search-box">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
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

        <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Lọc</button>
        
        <a href="diem.php" class="btn-reset">Xóa lọc</a>

        <span style="border-left: 1px solid #ccc; margin: 0 10px;"></span>

        <button type="submit" name="export" value="excel" class="btn-excel">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </button>

        <button type="submit" name="export" value="word" class="btn-word">
            <i class="fas fa-file-word"></i> Xuất Word
        </button>
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
    <th>GVHD</th>
    <th>GVPB</th>
    <th>HĐ</th>
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
    <td><b><?=number_format($r['DiemLan1'],1)?></b></td>
    <td><b><?=$r['DiemPB']!==null ? number_format($r['DiemPB'],1) : '-'?></b></td>
    <td><b><?=$r['DiemLan2']!==null?number_format($r['DiemLan2'],1):'-'?></b></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<?php endif; ?>

</body>
</html>
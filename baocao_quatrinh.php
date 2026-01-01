<?php
session_start(); // 1. Khởi động Session
require 'config.php';
// ================== XỬ LÝ LƯU (Giữ nguyên) ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duoc_bao_ve'])) {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            UPDATE danh_sach_sinh_vien
            SET duoc_bao_ve = ?
            WHERE MaSV = ?
        ");

        foreach ($_POST['duoc_bao_ve'] as $masv => $value) {
            if ($value === '') continue;
            $stmt->execute([(int)$value, $masv]);
        }

        $pdo->commit();
        $msg = "Đã lưu kết quả thành công!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = "Lỗi khi lưu dữ liệu!";
    }
}



// 1. Câu SQL cơ bản (Chưa có WHERE và ORDER BY)
$sql = "
    SELECT 
        sv.MaSV,
        sv.HoTen,
        sv.Lop,
        sv.duoc_bao_ve,
        n.ten_nhom,
        gv_hd.HoTen AS TenGVHD,
        gv_pb.HoTen AS TenGVPB
    FROM danh_sach_sinh_vien sv
    LEFT JOIN phan_thuoc_nhom ptn 
            ON ptn.sinh_vien_id = sv.id
    LEFT JOIN nhom n 
            ON n.id = ptn.nhom_id
    LEFT JOIN giang_vien gv_hd 
            ON gv_hd.MaGV = n.giang_vien_huong_dan_id
    LEFT JOIN giang_vien gv_pb 
            ON gv_pb.MaGV = sv.gvpb_id
";

$params = []; // Mảng chứa tham số truyền vào SQL

// 2. Kiểm tra quyền để thêm điều kiện WHERE
// Giả sử session lưu role là 'giangvien' hoặc 'admin', và 'MaGV' là mã giảng viên
$role = $_SESSION['role'] ?? '';
$maGV = $_SESSION['MaGV'] ?? '';

if ($role === 'giangvien') {
    // Nếu là Giảng viên: Chỉ lấy sinh viên thuộc nhóm do mình hướng dẫn
    $sql .= " WHERE n.giang_vien_huong_dan_id = ? ";
    $params[] = $maGV;
} 
// Nếu là Admin thì không thêm WHERE (lấy tất cả)

// 3. Thêm sắp xếp
$sql .= " ORDER BY sv.Lop ASC, sv.MaSV ASC";

// 4. Thực thi
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================== HẾT PHẦN PHP XỬ LÝ ==================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Báo cáo quá trình thực hiện LVTN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    font-family:"Times New Roman",Times,serif;
    font-size:11pt;
    margin:0;
    padding:20px;
}
@page{
    size:A4 landscape;
    margin:1cm;
}
@media print{
    .no-print{display:none!important}
    body{-webkit-print-color-adjust:exact}
}

.header-table{width:100%;margin-bottom:10px}
.header-left{text-align:left;font-weight:bold;text-transform:uppercase;font-size:12pt}
.header-right{text-align:right;font-weight:bold;font-style:italic}

.title-section{text-align:center;margin-bottom:20px}
.main-title{font-size:16pt;font-weight:bold;color:#C00000;text-transform:uppercase}
.sub-title{font-size:13pt;font-weight:bold;color:#002060;text-transform:uppercase}
.major-title{font-size:12pt;font-weight:bold;border:1px solid #000;display:inline-block;padding:5px 20px}

.legend-box{border:2px solid #000;padding:10px;margin-bottom:20px}
.legend-title{font-weight:bold;text-decoration:underline}
.legend-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;width:80%;margin:auto}

.text-blue{color:#002060;font-weight:bold}
.text-red{color:#C00000;font-weight:bold}
.text-purple{color:purple;font-weight:bold}

.data-table{width:100%;border-collapse:collapse}
.data-table th,.data-table td{border:1px solid #000;padding:6px 4px}
.data-table th{text-align:center;background:#FFFF00;height:40px}

.col-stt{width:30px;text-align:center}
.col-mssv{width:80px;text-align:center}
.col-ten{width:160px}
.col-lop{width:60px;text-align:center}
.col-gv{width:150px}
.col-hh{width:70px;text-align:center}
.col-donvi{width:80px;text-align:center}
.col-kq{width:80px;text-align:center;font-weight:bold}

/* ===== NÚT LƯU NỔI ===== */
.floating-save {
    position: fixed;
    right: 25px;
    bottom: 90px;
    z-index: 9999;
}

.floating-save button {
    padding: 14px 26px;
    background: #198754;
    color: #fff;
    border: none;
    border-radius: 999px;
    font-weight: bold;
    font-size: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,.25);
    cursor: pointer;
}

.floating-save button:hover {
    background: #157347;
    transform: translateY(-2px);
}
</style>
</head>

<body>

<?php if (!empty($msg)): ?>
<div class="no-print" style="text-align:center;color:green;font-weight:bold;margin-bottom:10px;">
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>
<?php if ($role !== 'giangvien'): ?>
<div class="no-print" style="text-align:center;margin-bottom:15px;">
   <div class="no-print" style="text-align:center;margin-bottom:15px;">
    <a href="xuat_excel_baocao.php"
       class="btn btn-success fw-bold px-4 py-2">
       📊 XUẤT EXCEL
    </a>
</div>
<?php endif; ?>
</div>

<form method="post">

<table class="header-table">
<tr>
<td class="header-left">TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN</td>
<td class="header-right">Phụ lục 1</td>
</tr>
</table>

<div class="title-section">
<div class="main-title">BÁO CÁO QUÁ TRÌNH THỰC HIỆN LUẬN VĂN TỐT NGHIỆP</div>
<div class="sub-title">ĐẠI HỌC 2021 - HỆ CHÍNH QUY</div>
<div class="major-title">NGÀNH : CÔNG NGHỆ THÔNG TIN</div>
</div>

<div class="legend-box">
<div class="legend-title">QUY ƯỚC KẾT QUẢ</div>
<div class="legend-grid">
<div>Được bảo vệ: <span class="text-blue">BV</span></div>
<div>Đình chỉ 50%: <span class="text-purple">DC50%</span></div>
<div>Không được bảo vệ: <span class="text-red">KBV</span></div>
</div>
</div>

<table class="data-table">
<thead>
<tr>
<th class="col-stt">STT</th>
<th class="col-mssv">MSSV</th>
<th class="col-ten">HỌ TÊN</th>
<th class="col-lop">LỚP</th>
<th class="col-gv">GVHD</th>
<th class="col-hh">HH-HV</th>
<th class="col-donvi">ĐƠN VỊ</th>
<th class="col-gv">GVPB</th>
<th class="col-hh">HH-HV</th>
<th class="col-donvi">ĐƠN VỊ</th>
<th class="col-kq">KẾT QUẢ</th>
</tr>
</thead>

<tbody>
<?php
if ($data && count($data) > 0):
    $stt=1;
    foreach($data as $row):
?>
<tr>
<td class="col-stt"><?= $stt++ ?></td>
<td class="col-mssv"><?= htmlspecialchars($row['MaSV']) ?></td>
<td class="col-ten"><b><?= htmlspecialchars($row['HoTen']) ?></b></td>
<td class="col-lop"><?= htmlspecialchars($row['Lop']) ?></td>

<td class="col-gv"><?= htmlspecialchars($row['TenGVHD'] ?? '') ?></td>
<td class="col-hh">Thạc sĩ</td>
<td class="col-donvi">ĐH CNSG</td>

<td class="col-gv"><?= htmlspecialchars($row['TenGVPB'] ?? '') ?></td>
<td class="col-hh">Thạc sĩ</td>
<td class="col-donvi">ĐH CNSG</td>

<td class="col-kq">
<select name="duoc_bao_ve[<?= $row['MaSV'] ?>]"
        class="form-select form-select-sm fw-bold">
    <option value="">--</option>
    <option value="1" <?= $row['duoc_bao_ve']==1?'selected':'' ?>>BV</option>
    <option value="2" <?= $row['duoc_bao_ve']==2?'selected':'' ?>>DC50%</option>
    <option value="0" <?= $row['duoc_bao_ve']==0?'selected':'' ?>>KBV</option>
</select>
</td>
</tr>
<?php 
    endforeach; 
else:
?>
    <tr>
        <td colspan="11" style="text-align:center; padding: 20px;">
            Không có sinh viên nào trong danh sách.
        </td>
    </tr>
<?php endif; ?>
</tbody>
</table>

<div class="no-print" style="text-align:center;margin-top:20px;">
<button type="submit"
    style="padding:10px 25px;background:#198754;color:#fff;border:none;border-radius:5px;font-weight:bold;">
    💾 LƯU KẾT QUẢ
</button>
</div>
<div class="floating-save no-print">
    <button type="submit">
        💾 LƯU KẾT QUẢ
    </button>
</div>

</form>

</body>
</html>
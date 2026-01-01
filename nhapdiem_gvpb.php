<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

/* ================== CHECK + GÁN SESSION ================== */
$MaGV    = $_SESSION['MaGV']    ?? null;
$role    = $_SESSION['role']    ?? null;
$is_gvpb = $_SESSION['is_gvpb'] ?? 0;

/* 🔥 FIX NOTICE: LUÔN KHỞI TẠO nhom_id */
$nhom_id = $_POST['nhom_id'] ?? '';

if ($role !== 'giangvien' || !$MaGV || !$is_gvpb) {
    echo '<div style="margin:50px;color:red;font-size:18px">
            ❌ Bạn không có quyền phản biện
          </div>';
    exit;
}

$msg = '';

/* ================== LƯU ĐIỂM + NHẬN XÉT ================== */
if (isset($_POST['save_all'])) {

    if (empty($nhom_id) || !ctype_digit($nhom_id)) {
        die('❌ Vui lòng chọn nhóm hợp lệ');
    }

    $nhom_id = (int)$nhom_id;
    $MaSVs   = $_POST['MaSV'] ?? [];

    $nhan_xet_chung = $_POST['nhan_xet_chung'] ?? '';
    $uu_diem        = $_POST['uu_diem'] ?? '';
    $thieu_sot      = $_POST['thieu_sot'] ?? '';
    $cau_hoi        = $_POST['cau_hoi'] ?? '';

    foreach ($MaSVs as $i => $MaSV) {

        $nd = floatval($_POST['diem_noi_dung'][$i] ?? 0);
        $ht = floatval($_POST['diem_hinh_thuc'][$i] ?? 0);
        $st = floatval($_POST['diem_sang_tao'][$i] ?? 0);
        
        // Tính trung bình cộng chia 3
        if ($nd > 0 || $ht > 0 || $st > 0) {
             $tong = ($nd + $ht + $st) / 3;
             $tong = round($tong, 2); 
        } else {
             $tong = 0;
        }

        $stmt = $pdo->prepare("
            REPLACE INTO qlsv_diem_gvpb
            (MaSV, MaGV, nhom_id,
             diem_noi_dung, diem_hinh_thuc, diem_sang_tao, diem_tong,
             nhan_xet_chung, uu_diem, thieu_sot, cau_hoi)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $MaSV, $MaGV, $nhom_id,
            $nd, $ht, $st, $tong,
            $nhan_xet_chung, $uu_diem, $thieu_sot, $cau_hoi
        ]);
    }

    $msg = '✔️ Đã lưu điểm và nhận xét cho toàn bộ nhóm';
}

/* ================== 1. LẤY DANH SÁCH NHÓM + GVHD ================== */
$stmt = $pdo->prepare("
    SELECT DISTINCT
        n.id,
        n.ten_nhom,
        gv.HoTen AS ten_gvhd
    FROM nhom n
    JOIN phan_thuoc_nhom ptn
        ON ptn.nhom_id = n.id
    JOIN danh_sach_sinh_vien sv
        ON sv.id = ptn.sinh_vien_id
    LEFT JOIN giang_vien gv
        ON gv.MaGV = n.giang_vien_huong_dan_id
    WHERE sv.gvpb_id = ?
    ORDER BY n.ten_nhom
");
$stmt->execute([$MaGV]);
$nhomList = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================== 2. (MỚI) LẤY ID CÁC NHÓM ĐÃ CHẤM ================== */
// Lấy danh sách các nhóm đã có dữ liệu trong bảng điểm của GV này
$stmtCheck = $pdo->prepare("
    SELECT DISTINCT nhom_id 
    FROM qlsv_diem_gvpb 
    WHERE MaGV = ?
");
$stmtCheck->execute([$MaGV]);
// Tạo mảng chứa ID các nhóm đã chấm (Ví dụ: [1, 5, 8])
$gradedGroups = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);


/* ================== 3. DSSV THEO NHÓM ================== */
$svList = [];
if (ctype_digit((string)$nhom_id)) {
    $stmt = $pdo->prepare("
        SELECT sv.MaSV, sv.HoTen, sv.Lop
        FROM phan_thuoc_nhom ptn
        JOIN danh_sach_sinh_vien sv
            ON sv.id = ptn.sinh_vien_id
        WHERE ptn.nhom_id = ?
        ORDER BY sv.MaSV
    ");
    $stmt->execute([(int)$nhom_id]);
    $svList = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Chấm điểm phản biện</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body.bg-light{
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    min-height:100vh;
}
.container{max-width:1200px}
.container>form{
    background:#fff;
    border-radius:20px;
    padding:35px;
    box-shadow:0 15px 40px rgba(0,0,0,.25);
}
.table th{
    background:#4e73df;
    color:#fff;
    text-align:center;
    vertical-align: middle;
}
.table td{text-align:center;vertical-align:middle}
.inp-score { width: 100%; min-width: 80px; }
.inp-total { background-color: #f8f9fa; font-weight: bold; color: #0d6efd; }
</style>
</head>

<body class="bg-light">
<div class="container mt-4">
<h3 class="text-center text-white fw-bold mb-4">Chấm điểm phản biện</h3>

<?php if ($msg): ?>
<div class="alert alert-success text-center"><?= $msg ?></div>
<?php endif; ?>

<form method="post">

<label class="form-label fw-bold">Chọn nhóm</label>
<select name="nhom_id" class="form-select mb-3" onchange="this.form.submit()">
    <option value="">-- Chọn nhóm để chấm --</option>
    <?php foreach ($nhomList as $n): 
        // Kiểm tra xem nhóm này có trong danh sách đã chấm chưa
        $isGraded = in_array($n['id'], $gradedGroups);
        $tick = $isGraded ? '✅ ' : ''; // Dấu tích xanh
        $textStyle = $isGraded ? 'font-weight:bold; color:green;' : '';
    ?>
        <option value="<?= $n['id'] ?>" 
            style="<?= $textStyle ?>"
            <?= ($n['id'] == $nhom_id ? 'selected' : '') ?>>
            <?= $tick . $n['ten_nhom'] ?> — GVHD: <?= $n['ten_gvhd'] ?? 'Chưa phân' ?> 
            <?= $isGraded ? '(Đã nhập)' : '' ?>
        </option>
    <?php endforeach; ?>
</select>

<?php if ($svList): ?>
<table class="table table-bordered mt-3">
<thead>
    <tr>
        <th>MSSV</th>
        <th>Họ tên</th>
        <th>Lớp</th>
        <th>Nội dung<br><small class="fw-normal font-monospace">(0-10)</small></th>
        <th>Hình thức<br><small class="fw-normal font-monospace">(0-10)</small></th>
        <th>Sáng tạo<br><small class="fw-normal font-monospace">(0-10)</small></th>
        <th class="bg-warning text-dark">TB Cộng<br><small class="fw-normal font-monospace">(Thang 10)</small></th>
    </tr>
</thead>
<tbody>
<?php foreach ($svList as $i=>$sv): 
    // Lấy lại điểm cũ từ DB nếu có để hiển thị lại
    // Đoạn này giúp khi load lại trang thì điểm cũ hiện lên ô input
    $stmtDiem = $pdo->prepare("SELECT * FROM qlsv_diem_gvpb WHERE MaSV = ? AND MaGV = ?");
    $stmtDiem->execute([$sv['MaSV'], $MaGV]);
    $d = $stmtDiem->fetch(PDO::FETCH_ASSOC);
    
    $v_nd = $d['diem_noi_dung'] ?? '';
    $v_ht = $d['diem_hinh_thuc'] ?? '';
    $v_st = $d['diem_sang_tao'] ?? '';
    $v_tong = $d['diem_tong'] ?? '';
?>
<tr>
    <td>
        <?= $sv['MaSV'] ?>
        <input type="hidden" name="MaSV[]" value="<?= $sv['MaSV'] ?>">
    </td>
    <td class="text-start fw-bold"><?= $sv['HoTen'] ?></td>
    <td><?= $sv['Lop'] ?></td>
    
    <td>
        <input type="number" step="0.1" min="0" max="10" 
               class="form-control text-center inp-score inp-nd" 
               name="diem_noi_dung[]" placeholder="0-10" required
               value="<?= $v_nd ?>">
    </td>
    <td>
        <input type="number" step="0.1" min="0" max="10" 
               class="form-control text-center inp-score inp-ht" 
               name="diem_hinh_thuc[]" placeholder="0-10" required
               value="<?= $v_ht ?>">
    </td>
    <td>
        <input type="number" step="0.1" min="0" max="10" 
               class="form-control text-center inp-score inp-st" 
               name="diem_sang_tao[]" placeholder="0-10" required
               value="<?= $v_st ?>">
    </td>
    
    <td>
        <input type="text" class="form-control text-center inp-total" 
               name="diem_tong[]" readonly tabindex="-1" 
               value="<?= $v_tong ?>">
    </td>
</tr>

<?php 
    // Nếu là sinh viên cuối cùng của vòng lặp, lấy nhận xét ra để điền vào form dưới
    if ($i == count($svList) - 1) {
        $nx_chung = $d['nhan_xet_chung'] ?? '';
        $nx_uu = $d['uu_diem'] ?? '';
        $nx_thieu = $d['thieu_sot'] ?? '';
        $nx_cauhoi = $d['cau_hoi'] ?? '';
    }
endforeach; 
?>
</tbody>
</table>

<hr class="my-4">

<h5 class="fw-bold text-primary mb-3">
    Nhận xét phản biện (cho cả nhóm)
</h5>

<div class="mb-3">
    <label class="form-label fw-semibold">4. Nhận xét chung</label>
    <textarea name="nhan_xet_chung" class="form-control" rows="3"
        placeholder="Nhận xét tổng quát về đề tài..."><?= $nx_chung ?? '' ?></textarea>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">5. Những ưu điểm chính</label>
    <textarea name="uu_diem" class="form-control" rows="3"
        placeholder="Ưu điểm của đề tài..."><?= $nx_uu ?? '' ?></textarea>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">6. Những thiếu sót chính</label>
    <textarea name="thieu_sot" class="form-control" rows="3"
        placeholder="Thiếu sót, hạn chế..."><?= $nx_thieu ?? '' ?></textarea>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">8. Câu hỏi phản biện</label>
    <textarea name="cau_hoi" class="form-control" rows="3"
        placeholder="Câu hỏi dành cho sinh viên..."><?= $nx_cauhoi ?? '' ?></textarea>
</div>

<button class="btn btn-primary px-4" name="save_all">
    💾 Lưu tất cả
</button>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
        const inpND = row.querySelector(".inp-nd");
        const inpHT = row.querySelector(".inp-ht");
        const inpST = row.querySelector(".inp-st");
        const inpTotal = row.querySelector(".inp-total");

        function calcAverage() {
            let nd = parseFloat(inpND.value) || 0;
            let ht = parseFloat(inpHT.value) || 0;
            let st = parseFloat(inpST.value) || 0;

            let average = (nd + ht + st) / 3;

            inpTotal.value = average.toFixed(2);
            
            if(nd > 10 || ht > 10 || st > 10) {
                inpTotal.style.backgroundColor = "#ffcccc"; 
                inpTotal.style.color = "red";
                inpTotal.value = "Lỗi >10";
            } else {
                inpTotal.style.backgroundColor = "#e8f0fe"; 
                inpTotal.style.color = "#0d6efd";
            }
        }

        [inpND, inpHT, inpST].forEach(input => {
            input.addEventListener("input", calcAverage);
        });
    });
});
</script>

<?php endif; ?>

</form>
</div>
</body>
</html>
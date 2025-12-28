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
        $tong = $nd + $ht + $st;

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

/* ================== LẤY DANH SÁCH NHÓM + GVHD ================== */
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

/* ================== DSSV THEO NHÓM ================== */
$svList = [];
if (ctype_digit($nhom_id)) {
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
}
.table td{text-align:center;vertical-align:middle}
.score-input-group{display:inline-block;margin:0 6px}
.score-input-group input{width:90px;text-align:center}
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
    <option value="">-- Chọn nhóm --</option>
    <?php foreach ($nhomList as $n): ?>
        <option value="<?= $n['id'] ?>"
            <?= ($n['id'] == $nhom_id ? 'selected' : '') ?>>
            <?= $n['ten_nhom'] ?> — GVHD: <?= $n['ten_gvhd'] ?? 'Chưa phân' ?>
        </option>
    <?php endforeach; ?>
</select>

<?php if ($svList): ?>
<table class="table table-bordered mt-3">
<tr>
    <th>MSSV</th>
    <th>Họ tên</th>
    <th>Lớp</th>
    <th>Nhập điểm (ND – HT – ST)</th>
</tr>

<?php foreach ($svList as $i=>$sv): ?>
<tr>
<td><?= $sv['MaSV'] ?></td>
<td><?= $sv['HoTen'] ?></td>
<td><?= $sv['Lop'] ?></td>
<td>
<input type="hidden" name="MaSV[]" value="<?= $sv['MaSV'] ?>">
<div class="score-input-group">
    <input type="number" step="0.1" name="diem_noi_dung[]" placeholder="ND" required>
</div>
<div class="score-input-group">
    <input type="number" step="0.1" name="diem_hinh_thuc[]" placeholder="HT" required>
</div>
<div class="score-input-group">
    <input type="number" step="0.1" name="diem_sang_tao[]" placeholder="ST" required>
</div>
</td>
</tr>
<?php endforeach; ?>
</table>

<hr class="my-4">

<h5 class="fw-bold text-primary mb-3">
    Nhận xét phản biện (cho cả nhóm)
</h5>

<div class="mb-3">
    <label class="form-label fw-semibold">4. Nhận xét chung</label>
    <textarea name="nhan_xet_chung" class="form-control" rows="3"
        placeholder="Nhận xét tổng quát về đề tài..."></textarea>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">5. Những ưu điểm chính</label>
    <textarea name="uu_diem" class="form-control" rows="3"
        placeholder="Ưu điểm của đề tài..."></textarea>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">6. Những thiếu sót chính</label>
    <textarea name="thieu_sot" class="form-control" rows="3"
        placeholder="Thiếu sót, hạn chế..."></textarea>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">8. Câu hỏi phản biện</label>
    <textarea name="cau_hoi" class="form-control" rows="3"
        placeholder="Câu hỏi dành cho sinh viên..."></textarea>
</div>

<button class="btn btn-primary px-4" name="save_all">
    💾 Lưu tất cả
</button>

<?php endif; ?>

</form>
</div>
</body>
</html>

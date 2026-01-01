<?php
session_start();
require 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

/* ===== CHECK QUYỀN GVPB ===== */
if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'giangvien' ||
    empty($_SESSION['is_gvpb'])
) {
    die('Bạn không có quyền phản biện');
}

$MaGV = $_SESSION['MaGV'];

/* ===== 1. SỬA CÂU TRUY VẤN LẤY DANH SÁCH NHÓM + GVHD ===== */
// Thêm LEFT JOIN giang_vien để lấy tên GVHD
$nhomList = $pdo->prepare("
    SELECT DISTINCT 
        n.id, 
        n.ten_nhom,
        gv.HoTen as ten_gvhd  -- Lấy thêm cột tên GVHD
    FROM nhom n
    JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
    JOIN danh_sach_sinh_vien sv ON sv.id = ptn.sinh_vien_id
    LEFT JOIN giang_vien gv ON gv.MaGV = n.giang_vien_huong_dan_id -- Nối bảng
    WHERE sv.gvpb_id = ?
");
$nhomList->execute([$MaGV]);
$nhomList = $nhomList->fetchAll();

/* ===== XỬ LÝ XUẤT WORD ===== */
if (isset($_POST['export_word'])) {

    $nhom_id = $_POST['nhom_id'];

    /* --- Lấy đề tài --- */
    $stmt = $pdo->prepare("SELECT huong_de_tai FROM nhom WHERE id=?");
    $stmt->execute([$nhom_id]);
    $nhom = $stmt->fetch();

    if (!$nhom) die('Không tìm thấy nhóm');

    /* --- Lấy DSSV + điểm + nhận xét --- */
    $stmt = $pdo->prepare("
        SELECT sv.MaSV, sv.HoTen, sv.Lop,
               d.diem_tong,
               d.nhan_xet_chung, d.uu_diem, d.thieu_sot, d.cau_hoi

        FROM danh_sach_sinh_vien sv
        JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id
        LEFT JOIN qlsv_diem_gvpb d
            ON d.MaSV = sv.MaSV
           AND d.MaGV = ?
           AND d.nhom_id = ?
        WHERE ptn.nhom_id = ?
        ORDER BY sv.MaSV
    ");
    $stmt->execute([$MaGV, $nhom_id, $nhom_id]);
    $svs = $stmt->fetchAll();

    if (count($svs) == 0) {
        die('Nhóm chưa có sinh viên');
    }

    /* --- Load mẫu Word --- */
    $template = new TemplateProcessor(
        'Mau_02_01_PHIEU_CHAM_PHAN_BIEN_NHOM.docx'
    );

    /* --- Thông tin chung --- */
    $template->setValue('TEN_DE_TAI', $nhom['huong_de_tai']);
    $template->setValue('GV_PHAN_BIEN', $_SESSION['hoten']);
    
    // Nếu bạn muốn in cả tên GVHD vào file Word thì uncomment dòng dưới (và thêm placeholder vào file mẫu)
    // $template->setValue('GV_HUONG_DAN', $_POST['ten_gvhd_hidden'] ?? '');

    /* --- Sinh viên + điểm --- */
    foreach ($svs as $i => $sv) {
        $idx = $i + 1;
        $template->setValue("SV{$idx}_HOTEN", $sv['HoTen']);
        $template->setValue("SV{$idx}_MSSV",  $sv['MaSV']);
        $template->setValue("SV{$idx}_LOP",   $sv['Lop']);
        $template->setValue("SV{$idx}_DIEM",  $sv['diem_tong'] ?? '');
    }

    /* --- Nhận xét --- */
    $template->setValue('NHAN_XET_CHUNG', $svs[0]['nhan_xet_chung'] ?? '');
    $template->setValue('UU_DIEM',        $svs[0]['uu_diem'] ?? '');
    $template->setValue('THIEU_SOT',      $svs[0]['thieu_sot'] ?? '');
    $template->setValue('CAU_HOI',        $svs[0]['cau_hoi'] ?? '');

    /* --- Kết luận tự động --- */
    $ket_luan = 'Được bảo vệ';
    foreach ($svs as $sv) {
        // Kiểm tra nếu chưa chấm điểm hoặc điểm < 5
        if (!isset($sv['diem_tong']) || $sv['diem_tong'] === '' || $sv['diem_tong'] < 5) {
            $ket_luan = 'Không được bảo vệ';
            break;
        }
    }
    $template->setValue('KET_LUAN', $ket_luan);

    /* --- Xuất file --- */
    $filename = "Phieu_Phan_Bien_Nhom_$nhom_id.docx";
    header("Content-Disposition: attachment; filename=$filename");
    $template->saveAs("php://output");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Xuất phiếu phản biện</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-5" style="max-width:600px">
<h4 class="text-center mb-4">Xuất phiếu phản biện (Word)</h4>

<form method="post">
    <label class="form-label fw-bold">Chọn nhóm</label>
    
    <select name="nhom_id" class="form-select mb-3" required>
        <option value="">-- Chọn nhóm --</option>
        <?php foreach ($nhomList as $n): ?>
            <option value="<?= $n['id'] ?>">
                <?= $n['ten_nhom'] ?> — GVHD: <?= $n['ten_gvhd'] ?? 'Chưa phân' ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="text-center">
        <button class="btn btn-success px-4" name="export_word">
            📄 Xuất file Word
        </button>
    </div>
</form>

<div class="text-center mt-3">
    <a href="nhapdiem_gvpb.php" class="text-decoration-none">
        <i class="fas fa-arrow-left"></i> Quay lại trang chấm điểm
    </a>
</div>

</div>
</body>
</html>
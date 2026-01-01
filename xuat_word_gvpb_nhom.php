<?php
session_start();
require_once 'config.php';

require_once 'vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;

// ===== KIỂM TRA QUYỀN =====
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'giangvien') {
    die('Không có quyền');
}

$MaGV = $_SESSION['MaGV'];
$nhom_id = $_GET['nhom_id'] ?? null;
if (!$nhom_id) die('Thiếu nhóm');

// ===== LOAD TEMPLATE =====
$template = new TemplateProcessor('Mau_02_01_PHIEU_CHAM_PHAN_BIEN_NHOM.docx');

// ===== LẤY THÔNG TIN NHÓM =====
$stmt = $pdo->prepare("
    SELECT n.ten_nhom, n.huong_de_tai
    FROM nhom n
    WHERE n.id = ?
");
$stmt->execute([$nhom_id]);
$nhom = $stmt->fetch(PDO::FETCH_ASSOC);

// ===== LẤY DANH SÁCH SV + ĐIỂM =====
$stmt = $pdo->prepare("
    SELECT sv.MaSV, sv.HoTen, sv.Lop, d.diem_tong
    FROM danh_sach_sinh_vien sv
    JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id
    LEFT JOIN qlsv_diem_gvpb d 
        ON d.MaSV = sv.MaSV 
        AND d.MaGV = ?
        AND d.nhom_id = ?
    ORDER BY sv.MaSV
");
$stmt->execute([$MaGV, $nhom_id]);
$svs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== SET THÔNG TIN CHUNG =====
$template->setValue('TEN_DE_TAI', $nhom['huong_de_tai']);
$template->setValue('GV_PHAN_BIEN', $_SESSION['hoten']);

// ===== SET SINH VIÊN =====
for ($i = 0; $i < count($svs); $i++) {
    $idx = $i + 1;
    $template->setValue("SV{$idx}_HOTEN", $svs[$i]['HoTen']);
    $template->setValue("SV{$idx}_MSSV", $svs[$i]['MaSV']);
    $template->setValue("SV{$idx}_LOP",  $svs[$i]['Lop']);
    $template->setValue("SV{$idx}_DIEM", $svs[$i]['diem_tong'] ?? '');
}

// ===== LẤY NHẬN XÉT CHUNG (LẤY 1 SV BẤT KỲ TRONG NHÓM) =====
$stmt = $pdo->prepare("
    SELECT nhan_xet_chung, uu_diem, thieu_sot, cau_hoi
    FROM qlsv_diem_gvpb
    WHERE MaGV = ? AND nhom_id = ?
    LIMIT 1
");
$stmt->execute([$MaGV, $nhom_id]);
$nx = $stmt->fetch(PDO::FETCH_ASSOC);

$template->setValue('NHAN_XET_CHUNG', $nx['nhan_xet_chung'] ?? '');
$template->setValue('UU_DIEM', $nx['uu_diem'] ?? '');
$template->setValue('THIEU_SOT', $nx['thieu_sot'] ?? '');
$template->setValue('CAU_HOI', $nx['cau_hoi'] ?? '');

// ===== BƯỚC 2: AUTO KẾT LUẬN THEO ĐIỂM =====
$ket_luan = '';
$all_pass = true;

foreach ($svs as $sv) {
    if (($sv['diem_tong'] ?? 0) < 5) {
        $all_pass = false;
        break;
    }
}

if ($all_pass) {
    $ket_luan = 'Được bảo vệ';
} else {
    $ket_luan = 'Không được bảo vệ';
}

$template->setValue('KET_LUAN', $ket_luan);

// ===== XUẤT FILE =====
$filename = "Phieu_Phan_Bien_Nhom_{$nhom_id}.docx";
header("Content-Disposition: attachment; filename=$filename");
$template->saveAs("php://output");
exit;

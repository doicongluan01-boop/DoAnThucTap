<?php
require 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

session_start();

/* ================== CHECK QUYỀN ================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'giangvien') {
    die('Bạn không có quyền truy cập');
}

$MaGV = $_SESSION['MaGV'] ?? '';
if (!$MaGV) die('Không tìm thấy mã giảng viên');

/* ================== INFO GV ================== */
$stmt = $pdo->prepare("SELECT HoTen FROM giang_vien WHERE MaGV=?");
$stmt->execute([$MaGV]);
$gv = $stmt->fetch();
$tenGV = $gv['HoTen'] ?? '';

/* ================== API NHÓM ================== */
if (isset($_GET['action']) && $_GET['action'] === 'get_nhom') {
    header("Content-Type: application/json; charset=utf-8");
    $st = $pdo->prepare("SELECT id, ten_nhom FROM nhom WHERE giang_vien_huong_dan_id=?");
    $st->execute([$MaGV]);
    echo json_encode($st->fetchAll());
    exit;
}

/* ================== API SV ================== */
if (isset($_GET['action']) && $_GET['action'] === 'get_sv_nhom') {
    $nhom_id = (int)($_GET['nhom_id'] ?? 0);
    header("Content-Type: application/json; charset=utf-8");

    $st = $pdo->prepare("
        SELECT sv.HoTen, sv.MaSV, sv.Lop
        FROM phan_thuoc_nhom p
        JOIN danh_sach_sinh_vien sv ON sv.id = p.sinh_vien_id
        WHERE p.nhom_id = ?
        ORDER BY sv.HoTen
    ");
    $st->execute([$nhom_id]);
    echo json_encode($st->fetchAll());
    exit;
}

/* ================== POST: XUẤT WORD ================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nhom_id   = (int)($_POST['nhom_id'] ?? 0);
    $tieu_de   = trim($_POST['tieu_de'] ?? '');
    $tai_lieu  = trim($_POST['tai_lieu'] ?? '');
    $ngay_giao = $_POST['ngay_giao'] ?: date('Y-m-d');
    $deadline  = $_POST['deadline'] ?: date('Y-m-d');
    $tasks     = array_filter($_POST['tasks'] ?? []);

    if (!$nhom_id) die('Chưa chọn nhóm');

    /* ===== LẤY SV ===== */
    $st = $pdo->prepare("
        SELECT sv.HoTen, sv.MaSV, sv.Lop
        FROM phan_thuoc_nhom p
        JOIN danh_sach_sinh_vien sv ON sv.id = p.sinh_vien_id
        WHERE p.nhom_id = ?
        ORDER BY sv.HoTen
    ");
    $st->execute([$nhom_id]);
    $svs = $st->fetchAll();

    /* ===== LOAD TEMPLATE ===== */
    $template = new TemplateProcessor(
        'Form_NhiemvuLVTN.docx'
    );

    /* ===== SINH VIÊN ===== */
    for ($i = 0; $i < 2; $i++) {
        $template->setValue('HOTENSV'.($i+1), $svs[$i]['HoTen'] ?? '');
        $template->setValue('MSSV'.($i+1),   $svs[$i]['MaSV'] ?? '');
        $template->setValue('LOPSV'.($i+1),  $svs[$i]['Lop'] ?? '');
    }

    /* ===== NỘI DUNG ===== */
    $template->setValue('TIEUDE', $tieu_de);
    $template->setValue('TAILIEU', $tai_lieu);
    $template->setValue('NGAYGIAO', date('d/m/Y', strtotime($ngay_giao)));
    $template->setValue('DEADLINE', date('d/m/Y', strtotime($deadline)));
    $template->setValue('GVHD', $tenGV);

    $noiDung = '';
    foreach ($tasks as $t) {
        $noiDung .= "- ".$t."\n";
    }
  $template->setValue('NOIDUNG', $noiDung);


    /* ===== LƯU FILE ===== */
    $saveDir = __DIR__ . '/uploads/nhiemvu/';
    if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);

    $fileName = 'NhiemVu_Nhom_'.$nhom_id.'_'.date('Ymd_His').'.docx';
    $template->saveAs($saveDir.$fileName);

    /* ===== DOWNLOAD ===== */
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
    readfile($saveDir.$fileName);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Phân công nhiệm vụ LVTN</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-900 min-h-screen py-10">
<div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow">

<h1 class="text-3xl font-bold text-center mb-6">PHÂN CÔNG NHIỆM VỤ LVTN</h1>

<form method="POST">

<select name="nhom_id" id="nhomSelect" class="w-full mb-4 p-3 border rounded">
    <option value="">-- Chọn nhóm --</option>
</select>

<div id="svList" class="mb-4 text-sm text-gray-600"></div>

<input name="tieu_de" class="w-full mb-3 p-3 border rounded" placeholder="Tiêu đề đồ án">
<input name="tai_lieu" class="w-full mb-3 p-3 border rounded" value="document">

<div class="grid grid-cols-2 gap-4 mb-4">
    <input type="date" name="ngay_giao" class="p-3 border rounded">
    <input type="date" name="deadline" class="p-3 border rounded">
</div>

<div id="tasks">
<textarea name="tasks[]" class="w-full mb-2 p-3 border rounded" placeholder="Nhiệm vụ"></textarea>
</div>

<button type="button" onclick="addTask()" class="mb-4 bg-green-600 text-white px-4 py-2 rounded">+ Thêm nhiệm vụ</button>

<button class="w-full bg-blue-700 text-white py-3 rounded font-bold">
    TẢI FILE WORD
</button>

</form>
</div>

<script>
fetch('?action=get_nhom')
.then(r=>r.json())
.then(d=>{
    const s=document.getElementById('nhomSelect');
    d.forEach(n=>s.add(new Option(n.ten_nhom,n.id)));
});

document.getElementById('nhomSelect').onchange=e=>{
    fetch('?action=get_sv_nhom&nhom_id='+e.target.value)
    .then(r=>r.json())
    .then(d=>{
        document.getElementById('svList').innerHTML =
            d.map(s=>`${s.HoTen} - ${s.MaSV} (${s.Lop})`).join('<br>');
    });
};

function addTask(){
    const t=document.createElement('textarea');
    t.name='tasks[]';
    t.className='w-full mb-2 p-3 border rounded';
    document.getElementById('tasks').appendChild(t);
}
</script>
</body>
</html>

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

/* ================== LẤY TÊN GIẢNG VIÊN ================== */
$stmt = $pdo->prepare("SELECT HoTen FROM giang_vien WHERE MaGV=?");
$stmt->execute([$MaGV]);
$gv = $stmt->fetch();
$tenGV = $gv['HoTen'] ?? '';

/* ================== API 1: LẤY DANH SÁCH NHÓM ================== */
if (isset($_GET['action']) && $_GET['action'] === 'get_nhom') {
    header("Content-Type: application/json; charset=utf-8");
    $st = $pdo->prepare("SELECT id, ten_nhom FROM nhom WHERE giang_vien_huong_dan_id=?");
    $st->execute([$MaGV]);
    echo json_encode($st->fetchAll());
    exit;
}

/* ================== API 2: LẤY SV (KHÔNG CẦN LẤY ĐỀ TÀI NỮA) ================== */
if (isset($_GET['action']) && $_GET['action'] === 'get_sv_nhom') {
    $nhom_id = (int)($_GET['nhom_id'] ?? 0);
    header("Content-Type: application/json; charset=utf-8");

    // Chỉ lấy thông tin sinh viên
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

/* ================== XỬ LÝ POST: XUẤT WORD ================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nhom_id   = (int)($_POST['nhom_id'] ?? 0);
    
    // 🔥 QUAN TRỌNG: Lấy đề tài từ ô nhập liệu (Tự nhập)
    $tieu_de   = trim($_POST['tieu_de'] ?? '');

    $tai_lieu  = trim($_POST['tai_lieu'] ?? '');
    $ngay_giao = $_POST['ngay_giao'] ?: date('Y-m-d');
    $deadline  = $_POST['deadline'] ?: date('Y-m-d');
    $tasks     = array_filter($_POST['tasks'] ?? []);

    if (!$nhom_id) die('Chưa chọn nhóm');

    /* --- LẤY SV TỪ DB --- */
    $st = $pdo->prepare("
        SELECT sv.HoTen, sv.MaSV, sv.Lop
        FROM phan_thuoc_nhom p
        JOIN danh_sach_sinh_vien sv ON sv.id = p.sinh_vien_id
        WHERE p.nhom_id = ?
        ORDER BY sv.HoTen
    ");
    $st->execute([$nhom_id]);
    $svs = $st->fetchAll();

    if (count($svs) == 0) die('Nhóm chưa có sinh viên');

    /* --- LOAD TEMPLATE --- */
    $template = new TemplateProcessor('Form_NhiemvuLVTN.docx');

    /* --- ĐIỀN DỮ LIỆU --- */
    
    // 1. Đề tài (Lấy từ biến $tieu_de do người dùng nhập)
    $template->setValue('TIEUDE', $tieu_de);

    // 2. Ngành (Mặc định)
    $template->setValue('NGANH', "Công Nghệ Thông Tin");

    // 3. Thông tin sinh viên
    for ($i = 0; $i < 2; $i++) {
        $template->setValue('HOTENSV'.($i+1), $svs[$i]['HoTen'] ?? '');
        $template->setValue('MSSV'.($i+1),    $svs[$i]['MaSV'] ?? '');
        $template->setValue('LOPSV'.($i+1),   $svs[$i]['Lop'] ?? '');
    }

    // 4. Các thông tin khác
    $template->setValue('TAILIEU', $tai_lieu);
    $template->setValue('NGAYGIAO', date('d/m/Y', strtotime($ngay_giao)));
    $template->setValue('DEADLINE', date('d/m/Y', strtotime($deadline)));
    $template->setValue('GVHD', $tenGV);

    // Xử lý xuống dòng nhiệm vụ
    $noiDung = '';
    foreach ($tasks as $t) {
        $noiDung .= "- ".$t."\n";
    }
    $template->setValue('NOIDUNG', str_replace("\n", "<w:br/>", $noiDung));

    /* --- LƯU VÀ TẢI VỀ --- */
    $saveDir = __DIR__ . '/uploads/nhiemvu/';
    if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);

    $fileName = 'NhiemVu_Nhom_'.$nhom_id.'_'.date('Ymd_His').'.docx';
    $template->saveAs($saveDir.$fileName);

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
<body class="bg-gray-100 min-h-screen py-10 font-sans">
<div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-lg">

    <h1 class="text-3xl font-bold text-center mb-6 text-blue-800 uppercase">Phân công nhiệm vụ LVTN</h1>

    <form method="POST">

        <label class="font-bold text-gray-700 block mb-1">Chọn nhóm hướng dẫn:</label>
        <select name="nhom_id" id="nhomSelect" class="w-full mb-4 p-3 border rounded border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">-- Chọn nhóm --</option>
        </select>

        <div id="infoBox" class="bg-blue-50 p-4 rounded-lg mb-6 border border-blue-100 hidden">
            <div>
                <span class="font-bold text-blue-800">👥 Sinh viên thực hiện:</span>
                <div id="svList" class="ml-1 mt-2 text-sm text-gray-700 font-medium space-y-1"></div>
            </div>
        </div>

        <label class="font-bold text-gray-700 block mb-1">Tên đề tài:</label>
        <input name="tieu_de" class="w-full mb-4 p-3 border rounded focus:outline-none focus:border-blue-500 font-bold" placeholder="Nhập tên đề tài tại đây..." required>

        <label class="font-bold text-gray-700 block mb-1">Tài liệu tham khảo:</label>
        <input name="tai_lieu" class="w-full mb-4 p-3 border rounded focus:outline-none focus:border-blue-500" value="Các tài liệu chuyên ngành liên quan">

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="font-bold text-gray-700 block mb-1">Ngày giao:</label>
                <input type="date" name="ngay_giao" class="w-full p-3 border rounded" value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label class="font-bold text-gray-700 block mb-1">Hạn nộp báo cáo:</label>
                <input type="date" name="deadline" class="w-full p-3 border rounded">
            </div>
        </div>

        <label class="font-bold text-gray-700 block mb-1">Nội dung nhiệm vụ:</label>
        <div id="tasks">
            <textarea name="tasks[]" class="w-full mb-3 p-3 border rounded h-24 focus:outline-none focus:border-blue-500" placeholder="- Tìm hiểu công nghệ...&#10;- Phân tích yêu cầu hệ thống..."></textarea>
        </div>

        <button type="button" onclick="addTask()" class="mb-6 text-sm bg-green-100 text-green-700 px-4 py-2 rounded-full hover:bg-green-200 font-bold transition flex items-center gap-2">
            <span>+</span> Thêm dòng nhiệm vụ
        </button>

        <button class="w-full bg-blue-700 hover:bg-blue-800 text-white py-3 rounded-lg font-bold shadow-lg transition duration-200 flex justify-center items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            XUẤT PHIẾU GIAO NHIỆM VỤ (WORD)
        </button>

    </form>
</div>

<script>
// 1. Load danh sách nhóm
fetch('?action=get_nhom')
.then(r=>r.json())
.then(d=>{
    const s=document.getElementById('nhomSelect');
    d.forEach(n=>s.add(new Option(n.ten_nhom,n.id)));
});

// 2. Xử lý khi chọn nhóm -> Chỉ hiện SV (Đề tài tự nhập)
document.getElementById('nhomSelect').onchange = function(e) {
    const nhomId = e.target.value;
    const box = document.getElementById('infoBox');
    const svDiv = document.getElementById('svList');

    if(!nhomId) {
        box.classList.add('hidden');
        svDiv.innerHTML = '';
        return;
    }

    fetch('?action=get_sv_nhom&nhom_id=' + nhomId)
    .then(r => r.json())
    .then(d => {
        box.classList.remove('hidden');
        
        if(d.length > 0){
            // Hiển thị danh sách SV
            svDiv.innerHTML = d.map(s => 
                `<div class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                    ${s.HoTen} - <span class="text-gray-500">${s.MaSV}</span> 
                    <span class="bg-gray-100 px-2 py-0.5 rounded text-xs text-gray-600">${s.Lop}</span>
                </div>`
            ).join('');
        } else {
            svDiv.innerHTML = '<span class="text-red-500 italic">Nhóm này chưa có sinh viên!</span>';
        }
    })
    .catch(err => {
        console.error(err);
        svDiv.innerHTML = '<span class="text-red-500">Lỗi kết nối!</span>';
    });
};

function addTask(){
    const t = document.createElement('textarea');
    t.name = 'tasks[]';
    t.className = 'w-full mb-3 p-3 border rounded h-20 focus:outline-none focus:border-blue-500';
    t.placeholder = 'Nhiệm vụ tiếp theo...';
    document.getElementById('tasks').appendChild(t);
}
</script>
</body>
</html>
<?php
session_start();
require_once 'config.php';

// === KIỂM TRA QUYỀN GIẢNG VIÊN ===
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'giangvien') {
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}

$MaGV = $_SESSION['MaGV'] ?? null;
if (!$MaGV) {
    die("Lỗi: Không tìm thấy thông tin giảng viên.");
}

// ======================
// API: LẤY DANH SÁCH NHÓM CỦA CHÍNH GIẢNG VIÊN NÀY
// ======================
if (isset($_GET['action']) && $_GET['action'] === 'get_nhom') {
    header("Content-Type: application/json; charset=utf-8");

    $stmt = $pdo->prepare("
        SELECT DISTINCT n.id AS MaNhom, n.ten_nhom AS TenNhom
        FROM nhom n
        WHERE n.giang_vien_huong_dan_id = ?
        ORDER BY n.ten_nhom
    ");
    $stmt->execute([$MaGV]);

    $result = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $result[] = [
            "MaNhom"  => $row['MaNhom'],
            "TenNhom"   => $row['MaNhom'],           // giữ lại cho tương thích cũ
            "TenNhom" => $row['TenNhom'] ?: "Nhóm " . $row['MaNhom']
        ];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

// ======================
// API: LẤY SINH VIÊN THEO NHÓM (chỉ nhóm của GV này)
// ======================
if (isset($_GET['action']) && $_GET['action'] === 'get_sv_nhom') {
    $nhom_id = $_GET['nhom_id'] ?? '';

    header("Content-Type: application/json; charset=utf-8");

    if (!$nhom_id || !is_numeric($nhom_id)) {
        echo json_encode([]);
        exit;
    }

    // Kiểm tra nhóm có thuộc về giảng viên này không (bảo mật)
    $check = $pdo->prepare("SELECT 1 FROM nhom WHERE id = ? AND giang_vien_huong_dan_id = ?");
    $check->execute([$nhom_id, $MaGV]);
    if (!$check->fetch()) {
        echo json_encode([]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT sv.MaSV AS mssv, sv.HoTen AS hoten
        FROM phan_thuoc_nhom ptn
        JOIN danh_sach_sinh_vien sv ON ptn.sinh_vien_id = sv.id
        WHERE ptn.nhom_id = ?
        ORDER BY sv.HoTen
    ");
    $stmt->execute([$nhom_id]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    exit;
}

// ======================
// LƯU ĐIỂM CHO CẢ NHÓM
// ======================
if (($_POST['action'] ?? '') === 'save') {
    header("Content-Type: application/json; charset=utf-8");

    $nhom_id  = (int)($_POST['nhom_id'] ?? 0);
    $diemhd   = floatval($_POST['diemhd'] ?? 0);
    $diemnd   = floatval($_POST['diemnoidung'] ?? 0);
    $diemcc   = floatval($_POST['diemchuyencan'] ?? 0);
    $diemtb   = floatval($_POST['diemtrinhbay'] ?? 0);
    $nhanxet  = trim($_POST['nhanxet'] ?? '');
    $ngaynhap = $_POST['ngaynhap'] ?? date('Y-m-d');

    if ($nhom_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng chọn nhóm!']);
        exit;
    }

    // Kiểm tra quyền: nhóm này có phải do GV này hướng dẫn không?
    $check = $pdo->prepare("SELECT 1 FROM nhom WHERE id = ? AND giang_vien_huong_dan_id = ?");
    $check->execute([$nhom_id, $MaGV]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Bạn không hướng dẫn nhóm này!']);
        exit;
    }

    // Kiểm tra điểm
    if ($diemhd < 0 || $diemhd > 10 || $diemnd < 0 || $diemnd > 10 ||
        $diemcc < 0 || $diemcc > 10 || $diemtb < 0 || $diemtb > 10) {
        echo json_encode(['success' => false, 'message' => 'Điểm phải từ 0 đến 10']);
        exit;
    }

    $diemLan1 = round(($diemcc * 0.2) + ($diemnd * 0.4) + ($diemtb * 0.2) + ($diemhd * 0.2), 2);

    // Lấy danh sách SV trong nhóm
    $stmt = $pdo->prepare("
        SELECT sv.MaSV 
        FROM phan_thuoc_nhom ptn
        JOIN danh_sach_sinh_vien sv ON ptn.sinh_vien_id = sv.id
        WHERE ptn.nhom_id = ?
    ");
    $stmt->execute([$nhom_id]);
    $sv_list = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$sv_list) {
        echo json_encode(['success' => false, 'message' => 'Nhóm không có sinh viên!']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        foreach ($sv_list as $mssv) {
            // Lưu điểm GVHD
            $stmt1 = $pdo->prepare("
                REPLACE INTO qlsv_diem_gvhd 
                (MaSV, DiemHD, NhanXet, NgayNhap, DiemNoiDung, DiemChuyenCan, DiemTrinhBay) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt1->execute([$mssv, $diemhd, $nhanxet, $ngaynhap, $diemnd, $diemcc, $diemtb]);

            // Cập nhật điểm tổng
            $stmt2 = $pdo->prepare("
                INSERT INTO qlsv_diem (MaSV, MaMH, NamHoc, HocKy, DiemLan1) 
                VALUES (?, 'LVTN', YEAR(CURDATE()), 2, ?)
                ON DUPLICATE KEY UPDATE DiemLan1 = VALUES(DiemLan1)
            ");
            $stmt2->execute([$mssv, $diemLan1]);
        }

        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Đã lưu điểm cho toàn bộ nhóm!',
            'soluong' => count($sv_list),
            'diemLan1' => $diemLan1
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Nhập điểm GVHD - Chỉ nhóm mình hướng dẫn</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .glass { backdrop-filter: blur(12px); background: rgba(255,255,255,0.95); border: 1px solid rgba(255,255,255,0.3); }
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-100">

<div class="container max-w-5xl mx-auto p-6">

    <div class="text-center mb-8 mt-10">
        <h1 class="text-4xl font-bold text-indigo-800">NHẬP ĐIỂM HƯỚNG DẪN</h1>
        <p class="text-lg text-gray-600 mt-2">Giảng viên: <strong><?= htmlspecialchars($_SESSION['HoTen'] ?? 'GV') ?></strong></p>
    </div>

    <div class="glass rounded-3xl shadow-2xl p-8">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- CỘT TRÁI -->
            <div class="space-y-6">
                <div>
                    <label class="block text-lg font-semibold text-gray-700 mb-3">Chọn nhóm bạn đang hướng dẫn <span class="text-red-500">*</span></label>
                    <select id="nhomSelect" class="w-full px-5 py-4 border-2 border-indigo-200 rounded-xl text-lg">
                        <option value="">-- Đang tải nhóm của bạn... --</option>
                    </select>
                </div>

                <div>
                    <label class="block text-lg font-semibold text-gray-700 mb-3">Sinh viên trong nhóm</label>
                    <div id="svList" class="border-2 border-gray-200 rounded-xl p-4 bg-white max-h-64 overflow-y-auto text-base">
                        <p class="text-gray-500 italic">-- Chọn nhóm để xem danh sách --</p>
                    </div>
                </div>

                <div>
                    <label class="block text-lg font-semibold text-gray-700 mb-3">Ngày nhập điểm</label>
                    <input type="date" id="ngaynhap" value="<?=date('Y-m-d')?>" 
                           class="w-full px-5 py-4 border-2 border-gray-200 rounded-xl">
                </div>
            </div>

            <!-- CỘT PHẢI - ĐIỂM -->
            <div class="space-y-6">
                <div><label class="block text-lg font-semibold text-gray-700 mb-2">Điểm chuyên cần (20%)</label>
                    <input type="number" step="0.1" min="0" max="10" id="diemCC" placeholder="0.0 - 10.0"
                           class="w-full px-5 py-4 border-2 border-green-200 rounded-xl focus:border-green-500">
                </div>
                <div><label class="block text-lg font-semibold text-gray-700 mb-2">Điểm nội dung (40%)</label>
                    <input type="number" step="0.1" min="0" max="10" id="diemND"
                           class="w-full px-5 py-4 border-2 border-blue-200 rounded-xl focus:border-blue-500">
                </div>
                <div><label class="block text-lg font-semibold text-gray-700 mb-2">Điểm trình bày (20%)</label>
                    <input type="number" step="0.1" min="0" max="10" id="diemTB"
                           class="w-full px-5 py-4 border-2 border-purple-200 rounded-xl focus:border-purple-500">
                </div>
                <div><label class="block text-lg font-semibold text-gray-700 mb-2">Điểm hướng dẫn (20%)</label>
                    <input type="number" step="0.1" min="0" max="10" id="diemHD"
                           class="w-full px-5 py-4 border-2 border-pink-200 rounded-xl focus:border-pink-500">
                </div>
            </div>
        </div>

        <div class="mt-8">
            <label class="block text-lg font-semibold text-gray-700 mb-3">Nhận xét của giảng viên</label>
            <textarea id="nhanxet" rows="6" placeholder="Ghi rõ ưu điểm, hạn chế..."
                      class="w-full px-5 py-4 border-2 border-gray-200 rounded-xl focus:border-indigo-500"></textarea>
        </div>

        <div class="flex justify-center gap-6 mt-10">
            <button onclick="luuDiem()" 
                    class="px-12 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-xl font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transform hover:scale-105 transition shadow-xl">
                LƯU ĐIỂM CHO CẢ NHÓM
            </button>
        </div>

        <div id="result" class="mt-8 text-center text-xl font-bold p-6 rounded-xl"></div>
    </div>
</div>

<script>
// Tải danh sách nhóm của GV hiện tại
fetch("?action=get_nhom")
    .then(r => r.json())
    .then(data => {
        const sel = document.getElementById("nhomSelect");
        sel.innerHTML = '<option value="">-- Chọn nhóm --</option>';
        if (data.length === 0) {
            sel.innerHTML += '<option value="">Bạn chưa hướng dẫn nhóm nào</option>';
            return;
        }
        data.forEach(g => {
            const opt = new Option(g.TenNhom, g.MaNhom);
            sel.add(opt);
        });
    });

// Khi chọn nhóm → load sinh viên
document.getElementById("nhomSelect").addEventListener("change", function() {
    const nhom_id = this.value;
    const box = document.getElementById("svList");

    if (!nhom_id) {
        box.innerHTML = '<p class="text-gray-500 italic">-- Chọn nhóm để xem --</p>';
        return;
    }

    fetch(`?action=get_sv_nhom&nhom_id=${nhom_id}`)
        .then(r => r.json())
        .then(data => {
            if (data.length === 0) {
                box.innerHTML = '<p class="text-red-500>Nhóm trống</p>';
                return;
            }
            box.innerHTML = data.map(s => 
                `<div class="p-2 border-b"><strong>${s.hoten}</strong> (${s.mssv})</div>`
            ).join('');
        });
});

function luuDiem() {
    const nhom_id = document.getElementById("nhomSelect").value;
    if (!nhom_id) return alert("Vui lòng chọn nhóm!");

    const fd = new FormData();
    fd.append("action", "save");
    fd.append("nhom_id", nhom_id);
    fd.append("diemchuyencan", document.getElementById("diemCC").value || 0);
    fd.append("diemnoidung", document.getElementById("diemND").value || 0);
    fd.append("diemtrinhbay", document.getElementById("diemTB").value || 0);
    fd.append("diemhd", document.getElementById("diemHD").value || 0);
    fd.append("nhanxet", document.getElementById("nhanxet").value);
    fd.append("ngaynhap", document.getElementById("ngaynhap").value);

    document.getElementById("result").innerHTML = '<p class="text-blue-600 text-xl">Đang lưu điểm...</p>';

    fetch("", {method:"POST", body:fd})
        .then(r => r.json())
        .then(d => {
            const el = document.getElementById("result");
            if (d.success) {
                el.className = "bg-green-100 text-green-800 p-8 rounded-xl text-xl";
                el.innerHTML = `<strong>THÀNH CÔNG!</strong><br>
                               Đã chấm điểm cho <strong>${d.soluong} sinh viên</strong> trong nhóm<br>
                               Điểm lần 1: <strong>${d.diemLan1}</strong>`;
            } else {
                el.className = "bg-red-100 text-red-800 p-8 rounded-xl text-xl";
                el.innerHTML = "Lỗi: " + d.message;
            }
        })
        .catch(() => {
            document.getElementById("result").innerHTML = '<p class="text-red-600">Lỗi kết nối!</p>';
        });
}
</script>
</body>
</html>
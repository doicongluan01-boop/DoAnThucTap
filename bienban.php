<?php
session_start();
require 'config.php';
//chặn admin truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'giangvien') {
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}
$pdo->exec("SET NAMES utf8mb4");

// =====================================
// Helper xuất JSON
// =====================================
function json_out($data){
    if (ob_get_level()) ob_end_clean();
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_REQUEST["action"] ?? null;
$role   = $_SESSION["role"] ?? "gv";
$MaGV   = $_SESSION["MaGV"] ?? null;

// =====================================
// API: lấy danh sách nhóm
// =====================================
if ($action === "get_groups") {

    if ($role === "admin") {
        $sql = "SELECT id, ten_nhom, giang_vien_huong_dan_id 
                FROM nhom ORDER BY ten_nhom";
        $groups = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sql = "SELECT id, ten_nhom, giang_vien_huong_dan_id
                FROM nhom
                WHERE giang_vien_huong_dan_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$MaGV]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    json_out($groups);
}

// =====================================
// API: lấy danh sách sinh viên theo nhóm
// =====================================
if ($action === "get_students") {
    $nhom_id = intval($_GET["nhom_id"] ?? 0);

    if ($nhom_id <= 0) json_out([]);

    // nếu là GV → kiểm tra quyền
    if ($role !== "admin") {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM nhom WHERE id = ? AND giang_vien_huong_dan_id = ?");
        $chk->execute([$nhom_id, $MaGV]);
        if ($chk->fetchColumn() == 0)
            json_out(["error" => "Bạn không có quyền xem nhóm này"]);
    }

    $sql = "SELECT ds.id AS sinh_id, ds.MaSV, ds.HoTen
            FROM phan_thuoc_nhom p
            JOIN danh_sach_sinh_vien ds ON ds.id = p.sinh_vien_id
            WHERE p.nhom_id = ?
            ORDER BY ds.HoTen";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nhom_id]);
    json_out($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// =====================================
// API: lấy danh sách biên bản đã nộp
// =====================================
if ($action === "list") {

    // Lấy nhóm theo quyền
    if ($role === "admin") {
        $sql_nhom = "SELECT id, ten_nhom 
                     FROM nhom 
                     ORDER BY ten_nhom";
        $stmt = $pdo->query($sql_nhom);
    } else {
        $sql_nhom = "SELECT id, ten_nhom 
                     FROM nhom
                     WHERE giang_vien_huong_dan_id = ?
                     ORDER BY ten_nhom";
        $stmt = $pdo->prepare($sql_nhom);
        $stmt->execute([$MaGV]);
    }

    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];

    foreach ($groups as $g) {

        // Lấy file mới nhất của nhóm
        $sql_file = "
            SELECT b.TenFile, b.GhiChu, b.NgayNop
            FROM qlsv_bienban b
            JOIN danh_sach_sinh_vien sv ON sv.MaSV = b.MaSV
            JOIN phan_thuoc_nhom p ON p.sinh_vien_id = sv.id
            WHERE p.nhom_id = ?
            ORDER BY b.NgayNop DESC
            LIMIT 1
        ";

        $stmt2 = $pdo->prepare($sql_file);
        $stmt2->execute([$g['id']]);
        $file = $stmt2->fetch(PDO::FETCH_ASSOC);

        // Lấy danh sách thành viên của nhóm
        $sql_sv = "
            SELECT sv.MaSV, sv.HoTen
            FROM phan_thuoc_nhom p
            JOIN danh_sach_sinh_vien sv ON sv.id = p.sinh_vien_id
            WHERE p.nhom_id = ?
            ORDER BY sv.HoTen
        ";

        $stmt3 = $pdo->prepare($sql_sv);
        $stmt3->execute([$g['id']]);
        $students = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        $result[] = [
            "nhom_id"   => $g['id'],
            "ten_nhom"  => $g['ten_nhom'],
            "file"      => $file['TenFile'] ?? null,
            "ghichu"    => $file['GhiChu'] ?? null,
            "ngaynop"   => $file['NgayNop'] ?? null,
            "sinhvien"  => $students
        ];
    }

    json_out($result);
}

// =====================================
// API: Upload theo nhóm
// =====================================
if ($action === "upload_group" && $_SERVER["REQUEST_METHOD"] === "POST") {

    $nhom_id = intval($_POST["nhom_id"] ?? 0);
    $ghichu  = $_POST["ghichu"] ?? "";
    $file    = $_FILES["file"] ?? null;

    if ($nhom_id <= 0 || !$file) {
        json_out(["success" => false, "message" => "Thiếu dữ liệu"]);
    }

    // Kiểm tra quyền GV
    if ($role !== "admin") {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM nhom WHERE id = ? AND giang_vien_huong_dan_id = ?");
        $chk->execute([$nhom_id, $MaGV]);
        if ($chk->fetchColumn() == 0)
            json_out(["success" => false, "message" => "Bạn không có quyền nộp cho nhóm này"]);
    }

    // Validate file
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($ext, ["pdf", "doc", "docx"])) {
        json_out(["success" => false, "message" => "Chỉ chấp nhận PDF, DOC, DOCX"]);
    }

    // Upload file
    $dir = __DIR__ . "/uploads_bienban/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $safeName = preg_replace("/[^a-zA-Z0-9._-]/", "_", $file["name"]);
    $newName  = time() . "_" . $safeName;

    if (!move_uploaded_file($file["tmp_name"], $dir . $newName)) {
        json_out(["success" => false, "message" => "Lỗi lưu file"]);
    }

    // Lấy danh sách sinh viên trong nhóm
    $sql = "SELECT ds.MaSV
            FROM phan_thuoc_nhom p
            JOIN danh_sach_sinh_vien ds ON ds.id = p.sinh_vien_id
            WHERE p.nhom_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nhom_id]);
    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Insert biên bản cho tất cả SV trong nhóm
    $ins = $pdo->prepare("INSERT INTO qlsv_bienban (MaSV, TenFile, GhiChu, NgayNop) 
                          VALUES (?, ?, ?, NOW())");

    foreach ($students as $mssv) {
        $ins->execute([$mssv, $newName, $ghichu]);
    }

    json_out(["success" => true, "message" => "Đã nộp biên bản cho toàn nhóm!"]);
}

// =====================================
// Nếu không có action → HIỂN THỊ GIAO DIỆN
// =====================================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Nộp biên bản theo nhóm</title>

<style>
body{font-family:Arial;background:#eef2ff;margin:0;padding:20px}
.container{max-width:900px;margin:auto;background:white;padding:20px;border-radius:10px;box-shadow:0 0 20px rgba(0,0,0,0.1)}
h2{text-align:center;color:#1f3c88}
select,input,textarea{width:100%;padding:10px;margin:5px 0 15px;border:1px solid #ccc;border-radius:6px}
button{padding:12px 20px;background:#1f3c88;color:white;border:0;border-radius:6px;cursor:pointer;font-weight:bold}
button:hover{background:#162d66}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{padding:10px;border-bottom:1px solid #ddd}
th{background:#1f3c88;color:white}
.no{color:#777;padding:15px;text-align:center}
</style>

</head>
<body>
<div class="container">

<h2>Nộp Biên Bản Theo Nhóm</h2>

<!-- Chọn nhóm -->
<label>Chọn nhóm</label>
<select id="selNhom"></select>

<!-- Danh sách sinh viên -->
<h3>Danh sách sinh viên</h3>
<div id="svArea"><div class='no'>Hãy chọn nhóm để xem sinh viên.</div></div>

<!-- Nộp biên bản -->
<h3>Nộp biên bản cho nhóm</h3>

<input type="file" id="fileBB" accept=".pdf,.doc,.docx">
<textarea id="ghiChu" placeholder="Ghi chú (tùy chọn)"></textarea>
<button onclick="uploadGroup()">Nộp biên bản</button>

<hr>

<!-- Danh sách biên bản -->
<h3>Biên bản đã nộp</h3>
<div id="listArea"></div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    loadGroups();
    loadList();
});

// ================= LOAD NHÓM =================
function loadGroups(){
    fetch("bienban.php?action=get_groups")
    .then(r=>r.json())
    .then(data=>{
        let sel = document.getElementById("selNhom");
        sel.innerHTML = "<option value=''>-- Chọn nhóm --</option>";
        data.forEach(g=>{
            sel.innerHTML += `<option value="${g.id}">${g.ten_nhom}</option>`;
        });
    });

    document.getElementById("selNhom").onchange = function(){
        loadStudents(this.value);
    };
}

// ================= LOAD SINH VIÊN =================
function loadStudents(id){
    if(!id){
        document.getElementById("svArea").innerHTML = "<div class='no'>Hãy chọn nhóm.</div>";
        return;
    }

    fetch("bienban.php?action=get_students&nhom_id=" + id)
    .then(r=>r.json())
    .then(data=>{
        if(!data.length){
            document.getElementById("svArea").innerHTML = "<div class='no'>Nhóm chưa có sinh viên.</div>";
            return;
        }

        let html = "<table><tr><th>MSSV</th><th>Họ tên</th></tr>";
        data.forEach(s=>{
            html += `<tr>
                        <td>${s.MaSV}</td>
                        <td>${s.HoTen}</td>
                     </tr>`;
        });
        html += "</table>";

        document.getElementById("svArea").innerHTML = html;
    });
}

// ================= UPLOAD NHÓM =================
function uploadGroup(){
    let nhom = document.getElementById("selNhom").value;
    let file = document.getElementById("fileBB").files[0];
    let ghi  = document.getElementById("ghiChu").value;

    if(!nhom){ alert("Chọn nhóm!"); return; }
    if(!file){ alert("Chọn file!"); return; }

    let fd = new FormData();
    fd.append("action", "upload_group");
    fd.append("nhom_id", nhom);
    fd.append("ghichu", ghi);
    fd.append("file", file);

    fetch("bienban.php?action=upload_group", {
        method: "POST",
        body: fd
    })
    .then(r=>r.json())
    .then(res=>{
        alert(res.message);
        if(res.success){
            document.getElementById("fileBB").value = "";
            document.getElementById("ghiChu").value = "";
            loadList();
        }
    });
}

// ================= DANH SÁCH BIÊN BẢN =================
function loadList(){
    fetch("bienban.php?action=list")
    .then(r=>r.json())
    .then(data=>{
        if(!data.length){
            document.getElementById("listArea").innerHTML = 
                "<div class='no'>Chưa có biên bản nào.</div>";
            return;
        }

        let html = "";

        data.forEach(grp => {

            html += `
                <div style="border:1px solid #ddd;
                            padding:15px;
                            margin-bottom:15px;
                            border-radius:8px;
                            background:#f8f9ff">

                    <h3 style="margin:0; color:#1f3c88">
                        Nhóm: ${grp.ten_nhom}
                    </h3>

                    <p><b>File:</b> 
                        ${grp.file 
                            ? `<a href="uploads_bienban/${grp.file}" target="_blank">Tải xuống</a>` 
                            : "<i>Chưa nộp</i>"
                        }
                    </p>

                    <p><b>Ghi chú:</b> ${grp.ghichu ?? "<i>Không có</i>"}</p>
                    <p><b>Ngày nộp:</b> ${grp.ngaynop ?? "<i>Chưa có</i>"}</p>

                    <b>Danh sách sinh viên:</b>
                    <ul style="margin-top:8px;">
            `;

            grp.sinhvien.forEach(s=>{
                html += `<li>${s.MaSV} – ${s.HoTen}</li>`;
            });

            html += `
                    </ul>
                </div>
            `;
        });

        document.getElementById("listArea").innerHTML = html;
    });
}

</script>

</body>
</html>

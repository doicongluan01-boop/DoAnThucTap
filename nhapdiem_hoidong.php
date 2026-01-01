<?php 
session_start();
require 'config.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}

// ====================== API 1: LẤY DANH SÁCH NHÓM + TRẠNG THÁI ĐIỂM ======================
if (isset($_GET['action']) && $_GET['action'] === 'get_nhom') {
    header('Content-Type: application/json; charset=utf-8');
    
    // SQL Nâng cao: Lấy thêm điểm của 1 sinh viên bất kỳ trong nhóm để check xem nhóm đã chấm chưa
    $sql = "SELECT n.id, n.ten_nhom, gv.HoTen as gvhd_ten,
                   (SELECT d.DiemBaoVe 
                    FROM phan_thuoc_nhom ptn2 
                    JOIN danh_sach_sinh_vien sv2 ON ptn2.sinh_vien_id = sv2.id 
                    JOIN qlsv_diem_hoidong d ON sv2.MaSV = d.MaSV 
                    WHERE ptn2.nhom_id = n.id 
                    LIMIT 1) as diem_da_cham
            FROM nhom n
            JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
            LEFT JOIN giang_vien gv ON n.giang_vien_huong_dan_id = gv.MaGV
            GROUP BY n.id
            ORDER BY n.id DESC";
            
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = [];
    foreach ($rows as $r) {
        $ten_gv = !empty($r['gvhd_ten']) ? $r['gvhd_ten'] : "Chưa phân GV";
        
        // Xử lý hiển thị trạng thái chấm
        $status_text = "";
        $is_graded = false;
        
        if ($r['diem_da_cham'] !== null) {
            $is_graded = true;
            $status_text = " ✅ Đã chấm (" . floatval($r['diem_da_cham']) . ")";
        }

        $display_name = $r['ten_nhom'] . " (ID: " . $r['id'] . ") - GV " . $ten_gv . $status_text;
        
        $result[] = [
            'nhom_id' => $r['id'],
            'ten' => $display_name,
            'diem_cu' => $r['diem_da_cham'] // Trả về điểm cũ để JS xử lý
        ];
    }
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

// ====================== API 2: LẤY SV TRONG NHÓM + ĐIỂM CHI TIẾT ======================
if (isset($_GET['action']) && $_GET['action'] === 'get_sv_nhom') {
    $nhom_id = $_GET['nhom_id'] ?? 0;
    header('Content-Type: application/json; charset=utf-8');
    
    $stmt = $pdo->prepare("
        SELECT sv.MaSV, sv.HoTen, sv.Lop, 
               gv.HoTen as gvhd_chuan,
               d.DiemBaoVe as diem_hien_tai
        FROM danh_sach_sinh_vien sv
        JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id
        JOIN nhom n ON ptn.nhom_id = n.id
        LEFT JOIN giang_vien gv ON n.giang_vien_huong_dan_id = gv.MaGV
        LEFT JOIN qlsv_diem_hoidong d ON sv.MaSV = d.MaSV
        WHERE ptn.nhom_id = ? 
        ORDER BY sv.HoTen
    ");
    $stmt->execute([$nhom_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    exit;
}

// ====================== API 3: LƯU ĐIỂM (GIỮ NGUYÊN) ======================
if (isset($_POST['action']) && $_POST['action'] === 'save_group') {
    header('Content-Type: application/json; charset=utf-8');
    
    $nhom_id = (int)$_POST['nhom_id'];
    $diem_baove = round(floatval($_POST['diem_baove']), 2);
    $nhanxet = trim($_POST['nhanxet'] ?? '');

    if ($nhom_id <= 0 || $diem_baove < 0 || $diem_baove > 10) {
        echo json_encode(['success' => false, 'msg' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT sv.MaSV 
            FROM danh_sach_sinh_vien sv
            JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id
            WHERE ptn.nhom_id = ?
        ");
        $stmt->execute([$nhom_id]);
        $sv_list = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($sv_list)) {
            echo json_encode(['success' => false, 'msg' => 'Nhóm này không có sinh viên nào!']);
            exit;
        }

        foreach ($sv_list as $mssv) {
            $pdo->prepare("REPLACE INTO qlsv_diem_hoidong (MaSV, DiemBaoVe, NhanXet) VALUES (?, ?, ?)")
                ->execute([$mssv, $diem_baove, $nhanxet]);

            $upd = $pdo->prepare("UPDATE qlsv_diem SET DiemLan2 = ? WHERE MaSV = ? AND MaMH = 'LVTN'");
            $upd->execute([$diem_baove, $mssv]);
            
            if ($upd->rowCount() === 0) {
                $pdo->prepare("INSERT INTO qlsv_diem (MaSV, MaMH, DiemLan2) VALUES (?, 'LVTN', ?)")
                    ->execute([$mssv, $diem_baove]);
            }
        }

        $pdo->commit();
        echo json_encode([
            'success' => true, 
            'msg' => "Đã lưu điểm ($diem_baove) cho nhóm ID: $nhom_id", 
            'diem' => $diem_baove
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'msg' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chấm điểm bảo vệ LVTN</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; padding: 30px 0; }
    .card { border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.4); }
    .diem-input { width: 120px; font-size: 1.8rem; text-align: center; font-weight: bold; }
    select option { padding: 10px; font-size: 1.1rem; }
  </style>
</head>
<body>
<div class="container">
  <div class="card">
    <div class="card-header bg-danger text-white text-center py-4">
      <h2 class="mb-0">CHẤM ĐIỂM HỘI ĐỒNG (THEO NHÓM GVHD)</h2>
      <p class="mb-0 mt-2 text-white-50">Tự động phát hiện nhóm đã chấm điểm</p>
    </div>

    <div class="card-body bg-light">
      <div class="row g-4">
        <div class="col-md-5">
          <label class="form-label fw-bold text-primary">Chọn Nhóm / Giảng viên</label>
          <select id="nhomSelect" class="form-select form-select-lg shadow-sm border-primary">
            <option value="">-- Đang tải dữ liệu... --</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-bold">Điểm (0-10)</label>
          <input type="number" step="0.1" min="0" max="10" id="diemInput" class="form-control form-control-lg diem-input shadow-sm" placeholder="8.5">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-bold">&nbsp;</label>
          <button onclick="luuDiemNhom()" class="btn btn-success btn-lg w-100 shadow fw-bold">
            <i class="bi bi-check-circle-fill"></i> LƯU ĐIỂM
          </button>
        </div>
      </div>

      <div class="mt-4">
        <label class="form-label fw-bold">Nhận xét chung</label>
        <textarea id="nhanxet" class="form-control shadow-sm" rows="2" placeholder="Nhập nhận xét của hội đồng..."></textarea>
      </div>

      <hr class="my-5">
      
      <div id="thongbao-cham" style="display: none;"></div>

      <h4 class="text-secondary"><i class="bi bi-people-fill"></i> Danh sách sinh viên trong nhóm</h4>
      <div id="danhsach" class="table-responsive bg-white rounded p-3 shadow-sm border">
        <div class="text-center text-muted py-4">
            <i class="bi bi-arrow-up-circle fs-1"></i><br>
            Vui lòng chọn nhóm GVHD ở trên
        </div>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<script>
// Biến toàn cục lưu danh sách nhóm để tra cứu lại điểm
let groupsData = [];

async function loadNhom() {
    try {
        const res = await fetch('?action=get_nhom');
        groupsData = await res.json();
        const sel = document.getElementById('nhomSelect');
        
        sel.innerHTML = '<option value="">-- Chọn Nhóm GVHD --</option>';

        if (groupsData.length === 0) {
            sel.innerHTML = '<option value="">Chưa có nhóm nào</option>';
            return;
        }

        groupsData.forEach(g => {
            const opt = new Option(g.ten, g.nhom_id);
            // Lưu điểm cũ vào thuộc tính data của option để dùng sau
            if (g.diem_cu) opt.dataset.diem = g.diem_cu;
            sel.add(opt);
        });
    } catch (e) {
        console.error(e);
        document.getElementById('nhomSelect').innerHTML = '<option>Lỗi kết nối</option>';
    }
}

document.getElementById('nhomSelect').addEventListener('change', async function() {
    const nhom_id = this.value;
    const box = document.getElementById('danhsach');
    const selectedOption = this.options[this.selectedIndex];
    const selectedText = selectedOption.text;
    const diemInput = document.getElementById('diemInput');
    const thongBaoBox = document.getElementById('thongbao-cham');

    // Reset giao diện
    thongBaoBox.style.display = 'none';
    diemInput.value = '';

    if (!nhom_id) {
        box.innerHTML = '<div class="text-center text-muted py-4">← Chọn nhóm để xem</div>';
        return;
    }

    // 1. KIỂM TRA XEM NHÓM NÀY ĐÃ CHẤM CHƯA
    // Lấy điểm cũ từ thuộc tính data-diem đã lưu lúc load
    const diemCu = selectedOption.dataset.diem;
    if (diemCu) {
        diemInput.value = diemCu; // Tự động điền điểm
        thongBaoBox.style.display = 'block';
        thongBaoBox.innerHTML = `
            <div class="alert alert-warning border-start border-5 border-warning shadow-sm">
                <h5 class="fw-bold text-warning"><i class="bi bi-check-circle-fill"></i> NHÓM NÀY ĐÃ ĐƯỢC CHẤM ĐIỂM!</h5>
                <div>Điểm hiện tại: <strong>${diemCu}</strong>. Nếu bạn nhập điểm mới và Lưu, điểm cũ sẽ bị ghi đè.</div>
            </div>
        `;
    }

    // 2. TẢI DANH SÁCH SINH VIÊN
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Đang tải...</p></div>';

    try {
        const res = await fetch(`?action=get_sv_nhom&nhom_id=${nhom_id}`);
        const sv = await res.json();

        let html = `
            <div class="alert alert-info d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>Đang chấm cho: <strong>${selectedText}</strong></div>
            </div>
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th width="60">STT</th>
                        <th width="150">MSSV</th>
                        <th>Họ và Tên</th>
                        <th>GVHD</th>
                        <th>Điểm hiện có</th>
                    </tr>
                </thead>
                <tbody>`;
                
        if (sv.length === 0) {
            html += `<tr><td colspan="5" class="text-center py-4 text-muted">Nhóm trống</td></tr>`;
        } else {
            sv.forEach((s, i) => {
                // Hiển thị điểm từng người nếu có
                let diemBadge = s.diem_hien_tai 
                    ? `<span class="badge bg-success fs-6">${s.diem_hien_tai}</span>` 
                    : `<span class="badge bg-secondary">Chưa có</span>`;

                html += `
                    <tr>
                        <td class="text-center">${i+1}</td>
                        <td class="text-center fw-bold text-primary">${s.MaSV}</td>
                        <td class="fw-bold">${s.HoTen}</td>
                        <td class="text-success"><i class="bi bi-person-badge"></i> ${s.gvhd_chuan || 'Chưa rõ'}</td>
                        <td class="text-center">${diemBadge}</td>
                    </tr>`;
            });
        }
        html += `</tbody></table>`;
        box.innerHTML = html;
        
    } catch (err) {
        box.innerHTML = '<div class="alert alert-danger">Lỗi tải dữ liệu!</div>';
    }
});

function luuDiemNhom() {
    const nhom_id = document.getElementById('nhomSelect').value;
    const diem = document.getElementById('diemInput').value;
    const nhanxet = document.getElementById('nhanxet').value;
    const selectedText = document.getElementById('nhomSelect').options[document.getElementById('nhomSelect').selectedIndex].text;

    if (!nhom_id) return Swal.fire('Chưa chọn nhóm', 'Vui lòng chọn nhóm GVHD trước!', 'warning');
    if (diem === '' || diem < 0 || diem > 10) return Swal.fire('Điểm không hợp lệ', 'Điểm phải từ 0 đến 10', 'error');

    Swal.fire({
        title: 'Xác nhận lưu điểm',
        html: `Chấm <b>${diem} điểm</b> cho nhóm:<br/><br/>
               <span class="text-primary fw-bold fs-5">${selectedText}</span><br><br>
               <span class="text-danger small">Lưu ý: Điểm này sẽ áp dụng cho tất cả thành viên nhóm.</span>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Đồng ý Lưu',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.showLoading();
            
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=save_group&nhom_id=${nhom_id}&diem_baove=${diem}&nhanxet=${encodeURIComponent(nhanxet)}`
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    Swal.fire('Thành công!', d.msg, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Thất bại', d.msg, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Lỗi', 'Không thể kết nối đến server.', 'error');
            });
        }
    });
}

loadNhom();
</script>
</body>
</html>
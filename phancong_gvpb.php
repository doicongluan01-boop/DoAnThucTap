<?php require 'config.php'; 
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phân công GVPB theo Nhóm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Sidebar */
        .sticky-sidebar { position: -webkit-sticky; position: sticky; top: 20px; height: calc(100vh - 40px); overflow-y: auto; padding-right: 5px; }
        .sticky-sidebar::-webkit-scrollbar { width: 6px; }
        .sticky-sidebar::-webkit-scrollbar-thumb { background-color: #cbd5e0; border-radius: 4px; }

        .gv-item { background: white; padding: 12px 15px; margin-bottom: 10px; border-radius: 8px; cursor: grab; border-left: 4px solid #0d6efd; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; display: flex; justify-content: space-between; align-items: center; }
        .gv-item:hover { transform: translateX(5px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); background: #f8f9fa; }
        
        /* Group Card Styles */
        .group-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; border: 1px solid #e1e4e8; transition: 0.3s; position: relative; overflow: hidden; }
        .group-card:hover { box-shadow: 0 8px 16px rgba(0,0,0,0.1); border-color: #0d6efd; }
        .group-header { background: #f8f9fa; padding: 10px 15px; border-bottom: 1px solid #e1e4e8; font-weight: bold; color: #2c3e50; display: flex; justify-content: space-between; align-items: center; }
        
        .member-list { list-style: none; padding: 0; margin: 10px 15px; font-size: 0.9em; }
        .member-list li { margin-bottom: 5px; padding-bottom: 5px; border-bottom: 1px dashed #eee; display: flex; justify-content: space-between; }
        
        .drop-zone { border: 2px dashed #cbd5e0; border-radius: 8px; margin: 15px; padding: 15px; text-align: center; background: #f8f9fa; transition: 0.3s; min-height: 60px; display: flex; align-items: center; justify-content: center; flex-direction: column; }
        .drop-zone.dragover { background: #e7f1ff; border-color: #0d6efd; transform: scale(1.02); }
        .drop-zone.has-gv { border: 2px solid #198754; background: #d1e7dd; border-style: solid; }

        .council-header { background: linear-gradient(45deg, #2c3e50, #3498db); color: white; padding: 10px 20px; border-radius: 8px; margin: 30px 0 20px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        #toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 9999; }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-lg-3 col-md-4">
            <div class="sticky-sidebar">
                <div class="bg-white p-3 rounded shadow-sm mb-3">
                    <h5 class="fw-bold text-primary mb-3"><i class="fas fa-chalkboard-teacher"></i> Giảng viên</h5>
                    <input type="text" id="searchGV" class="form-control mb-3" placeholder="Tìm tên giảng viên...">
                    
                    <div class="d-grid gap-2">
                        <button onclick="autoAssignGroup()" class="btn btn-warning fw-bold shadow-sm">
                            <i class="fas fa-magic"></i> Phân Tự Động (Nhóm)
                        </button>
                        <hr class="my-1">
                        <button onclick="window.open('in_baocao_hoidong.php','_blank')" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-print"></i> In Báo cáo
                        </button>
                        <a href="export_excel_gvpb_gvhd.php" target="_blank" class="btn btn-success fw-bold shadow-sm">
    <i class="fas fa-file-excel"></i> Xuất Excel
</a>
                    </div>
                </div>

                <div id="gvList">
                    <?php
                    // Lấy danh sách GV và đếm số NHÓM họ đang phản biện
                    // Logic đếm: Đếm số nhóm (distinct nhom_id) mà GV này làm GVPB
                    $stmt = $pdo->query("SELECT MaGV, HoTen FROM giang_vien ORDER BY HoTen");
                    while ($gv = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Query đếm số nhóm mà GV này đang phản biện
                        $sql_count = "SELECT COUNT(DISTINCT ptn.nhom_id) 
                                      FROM danh_sach_sinh_vien sv
                                      JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id
                                      WHERE sv.gvpb_id = '{$gv['MaGV']}'";
                        $count = $pdo->query($sql_count)->fetchColumn();

                        echo "<div class='gv-item' draggable='true' data-gv='{$gv['MaGV']}' data-name='{$gv['HoTen']}'>
                                <div><div class='fw-bold'>{$gv['HoTen']}</div><small class='text-muted'>{$gv['MaGV']}</small></div>
                                <span class='badge bg-primary rounded-pill' id='badge-{$gv['MaGV']}'>{$count}</span>
                              </div>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-md-8">
            <h2 class="text-center fw-bold text-uppercase mb-4" style="color: #2c3e50;">Phân công GVPB cho Nhóm</h2>
            <?php
            $hds = $pdo->query("SELECT * FROM hoidong ORDER BY ten_hoidong");
            while ($hd = $hds->fetch(PDO::FETCH_ASSOC)) {
                
                // SQL QUAN TRỌNG: Lấy danh sách NHÓM thuộc hội đồng này
                // Logic: Tìm các nhóm có thành viên thuộc hội đồng ID = ?
                $sql_group = "SELECT DISTINCT n.id as nhom_id, n.ten_nhom, n.huong_de_tai, 
                                     gv.HoTen as gvhd_ten, gv.MaGV as gvhd_id
                              FROM nhom n
                              JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
                              JOIN danh_sach_sinh_vien sv ON ptn.sinh_vien_id = sv.id
                              LEFT JOIN giang_vien gv ON n.giang_vien_huong_dan_id = gv.MaGV
                              WHERE sv.hoidong_id = ?
                              ORDER BY n.ten_nhom";
                
                $stmt = $pdo->prepare($sql_group);
                $stmt->execute([$hd['id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo "<div class='council-header'><h5 class='m-0'><i class='fas fa-users'></i> {$hd['ten_hoidong']} - Phòng {$hd['phong']}</h5></div><div class='row'>";
                    
                    while ($nhom = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Lấy danh sách thành viên trong nhóm
                        $stmt_tv = $pdo->prepare("SELECT HoTen, MaSV, gvpb_id FROM danh_sach_sinh_vien sv 
                                                  JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id 
                                                  WHERE ptn.nhom_id = ?");
                        $stmt_tv->execute([$nhom['nhom_id']]);
                        $thanh_vien = $stmt_tv->fetchAll(PDO::FETCH_ASSOC);

                        // Xác định GVPB hiện tại của nhóm (Lấy từ thành viên đầu tiên)
                        $current_gvpb_id = null;
                        $current_gvpb_name = '';
                        $has_gv = '';
                        $display_text = '<span class="text-muted"><i class="fas fa-arrow-down"></i> Kéo GVPB vào nhóm</span>';

                        if (count($thanh_vien) > 0 && !empty($thanh_vien[0]['gvpb_id'])) {
                            $current_gvpb_id = $thanh_vien[0]['gvpb_id'];
                            // Lấy tên GV
                            $stmt_name = $pdo->prepare("SELECT HoTen FROM giang_vien WHERE MaGV = ?");
                            $stmt_name->execute([$current_gvpb_id]);
                            $gv_name = $stmt_name->fetchColumn();
                            
                            $has_gv = 'has-gv';
                            $display_text = "<div class='fw-bold text-success'><i class='fas fa-check-circle'></i> {$gv_name}</div>";
                        }

                        $gvhd_display = $nhom['gvhd_ten'] ? $nhom['gvhd_ten'] : '<span class="text-danger">Chưa có</span>';

                        echo "<div class='col-lg-6 mb-4'>
                                <div class='group-card h-100'>
                                    <div class='group-header'>
                                        <span><i class='fas fa-layer-group'></i> {$nhom['ten_nhom']}</span>
                                        <span class='badge bg-secondary'>".count($thanh_vien)." SV</span>
                                    </div>
                                    
                                    <div class='p-3'>
                                        <div class='mb-2 fw-bold text-primary'>{$nhom['huong_de_tai']}</div>
                                        <div class='small text-muted mb-2'>GVHD: <strong>{$gvhd_display}</strong></div>
                                        
                                        <ul class='member-list'>";
                                        foreach($thanh_vien as $tv) {
                                            echo "<li><span>{$tv['HoTen']}</span> <span class='text-muted small'>{$tv['MaSV']}</span></li>";
                                        }
                                  echo "</ul>

                                        <div class='drop-zone {$has_gv}' data-nhomid='{$nhom['nhom_id']}'>{$display_text}</div>
                                    </div>
                                </div>
                              </div>";
                    }
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>
</div>
<div id="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // TÌM KIẾM GV
    document.getElementById('searchGV').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('.gv-item').forEach(item => {
            item.style.display = item.innerText.toLowerCase().includes(filter) ? 'flex' : 'none';
        });
    });

    // KÉO THẢ (DRAG)
    document.querySelectorAll('.gv-item').forEach(item => {
        item.addEventListener('dragstart', e => {
            e.dataTransfer.setData('gvID', item.dataset.gv);
            e.dataTransfer.setData('gvName', item.dataset.name);
            item.classList.add('opacity-50');
        });
        item.addEventListener('dragend', () => item.classList.remove('opacity-50'));
    });

    // VÙNG THẢ (DROP ZONE)
    document.querySelectorAll('.drop-zone').forEach(zone => {
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', e => {
            e.preventDefault(); zone.classList.remove('dragover');
            const gvID = e.dataTransfer.getData('gvID');
            const gvName = e.dataTransfer.getData('gvName');
            const nhomID = zone.dataset.nhomid; // Lấy ID Nhóm
            
            if (gvID && nhomID) assignReviewerGroup(nhomID, gvID, gvName, zone);
        });
    });
});

// Gán Thủ công (Cho Nhóm)
function assignReviewerGroup(nhomID, gvID, gvName, zoneElement) {
    zoneElement.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';
    
    // Gửi action = group
    fetch('save_gvpb.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        // Chú ý: Gửi nhom_id thay vì sv_id
        body: `action=group&nhom_id=${nhomID}&gvpb_id=${gvID}`
    })
    .then(r => r.text())
    .then(res => {
        if (res.trim() === 'OK') {
            zoneElement.className = 'drop-zone has-gv';
            zoneElement.innerHTML = `<div class='fw-bold text-success'><i class='fas fa-check-circle'></i> ${gvName}</div>`;
            updateBadge(gvID);
            showToast(`Đã phân nhóm cho ${gvName}`, 'success');
        } else {
            zoneElement.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Lỗi!</span>';
            showToast(res, 'danger'); 
        }
    });
}

// Phân Tự động (Cho Nhóm)
function autoAssignGroup() {
    if (!confirm('Tự động phân công GVPB cho các NHÓM chưa có (Tránh trùng GVHD)?')) return;

    const btn = document.querySelector('button[onclick="autoAssignGroup()"]');
    const oldText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    btn.disabled = true;

    fetch('save_gvpb.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=auto_group` // Action mới cho nhóm
    })
    .then(r => r.text())
    .then(data => {
        const parts = data.split('|');
        if (parts[0] === 'OK') {
            alert(`Hoàn tất! Đã phân bổ cho ${parts[1]} nhóm.`);
            location.reload();
        } else {
            alert('Lỗi: ' + data);
            btn.innerHTML = oldText;
            btn.disabled = false;
        }
    });
}

function updateBadge(id) {
    const el = document.getElementById(`badge-${id}`);
    if (el) el.innerText = parseInt(el.innerText) + 1;
}

function showToast(msg, type) {
    const box = document.getElementById('toast-container');
    const html = `<div class="toast show align-items-center text-white bg-${type} border-0 mb-2"><div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`;
    const div = document.createElement('div');
    div.innerHTML = html;
    box.appendChild(div.firstChild);
    setTimeout(() => box.firstChild.remove(), 3000);
}
</script>
</body>
</html>
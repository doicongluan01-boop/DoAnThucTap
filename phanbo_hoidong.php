<?php 

session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('<h3 class="text-center text-danger mt-5">Bạn không có quyền truy cập trang này!</h3>');
}

// === ĐẾM SỐ NHÓM CHƯA PHÂN ===
$sql_count = "SELECT COUNT(DISTINCT n.id) 
              FROM nhom n
              JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
              JOIN danh_sach_sinh_vien sv ON ptn.sinh_vien_id = sv.id
              WHERE sv.hoidong_id IS NULL OR sv.hoidong_id = 0";
$chua_phan_count = $pdo->query($sql_count)->fetchColumn();

// === HÀM RENDER NHÓM (CÓ GVHD) ===
function renderGroups($pdo, $hoidong_id = null) {
    if ($hoidong_id === null) {
        $whereClause = "(sv.hoidong_id IS NULL OR sv.hoidong_id = 0)";
    } else {
        $whereClause = "sv.hoidong_id = " . intval($hoidong_id);
    }

    $sql = "SELECT DISTINCT n.id, n.ten_nhom, n.huong_de_tai, n.giang_vien_huong_dan_id 
            FROM nhom n
            JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
            JOIN danh_sach_sinh_vien sv ON ptn.sinh_vien_id = sv.id
            WHERE $whereClause
            ORDER BY n.id ASC"; // Sắp xếp ID tăng dần
            
    $stmt = $pdo->query($sql);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($groups as $g) {
        // Lấy thành viên
        $stmt_mem = $pdo->prepare("
            SELECT sv.MaSV, sv.HoTen 
            FROM danh_sach_sinh_vien sv
            JOIN phan_thuoc_nhom ptn ON sv.id = ptn.sinh_vien_id
            WHERE ptn.nhom_id = ?");
        $stmt_mem->execute([$g['id']]);
        $members = $stmt_mem->fetchAll();
        ?>
        <div class="group-item" draggable="true" data-nhom-id="<?= $g['id'] ?>">
            <div class="group-header d-flex justify-content-between align-items-center">
                <span class="fw-bold">
                    <i class="bi bi-people-fill text-primary"></i> <?= htmlspecialchars($g['ten_nhom']) ?>
                </span>
                <span class="badge bg-secondary rounded-pill"><?= count($members) ?> SV</span>
            </div>
            
            <div class="group-body">
                <?php if(!empty($g['giang_vien_huong_dan_id'])): ?>
                    <div class="text-success fw-bold small mb-1 border-bottom pb-1">
                        <i class="bi bi-person-workspace"></i> GV: <?= htmlspecialchars($g['giang_vien_huong_dan_id']) ?>
                    </div>
                <?php endif; ?>

                <?php if(!empty($g['huong_de_tai'])): ?>
                    <div class="text-muted small fst-italic mb-2 text-truncate" title="<?= htmlspecialchars($g['huong_de_tai']) ?>">
                        <i class="bi bi-card-text"></i> <?= htmlspecialchars($g['huong_de_tai']) ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($members as $mem): ?>
                    <div class="sv-mini-row text-dark">
                        <small>• <?= $mem['MaSV'] ?> - <?= htmlspecialchars($mem['HoTen']) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phân bổ Nhóm vào Hội đồng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* === CSS CHO STICKY SIDEBAR === */
        .sticky-sidebar {
            position: -webkit-sticky; /* Cho Safari */
            position: sticky;
            top: 20px; /* Cách mép trên 20px khi cuộn */
            z-index: 1000;
            max-height: 90vh; /* Giới hạn chiều cao */
            overflow-y: auto; /* Cho phép cuộn bên trong nếu danh sách quá dài */
        }
        
        /* Tùy chỉnh thanh cuộn cho đẹp */
        .sticky-sidebar::-webkit-scrollbar { width: 6px; }
        .sticky-sidebar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }

        .card-hd {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            background: white;
            height: 100%;
        }

        .drop-zone {
            min-height: 150px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 0 0 8px 8px;
            flex-grow: 1;
            transition: background 0.2s;
        }
        .drop-zone.dragover {
            background-color: #e3f2fd;
            border: 2px dashed #0d6efd;
        }

        .group-item {
            background: #fff;
            border: 1px solid #dee2e6;
            border-left: 4px solid #0d6efd;
            border-radius: 6px;
            margin-bottom: 10px;
            cursor: move;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .group-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }
        .group-header { padding: 8px 10px; background: #f8f9fa; border-bottom: 1px solid #eee; }
        .group-body { padding: 8px 10px; font-size: 0.9rem; }
        .sv-mini-row { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .btn-print-sm { font-size: 0.8rem; padding: 2px 8px; }
        
        /* Trang trí cho khung chứa bảng 'Chưa phân' */
#box_chua_phan {
    margin-top: 50px;              /* Cách xa phần trên một chút */
    border: 2px solid #ffc107;     /* Viền màu vàng cam nổi bật */
    border-radius: 10px;           /* Bo tròn góc */
    box-shadow: 0 10px 30px rgba(0,0,0,0.15); /* Đổ bóng đậm tạo cảm giác nổi 3D */
    padding: 20px;
    background-color: #fffaf0;     /* Nền màu kem nhạt */
    position: relative;            /* Để căn chỉnh */
    transition: all 0.3s ease;     /* Hiệu ứng mượt khi di chuột */
}

/* Khi di chuột vào thì nổi hơn nữa */
#box_chua_phan:hover {
    transform: translateY(-5px);   /* Nhấc nhẹ lên */
    box-shadow: 0 15px 35px rgba(255, 193, 7, 0.4); /* Bóng phát sáng màu vàng */
}

/* Tiêu đề của bảng chưa phân */
#box_chua_phan h3 {
    color: #d63384;                /* Màu chữ hồng đậm hoặc đỏ tùy ý */
    text-transform: uppercase;
    text-align: center;
    border-bottom: 2px dashed #ffc107;
    padding-bottom: 15px;
    margin-bottom: 20px;
}
    </style>
</head>
<body>
    <div class="container-fluid py-3">
        
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="mb-0 text-primary text-uppercase fw-bold">
                <i class="bi bi-kanban"></i> Phân bổ Nhóm SV
            </h4>
            
            <div>
                <a href="phanbo_tudong.php" class="btn btn-success me-2">
                    <i class="bi bi-magic"></i> Phân tự động
                </a>
                
                <a href="in_tatca_danhsach_hoidong.php" target="_blank" class="btn btn-dark">
                    <i class="bi bi-printer-fill"></i> In tất cả DS
                </a>
            </div>
            
            <div class="text-danger fw-bold">
                Chưa phân: <span id="count-chua-phan" class="badge bg-danger fs-6"><?= $chua_phan_count ?></span> nhóm
            </div>
        </div>

        <div class="row align-items-start g-3" >
            
            <div class="col-lg-3 col-md-4">
                <div class="sticky-sidebar card card-hd border-danger">
                    <div class="card-header bg-danger text-white text-center fw-bold", id="box_chua_phan">
                        <i class="bi bi-inbox"></i> KHO CHƯA PHÂN
                    </div>
                    <div class="drop-zone" id="chua-phan-zone" data-hdid="chua">
                        <?php renderGroups($pdo, null); ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-md-8">
                <div class="row g-3">
                    <?php
                    $hds = $pdo->query("SELECT * FROM hoidong ORDER BY ten_hoidong")->fetchAll();
                    foreach ($hds as $hd):
                        // Đếm số nhóm trong HĐ
                        $sql_c = "SELECT COUNT(DISTINCT n.id) 
                                  FROM nhom n
                                  JOIN phan_thuoc_nhom ptn ON n.id = ptn.nhom_id
                                  JOIN danh_sach_sinh_vien sv ON ptn.sinh_vien_id = sv.id
                                  WHERE sv.hoidong_id = {$hd['id']}";
                        $cnt = $pdo->query($sql_c)->fetchColumn();
                    ?>
                        <div class="col-xxl-4 col-xl-4 col-md-6">
                            <div class="card card-hd">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold"><?= htmlspecialchars($hd['ten_hoidong']) ?></span>
                                        <span class="badge bg-white text-primary group-count-badge"><?= $cnt ?> nhóm</span>
                                    </div>
                                    <div class="small mt-1 opacity-75">
                                        <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($hd['phong']) ?>
                                    </div>
                                </div>
                                
                                <div class="drop-zone" id="zone-<?= $hd['id'] ?>" data-hdid="<?= $hd['id'] ?>">
                                    <?php renderGroups($pdo, $hd['id']); ?>
                                </div>

                                <div class="card-footer bg-white text-center border-top-0 pb-3">
                                    <button class="btn btn-outline-primary btn-sm w-100" 
                                            onclick="window.open('in_danhsach_hoidong.php?id=<?= $hd['id'] ?>', '_blank')">
                                        <i class="bi bi-printer"></i> In danh sách này
                                    </button>
                                </div>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const zones = document.querySelectorAll('.drop-zone');
            const items = document.querySelectorAll('.group-item');

            items.forEach(item => {
                item.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('nhom_id', item.dataset.nhomId);
                    e.dataTransfer.effectAllowed = "move";
                    item.classList.add('opacity-50');
                });
                item.addEventListener('dragend', () => {
                    item.classList.remove('opacity-50');
                });
            });

            zones.forEach(zone => {
                zone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    zone.classList.add('dragover');
                });
                zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
                zone.addEventListener('drop', handleDrop);
            });
        });

      // Trong file index.php (Giao diện chính)

function handleDrop(e) {
    e.preventDefault();
    const zone = e.currentTarget;
    zone.classList.remove('dragover');

    const nhomId = e.dataTransfer.getData('nhom_id');
    if (!nhomId) return;

    const groupItem = document.querySelector(`.group-item[data-nhom-id="${nhomId}"]`);
    if (!groupItem) return;

    // Lấy ID hội đồng đích
    let targetHdId = zone.getAttribute('data-hdid'); // Lấy attribute data-hdid
    
    // LOGIC FIX LỖI:
    // Nếu kéo vào vùng chưa phân (data-hdid="chua"), ta giữ nguyên chữ 'chua' gửi đi
    // PHP ở Bước 1 sẽ bắt chữ 'chua' này và đổi thành NULL.
    
    console.log("Đang chuyển nhóm " + nhomId + " sang hội đồng: " + targetHdId);

    // Gửi AJAX
    fetch('save_phanbo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `nhom_id=${nhomId}&hoidong_id=${targetHdId}`
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === 'Success') {
            zone.appendChild(groupItem);
            updateCounts(); // Cập nhật số lượng trên badge
        } else {
            alert('Lỗi: ' + data);
            console.error(data);
        }
    })
    .catch(err => console.error(err));
}
// Hàm cập nhật số lượng (đảm bảo code này có để badge nhảy số)
function updateCounts() {
    // Đếm kho chưa phân
    const chuaZone = document.getElementById('chua-phan-zone');
    if(chuaZone) document.getElementById('count-chua-phan').innerText = chuaZone.children.length;

    // Đếm từng hội đồng
    document.querySelectorAll('.card-hd').forEach(card => {
        const badge = card.querySelector('.group-count-badge');
        const zone = card.querySelector('.drop-zone');
        if (badge && zone) {
            // Trừ đi vùng chưa phân nếu nó nằm trong card (layout cũ)
            if (zone.id !== 'chua-phan-zone') {
                 badge.innerText = zone.children.length + " nhóm";
            }
        }
    });
}
    </script>
</body>

</html>
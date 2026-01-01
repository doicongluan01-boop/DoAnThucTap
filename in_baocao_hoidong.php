<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Phiếu Báo Cáo Đồ Án - Chuẩn STU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Cấu hình trang in A4 */
        @page { size: A4; margin: 2cm 1.5cm 2cm 2cm; }
        body { 
            font-family: 'Times New Roman', serif; 
            font-size: 13pt; 
            line-height: 1.3; 
            color: #000;
        }
        
        /* Class ngắt trang khi in nhiều nhóm */
        .page-break { page-break-after: always; }
        
        /* Header chuẩn STU */
        .stu-header-left { text-align: center; font-weight: bold; width: 50%; float: left; }
        .stu-header-right { text-align: center; font-weight: bold; width: 50%; float: right; }
        .stu-hr { width: 60%; margin: 5px auto; border-top: 1px solid black; opacity: 1; }
        
        /* Tiêu đề chính */
        .report-title { 
            clear: both; 
            padding-top: 20px; 
            text-align: center; 
            font-size: 16pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin-bottom: 30px; 
        }

        /* Phần thông tin chi tiết */
        .info-label { font-weight: bold; min-width: 180px; display: inline-block; vertical-align: top; }
        .info-content { display: inline-block; width: calc(100% - 190px); text-align: justify; }
        
        /* Bảng danh sách sinh viên */
        .sv-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        .sv-table th, .sv-table td { border: 1px solid black; padding: 8px; text-align: center; }
        .sv-table th { background-color: #f0f0f0; font-weight: bold; }
        
        /* Footer ký tên */
        .footer-section { margin-top: 50px; }
        .sign-box { text-align: center; float: left; width: 50%; }
        
        /* Reset float */
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body onload="window.print()">

<?php
// 1. TRUY VẤN DỮ LIỆU (ĐÃ SỬA LỖI SQL)
// Đã xóa 's.detai' để tránh lỗi Column not found
$sql = "SELECT 
            s.id, s.MaSV, s.HoTen, s.Lop, s.huong_de_tai,
            n.id as nhom_id, n.ten_nhom, n.huong_de_tai as de_tai_nhom,
            gv.HoTen as gvhd_ten, 
            -- gv.HocVi as gvhd_hocvi, (Tạm bỏ nếu bảng GV chưa có cột HocVi)
            h.ten_hoidong, h.phong, h.thoigian,
            (SELECT HoTen FROM giang_vien WHERE MaGV = s.gvpb_id LIMIT 1) as gvpb_ten
        FROM danh_sach_sinh_vien s
        LEFT JOIN phan_thuoc_nhom ptn ON s.id = ptn.sinh_vien_id 
        LEFT JOIN nhom n ON ptn.nhom_id = n.id 
        LEFT JOIN giang_vien gv ON n.giang_vien_huong_dan_id = gv.MaGV 
        LEFT JOIN hoidong h ON s.hoidong_id = h.id
        ORDER BY h.ten_hoidong, n.ten_nhom, s.MaSV";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. GOM NHÓM DỮ LIỆU (Group by nhom_id)
$grouped_projects = [];

foreach ($all_data as $row) {
    // Nếu sinh viên chưa có nhóm, tạo ID ảo
    $key = !empty($row['nhom_id']) ? 'NHOM_'.$row['nhom_id'] : 'SV_'.$row['id'];
    
    if (!isset($grouped_projects[$key])) {
        // [LOGIC LẤY ĐỀ TÀI]
        // Ưu tiên lấy đề tài Nhóm -> Nếu không có thì lấy huong_de_tai của Sinh viên
        $ten_de_tai = $row['de_tai_nhom'] ?? $row['huong_de_tai'] ?? 'Chưa cập nhật đề tài';
        
        // Nếu tên đề tài vẫn rỗng, thử gán chuỗi mặc định
        if (trim($ten_de_tai) == '') $ten_de_tai = 'Chưa cập nhật đề tài';

        $grouped_projects[$key] = [
            'ten_de_tai' => $ten_de_tai,
            'gvhd'       => $row['gvhd_ten'],
            'gvpb'       => $row['gvpb_ten'],
            'hoidong'    => $row['ten_hoidong'],
            'phong'      => $row['phong'],
            'thoigian'   => $row['thoigian'],
            'students'   => [] // Mảng chứa danh sách sinh viên
        ];
    }
    // Thêm sinh viên vào danh sách của nhóm này
    $grouped_projects[$key]['students'][] = $row;
}

// 3. HIỂN THỊ RA HTML
foreach ($grouped_projects as $project) {
?>

    <div class="page-break">
        <div class="clearfix" style="margin-bottom: 30px;">
            <div class="stu-header-left">
                TRƯỜNG ĐH CÔNG NGHỆ SÀI GÒN<br>
                KHOA CÔNG NGHỆ THÔNG TIN
                <hr class="stu-hr">
            </div>
            <div class="stu-header-right">
                CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM<br>
                Độc lập - Tự do - Hạnh phúc
                <hr class="stu-hr">
            </div>
        </div>

        <div class="report-title">
            PHIẾU BÁO CÁO ĐỒ ÁN TỐT NGHIỆP<br>
            <span style="font-size: 14pt; font-weight: normal; text-transform: none;">(Dành cho Hội đồng bảo vệ)</span>
        </div>

        <div style="margin-bottom: 20px;">
            <div style="margin-bottom: 15px;">
                <span class="info-label">Tên đề tài:</span>
                <span class="info-content" style="font-weight: bold; text-transform: uppercase;">
                    <?= htmlspecialchars($project['ten_de_tai']) ?>
                </span>
            </div>
            
            <div style="margin-bottom: 15px;">
                <span class="info-label">Giảng viên hướng dẫn:</span>
                <span class="info-content"><?= htmlspecialchars($project['gvhd'] ?? 'Chưa phân công') ?></span>
            </div>

            <div style="margin-bottom: 15px;">
                <span class="info-label">Giảng viên phản biện:</span>
                <span class="info-content"><?= htmlspecialchars($project['gvpb'] ?? '............................................') ?></span>
            </div>
            
            <div style="margin-bottom: 15px;">
                <span class="info-label">Hội đồng bảo vệ:</span>
                <span class="info-content">
                    <?= htmlspecialchars($project['hoidong'] ?? '.....') ?> 
                    (Phòng: <?= htmlspecialchars($project['phong'] ?? '.....') ?> 
                    - Thời gian: <?= !empty($project['thoigian']) ? date('H:i d/m/Y', strtotime($project['thoigian'])) : '.....' ?>)
                </span>
            </div>
        </div>

        <p style="font-weight: bold; margin-bottom: 5px;">Sinh viên thực hiện:</p>
        <table class="sv-table">
            <thead>
                <tr>
                    <th style="width: 10%;">STT</th>
                    <th style="width: 25%;">MSSV</th>
                    <th style="width: 45%;">Họ và Tên</th>
                    <th style="width: 20%;">Lớp</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                foreach ($project['students'] as $sv): 
                ?>
                <tr>
                    <td><?= $stt++ ?></td>
                    <td><?= htmlspecialchars($sv['MaSV']) ?></td>
                    <td style="text-align: left; padding-left: 20px; font-weight: bold;">
                        <?= htmlspecialchars($sv['HoTen']) ?>
                    </td>
                    <td><?= htmlspecialchars($sv['Lop']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; border: 1px dashed #000; padding: 15px; height: 150px;">
            <strong>Ý kiến / Nhận xét của Hội đồng:</strong>
            <br>.......................................................................................................................................................
            <br>.......................................................................................................................................................
            <br>.......................................................................................................................................................
        </div>

        <div class="clearfix footer-section">
            <div class="sign-box">
                <p style="font-weight: bold;">THƯ KÝ HỘI ĐỒNG</p>
                <br><br><br><br>
                <p>______________________</p>
            </div>
            <div class="sign-box">
                <p style="font-style: italic;">TP. Hồ Chí Minh, ngày ...... tháng ...... năm 20...</p>
                <p style="font-weight: bold;">CHỦ TỊCH HỘI ĐỒNG</p>
                <br><br><br><br>
                <p>______________________</p>
            </div>
        </div>
        
    </div> <?php
} // End Foreach
?>

</body>
</html>
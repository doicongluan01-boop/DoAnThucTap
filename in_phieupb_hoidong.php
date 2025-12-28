<?php require 'config.php'; ?>
<!DOCTYPE html>
<html><head><meta charset="utf-8">
<title>Phiếu Phản Biện</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>@page { margin: 1.5cm; } body { font-family: 'Times New Roman', serif; }</style>
</head><body class="p-5">
<?php
$gvs = $pdo->query("SELECT DISTINCT gvpb_id, gv.HoTen FROM danh_sach_sinh_vien s LEFT JOIN giang_vien gv ON s.gvpb_id = gv.MaGV WHERE gvpb_id IS NOT NULL");
while($gv = $gvs->fetch()){
    echo "<h2 class='text-center fw-bold mb-5'>PHIẾU PHẢN BIỆN - GV: {$gv['HoTen']}</h2>";
    $svs = $pdo->prepare("SELECT s.*, h.ten_hoidong, h.phong FROM danh_sach_sinh_vien s JOIN hoidong h ON s.hoidong_id = h.id WHERE s.gvpb_id = ?");
    $svs->execute([$gv['gvpb_id']]);
    while($sv = $svs->fetch()){
        echo "<div class='border p-4 mb-4'>
                <p><strong>MSSV:</strong> {$sv['MaSV']} - <strong>Họ tên:</strong> {$sv['HoTen']}</p>
                <p><strong>Đề tài:</strong> {$sv['DeTai']}</p>
                <p><strong>Hội đồng:</strong> {$sv['ten_hoidong']} - Phòng {$sv['phong']}</p>
              </div>";
    }
    echo "<div class='page-break'></div>"; // ngắt trang cho từng GV
}
?>
<style>.page-break { page-break-after: always; }</style>
<script>window.print();</script>
</body></html>
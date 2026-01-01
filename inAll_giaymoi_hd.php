<?php
require 'config.php';
$stmt = $pdo->query("SELECT * FROM hoidong ORDER BY ten_hoidong");
$hoi_dongs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>In tất cả giấy mời</title>
    <style>
        body { font-family: "Times New Roman", serif; margin: 40px; }
        .page { page-break-after: always; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
<?php foreach($hoi_dongs as $hd): ?>
<div class="page">
    <div class="text-center">
        <p><strong>TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN</strong></p>
        <p><strong>KHOA CÔNG NGHỆ THÔNG TIN</strong></p>
        <p>-------oOo-------</p>
    </div>

    <h2 class="text-center bold">GIẤY MỜI HỘI ĐỒNG <?= $hd['ten_hoidong'] ?></h2>

    <p><strong>Chủ tịch:</strong> <?= $hd['chu_tich'] ?></p>
    <p><strong>Thư ký:</strong> <?= $hd['thu_ky'] ?></p>
    <?php if($hd['uy_vien1']): ?><p><strong>Ủy viên:</strong> <?= $hd['uy_vien1'] ?></p><?php endif; ?>
    <?php if($hd['uy_vien2']): ?><p><strong>Ủy viên:</strong> <?= $hd['uy_vien2'] ?></p><?php endif; ?>

    <p><strong>Thời gian:</strong> <?= date('H\hi \n\g\à\y d/m/Y', strtotime($hd['thoigian'])) ?></p>
    <p><strong>Phòng:</strong> <?= $hd['phong'] ?></p>

    <div style="margin-top:100px; text-align:right;">
        <p>TP. Hồ Chí Minh, ngày <?= date('d/m/Y') ?></p>
        <p class="bold">TRƯỞNG KHOA</p>
    </div>
</div>
<?php endforeach; ?>

<script>window.print();</script>
</body>
</html>
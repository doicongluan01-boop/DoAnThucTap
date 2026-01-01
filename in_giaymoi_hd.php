<?php
require 'config.php';
if (!isset($_GET['id'])) die("Không có ID");

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM hoidong WHERE id=?");
$stmt->execute([$id]);
$hd = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$hd) die("Không tìm thấy hội đồng");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giấy mời hội đồng <?= $hd['ten_hoidong'] ?></title>
    <style>
        body { font-family: "Times New Roman", serif; margin: 40px; line-height: 1.6; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .mt-5 { margin-top: 50px; }
        .text-right { text-align: right; }
        table { width: 100%; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="text-center">
        <p><strong>TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN</strong></p>
        <p><strong>KHOA CÔNG NGHỆ THÔNG TIN</strong></p>
        <p>-------oOo-------</p>
    </div>

    <h2 class="text-center bold">GIẤY MỜI THAM GIA HỘI ĐỒNG BẢO VỆ ĐỒ ÁN TỐT NGHIỆP</h2>

    <p>Kính gửi: <strong><?= $hd['chu_tich'] ?></strong> - Chủ tịch hội đồng</p>
    <p>Kính gửi: <strong><?= $hd['thu_ky'] ?></strong> - Thư ký hội đồng</p>
    <?php if($hd['uy_vien1']): ?><p>Kính gửi: <strong><?= $hd['uy_vien1'] ?></strong> - Ủy viên</p><?php endif; ?>
    <?php if($hd['uy_vien2']): ?><p>Kính gửi: <strong><?= $hd['uy_vien2'] ?></strong> - Ủy viên</p><?php endif; ?>

    <p>Trân trọng kính mời Thầy/Cô tham gia <strong>Hội đồng bảo vệ đồ án tốt nghiệp</strong> với thông tin như sau:</p>

    <ul>
        <li><strong>Hội đồng:</strong> <?= $hd['ten_hoidong'] ?></li>
        <li><strong>Thời gian:</strong> <?= date('H\hi, \n\g\à\y d/m/Y', strtotime($hd['thoigian'])) ?></li>
        <li><strong>Địa điểm:</strong> Phòng <?= $hd['phong'] ?></li>
    </ul>

    <p>Rất mong Thầy/Cô sắp xếp thời gian tham dự đúng giờ.</p>
    <p>Trân trọng cảm ơn!</p>

    <div class="mt-5 text-right">
        <p>TP. Hồ Chí Minh, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?></p>
        <p class="bold">TRƯỞNG KHOA</p>
        <p class="mt-5">(Ký, ghi rõ họ tên)</p>
    </div>

    <script>window.print();</script>
</body>
</html>
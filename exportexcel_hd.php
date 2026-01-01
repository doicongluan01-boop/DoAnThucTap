<?php
require 'config.php';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Danh_sach_hoi_dong_LVTN.xls"');

echo "STT\tTên hội đồng\tChủ tịch\tThư ký\tỦy viên 1\tỦy viên 2\tPhòng\tThời gian\n";

$stmt = $pdo->query("SELECT * FROM hoidong ORDER BY ten_hoidong");
$stt = 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "$stt\t{$row['ten_hoidong']}\t{$row['chu_tich']}\t{$row['thu_ky']}\t{$row['uy_vien1']}\t{$row['uy_vien2']}\t{$row['phong']}\t" .
         date('d/m/Y H:i', strtotime($row['thoigian'])) . "\n";
    $stt++;
}
?>
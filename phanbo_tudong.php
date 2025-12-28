<?php
require 'config.php';

try {
    // Lấy hội đồng theo thứ tự ID cố định → không bao giờ thay đổi
    $hoidongs = $pdo->query("SELECT id FROM hoidong ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($hoidongs)) {
        header("Location: phanbo_hoidong.php?msg=no_hd");
        exit;
    }

    // Lấy sinh viên chưa phân (bao gồm cả kéo về Chưa phân)
    $sv_chua_phan = $pdo->query("
        SELECT id FROM danh_sach_sinh_vien 
        WHERE hoidong_id IS NULL 
           OR hoidong_id = 0 
           OR hoidong_id NOT IN (SELECT id FROM hoidong)
        ORDER BY id ASC
    ")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($sv_chua_phan)) {
        header("Location: phanbo_hoidong.php?msg=done");
        exit;
    }

    $total_sv = count($sv_chua_phan);
    $total_hd = count($hoidongs);
    $base     = floor($total_sv / $total_hd);
    $extra    = $total_sv % $total_hd;

    $idx = 0;
    foreach ($hoidongs as $i => $hd_id) {
        $count = $base + ($i < $extra ? 1 : 0);
        for ($j = 0; $j < $count && $idx < $total_sv; $j++) {
            $sv_id = $sv_chua_phan[$idx++];
            $pdo->prepare("UPDATE danh_sach_sinh_vien SET hoidong_id = ? WHERE id = ?")
                ->execute([$hd_id, $sv_id]);
        }
    }

    header("Location: phanbo_hoidong.php?auto=ok&sl=$total_sv");
    exit;

} catch (Exception $e) {
    header("Location: phanbo_hoidong.php?msg=error");
    exit;
}
?>
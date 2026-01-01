<?php
require 'config.php';

if ($_POST) {
    $ten_hoidong = $_POST['ten_hoidong'];
    $phong       = $_POST['phong'];
    $thoigian    = $_POST['thoigian'];
    $chu_tich    = $_POST['chu_tich'];
    $thu_ky      = $_POST['thu_ky'];
    $uy_vien1    = $_POST['uy_vien1'] ?? '';
    $uy_vien2    = $_POST['uy_vien2'] ?? '';

    $sql = "INSERT INTO hoidong (ten_hoidong, chu_tich, thu_ky, uy_vien1, uy_vien2, phong, thoigian)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ten_hoidong, $chu_tich, $thu_ky, $uy_vien1, $uy_vien2, $phong, $thoigian]);

    header("Location: danhsach_hd.php");
}
?>
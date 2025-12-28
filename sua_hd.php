<?php
require 'config.php';

if ($_POST) {
    $id          = $_POST['id'];
    $ten_hoidong = $_POST['ten_hoidong'];
    $phong       = $_POST['phong'];
    $thoigian    = $_POST['thoigian'];
    $chu_tich    = $_POST['chu_tich'];
    $thu_ky      = $_POST['thu_ky'];
    $uy_vien1    = $_POST['uy_vien1'] ?? '';
    $uy_vien2    = $_POST['uy_vien2'] ?? '';

    $sql = "UPDATE hoidong SET ten_hoidong=?, chu_tich=?, thu_ky=?, uy_vien1=?, uy_vien2=?, phong=?, thoigian=?
            WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ten_hoidong, $chu_tich, $thu_ky, $uy_vien1, $uy_vien2, $phong, $thoigian, $id]);

    header("Location: danhsach_hd.php");
}
?>
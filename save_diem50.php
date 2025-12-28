<?php
require 'config.php';
if($_SESSION['role'] !== 'gvhd') die(json_encode(['status'=>'error','message'=>'Không có quyền']));

$magv = $_SESSION['ma_gv'];

foreach($_POST['diem50'] as $id => $diem){
  $diem = round(floatval($diem), 2);
  if($diem < 0) $diem = 0;
  if($diem > 10) $diem = 10;
  
  $pdo->prepare("UPDATE danh_sach_sinh_vien SET diem_50=? WHERE id=? AND gvhd=?")
      ->execute([$diem, $id, $magv]);
}

echo json_encode(['status'=>'success','message'=>'Lưu điểm 50% thành công!']);
?>
<?php
require_once 'config.php';
header('Content-Type: application/json');

$magv = $_POST['magv'] ?? '';
if (!$magv) die(json_encode(['success'=>false, 'message'=>'Thiếu mã GV']));

try {
    $pdo->beginTransaction();
    
    $upd = $pdo->prepare("UPDATE danh_sach_sinh_vien SET gvhd = ? WHERE gvhd IS NULL OR gvhd = ''");
    $count = $upd->execute([$magv]) ? $upd->rowCount() : 0;

    $pdo->commit();
    echo json_encode(['success'=>true, 'message'=>"Đã phân công $magv cho $count sinh viên!"]);
} catch(Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
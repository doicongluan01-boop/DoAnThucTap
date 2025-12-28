<?php
require 'config.php';
$count = $pdo->query("
    SELECT COUNT(*) FROM danh_sach_sinh_vien 
    WHERE hoidong_id IS NULL 
       OR hoidong_id = 0 
       OR hoidong_id NOT IN (SELECT id FROM hoidong)
")->fetchColumn();
echo $count;
?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// $host = 'sql209.infinityfree.com';
// $dbname = 'if0_40253287_thinhvaluan';  // Tên DB của bạn
// $user = 'if0_40253287';
// $pass = 'Thinh23042004';
$host = 'localhost';
$dbname = 'qlsv';  // Tên DB của bạn
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'DB lỗi: ' . $e->getMessage()]));
}
?>
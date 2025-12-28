<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['hoten'])) {
    echo json_encode([
        'success' => true,
        'hoten'   => $_SESSION['hoten'],
        'role'    => $_SESSION['role'] ?? 'user'
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>
<?php
session_start();
require_once 'config.php';  

// Bật lỗi để dev (sau này tắt đi)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Xử lý nộp báo cáo
$message = '';
$message_type = '';

if (isset($_POST['nop'])) {
    $masv    = trim($_POST['masv']);
    $noidung = trim($_POST['noidung']);

    if (empty($masv) || empty($noidung)) {
        $message = 'Vui lòng nhập đầy đủ thông tin!';
        $message_type = 'error';
    } elseif (strlen($masv) > 20) {
        $message = 'Mã sinh viên không hợp lệ!';
        $message_type = 'error';
    } else {
        try {
            $sql = "INSERT INTO bao_caogv (MaSV, ngay, noi_dung) VALUES (?, NOW(), ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$masv, $noidung]);

            $message = 'Nộp báo cáo thành công!';
            $message_type = 'success';
            $_POST = []; // reset form
        } catch (Exception $e) {
            $message = 'Lỗi lưu dữ liệu!';
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Báo Cáo Đồ Án</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body{font-family:Arial,sans-serif;background:#f0f2f5;padding:20px}
        .box{max-width:900px;margin:auto;background:white;padding:30px;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.1)}
        h2{color:#1a73e8;text-align:center;margin-bottom:20px}
        input,textarea,button{width:100%;padding:15px;margin:10px 0;border-radius:10px;font-size:17px;border:1px solid #ddd;box-sizing:border-box}
        textarea{height:130px;resize:vertical}
        button{background:#1a73e8;color:white;border:none;cursor:pointer;font-weight:bold;font-size:18px}
        button:hover{background:#0d5bbd}
        table{width:100%;border-collapse:collapse;margin-top:30px}
        th{background:#1a73e8;color:white;padding:15px;text-align:center}
        td{padding:12px;border-bottom:1px solid #eee}
        td:nth-child(5){text-align:left}
        .toast{position:fixed;top:20px;right:20px;padding:16px 30px;border-radius:12px;color:white;font-weight:bold;z-index:9999;box-shadow:0 10px 30px rgba(0,0,0,0.2);opacity:0;transform:translateY(-20px);transition:all 0.4s}
        .toast.show{opacity:1;transform:translateY(0)}
        .success{background:#22c55e}
        .error{background:#ef4444}
    </style>
</head>
<body>

<div class="box">
    <h2>NỘP BÁO CÁO TIẾN ĐỘ ĐỒ ÁN</h2>

    <form method="post">
        <input type="text" name="masv" placeholder="Mã sinh viên (VD: 21110001)" value="<?= htmlspecialchars($_POST['masv'] ?? '') ?>" required>
        <textarea name="noidung" placeholder="Hôm nay em đã làm gì..." required><?= htmlspecialchars($_POST['noidung'] ?? '') ?></textarea>
        <button type="submit" name="nop">NỘP BÁO CÁO</button>
    </form>

    <h2 style="margin-top:50px">Lịch sử báo cáo (50 báo cáo gần nhất)</h2>

    <table>
        <tr>
            <th>STT</th>
            <th>Mã SV</th>
            <th>Họ tên</th>
            <th>Ngày nộp</th>
            <th>Nội dung</th>
        </tr>

        <?php
        try {
            $sql = "SELECT g.MaSV, g.ngay, g.noi_dung, s.HoTen 
                    FROM bao_caogv g
                    LEFT JOIN danh_sach_sinh_vien s ON g.MaSV = s.MaSV
                    ORDER BY g.ngay DESC LIMIT 50";
            
            $stmt = $pdo->query($sql);           // ← Dùng $pdo (không dùng $conn)
            $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($rs) {
                $stt = 1;
                foreach ($rs as $r) {
                    echo "<tr>
                        <td>$stt</td>
                        <td>{$r['MaSV']}</td>
                        <td>" . htmlspecialchars($r['HoTen'] ?: 'Chưa có tên') . "</td>
                        <td>" . date('d/m/Y H:i', strtotime($r['ngay'])) . "</td>
                        <td align='left'>" . nl2br(htmlspecialchars($r['noi_dung'])) . "</td>
                    </tr>";
                    $stt++;
                }
            } else {
                echo "<tr><td colspan='5' style='color:#e74c3c;padding:30px;text-align:center'>Chưa có báo cáo nào</td></tr>";
            }
        } catch (Exception $e) {
            echo "<tr><td colspan='5' style='color:red'>Lỗi tải dữ liệu</td></tr>";
        }
        ?>
    </table>
</div>

<!-- Toast thông báo -->
<?php if ($message): ?>
<div class="toast <?= $message_type ?> show" id="toast"><?= $message ?></div>
<script>setTimeout(() => document.getElementById('toast')?.remove(), 3000);</script>
<?php endif ?>

</body>
</html>
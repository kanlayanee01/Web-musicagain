<?php
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $file = $_FILES['image'] ?? null;

    // ตรวจสอบให้กรอกชื่อและเลือกไฟล์รูป
    if (!$name || !$file['tmp_name']) {
        $message = "กรุณากรอกชื่อศิลปินและเลือกไฟล์รูป";
    } else {
        $imageFilename = basename($file['name']);
        $uploadDir = 'uploads/images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $targetFile = $uploadDir . $imageFilename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // เพิ่มศิลปินลงฐานข้อมูล
            $stmt = $pdo->prepare("INSERT INTO artists (name, image) VALUES (:name, :image)");
            $stmt->execute([
                ':name' => $name,
                ':image' => $imageFilename
            ]);

            $message = "เพิ่มศิลปินเรียบร้อยแล้ว!";
        } else {
            $message = "เกิดข้อผิดพลาดในการอัปโหลดรูป";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มศิลปิน | Music Again</title>
    <link href="css/style-admin.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="add_song.php">จัดการเพลง</a>
    <a href="add_artist.php">จัดการศิลปิน</a>
    <a href="add_album.php">จัดการอัลบัม</a>
    <a href="logout.php">ออกจากระบบ</a>
</div>

<div class="main">
   

        <?php if($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>ชื่อศิลปิน:</label>
            <input type="text" name="name" placeholder="กรอกชื่อศิลปิน" required>

            <label>รูปศิลปิน:</label>
            <input type="file" name="image" accept="image/*" required>

            <button type="submit">เพิ่มศิลปิน</button>
        </form>
</div>
<div class="image">
    <img src="images/Music.png">
</div>
</body>
</html>
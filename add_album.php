<?php
require 'db.php';

$message = '';

// ดึงรายชื่อศิลปินสำหรับ dropdown
$artists = $pdo->query("SELECT * FROM artists ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $artist_id = $_POST['artist_id'] ?? '';
    $file = $_FILES['cover_image'] ?? null;

    if (!$title || !$artist_id) {
        $message = "กรุณากรอกชื่ออัลบัมและเลือกศิลปิน";
    } else {
        $coverFilename = null;

        // ถ้ามีไฟล์หน้าปกให้ upload
        if ($file && $file['tmp_name']) {
            $uploadDir = 'uploads/images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $coverFilename = basename($file['name']);
            $targetFile = $uploadDir . $coverFilename;

            if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
                $message = "เกิดข้อผิดพลาดในการอัปโหลดรูปหน้าปก";
                $coverFilename = null;
            }
        }

        // เพิ่มอัลบัมลง database (ลบ release_date ออก)
        $stmt = $pdo->prepare("INSERT INTO albums (title, artist_id, cover_image) 
                               VALUES (:title, :artist_id, :cover_image)");
        $stmt->execute([
            ':title' => $title,
            ':artist_id' => $artist_id,
            ':cover_image' => $coverFilename
        ]);

        $message = "เพิ่มอัลบัมเรียบร้อยแล้ว!";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มอัลบัม | Music Again</title>
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
            <label>ชื่ออัลบัม:</label>
            <input type="text" name="title" placeholder="กรอกชื่ออัลบัม" required>

            <label>ศิลปิน:</label>
            <select name="artist_id" required>
                <option value="">-- เลือกศิลปิน --</option>
                <?php foreach($artists as $artist): ?>
                    <option value="<?php echo $artist['id']; ?>"><?php echo htmlspecialchars($artist['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label>รูปหน้าปกอัลบัม:</label>
            <input type="file" name="cover_image" accept="image/*">

            <button type="submit">เพิ่มอัลบัม</button>
        </form>

</div>

<div class="image">
    <img src="images/Music.png">
</div>

</body>
</html>
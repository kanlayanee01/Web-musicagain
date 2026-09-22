<?php
require 'db.php';

$message = '';

$artists = $pdo->query("SELECT * FROM artists ORDER BY name")->fetchAll();
$albums = $pdo->query("SELECT * FROM albums ORDER BY title")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $artist_id = $_POST['artist_id'] ?? '';
    $album_id = $_POST['album_id'] ?? '';
    $file = $_FILES['file'] ?? null;

    if (!$title || !$artist_id || !$file) {
        $message = "กรุณากรอกข้อมูลทุกช่องและเลือกไฟล์เพลง";
    } else {
        $uploadDir = 'uploads/songs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = basename($file['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // ตรวจสอบอัลบั้ม
            if ($album_id === '0' || $album_id === '') {
                $album_id = null;
            }

            $stmt = $pdo->prepare("
                INSERT INTO songs (title, album_id, artist_id, file_path)
                VALUES (:title, :album_id, :artist_id, :file_path)
            ");
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':album_id', $album_id, $album_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':artist_id', $artist_id, PDO::PARAM_INT);
            $stmt->bindValue(':file_path', $filename);
            $stmt->execute();

            $message = "เพิ่มเพลงเรียบร้อยแล้ว!";
        } else {
            $message = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
        }
            }
    }
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มเพลง | Music Again</title>
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
            <label>ชื่อเพลง:</label>
            <input type="text" name="title" placeholder="กรอกชื่อเพลง" required>

            <label>ศิลปิน:</label>
            <select name="artist_id" required>
                <option value="">-- เลือกศิลปิน --</option>
                <?php foreach($artists as $artist): ?>
                    <option value="<?php echo $artist['id']; ?>"><?php echo htmlspecialchars($artist['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label>อัลบัม:</label>
            <select name="album_id" required>
                <option value="">-- เลือกอัลบัม --</option>
                <option value="0">เพลงเดี่ยว (ไม่มีอัลบั้ม)</option>
            <?php foreach($albums as $album): ?>
                <option value="<?php echo $album['id']; ?>"><?php echo htmlspecialchars($album['title']); ?></option>
            <?php endforeach; ?>
            </select>

            <label>ไฟล์เพลง (mp3):</label>
            <input type="file" name="file" accept="audio/mpeg" required>

            <button type="submit">เพิ่มเพลง</button>
        </form>

</div>
<div class="image">
    <img src="images/Music.png">
</div>
</body>
</html>
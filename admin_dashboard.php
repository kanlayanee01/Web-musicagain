<?php
require 'db.php';
session_start();

// 🔒 ตรวจสอบสิทธิ์แอดมิน
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';
$view = $_GET['view'] ?? 'songs'; // กำหนดว่าจะดูหน้าเพลง/ศิลปิน/อัลบัม

// โหลดข้อมูลศิลปินและอัลบัม สำหรับ dropdown
$artists = $pdo->query("SELECT * FROM artists ORDER BY name")->fetchAll();
$albums = $pdo->query("SELECT * FROM albums ORDER BY title")->fetchAll();

// ฟังก์ชันเพิ่มเพลง
function addSong($pdo, &$message) {
    global $artists, $albums;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_song'])) {
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
                $album_id = ($album_id === '0' ? null : $album_id);
                $stmt = $pdo->prepare("INSERT INTO songs (title, album_id, artist_id, file_path)
                    VALUES (:title, :album_id, :artist_id, :file_path)");
                $stmt->bindValue(':title', $title);
                $stmt->bindValue(':album_id', $album_id, $album_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindValue(':artist_id', $artist_id, PDO::PARAM_INT);
                $stmt->bindValue(':file_path', $filename);
                $stmt->execute();
                $message = "เพิ่มเพลงเรียบร้อยแล้ว!";
            } else {
                $message = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
            }
        }
    }
}

// ฟังก์ชันเพิ่มศิลปิน
function addArtist($pdo, &$message) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_artist'])) {
        $name = trim($_POST['name'] ?? '');
        $file = $_FILES['image'] ?? null;

        if (!$name || !$file['tmp_name']) {
            $message = "กรุณากรอกชื่อศิลปินและเลือกไฟล์รูป";
        } else {
            $imageFilename = basename($file['name']);
            $uploadDir = 'uploads/images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $targetFile = $uploadDir . $imageFilename;
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $stmt = $pdo->prepare("INSERT INTO artists (name, image) VALUES (:name, :image)");
                $stmt->execute([':name'=>$name, ':image'=>$imageFilename]);
                $message = "เพิ่มศิลปินเรียบร้อยแล้ว!";
            } else {
                $message = "เกิดข้อผิดพลาดในการอัปโหลดรูป";
            }
        }
    }
}

// ฟังก์ชันเพิ่มอัลบัม
function addAlbum($pdo, &$message) {
    global $artists;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_album'])) {
        $title = trim($_POST['title'] ?? '');
        $artist_id = $_POST['artist_id'] ?? '';
        $file = $_FILES['cover_image'] ?? null;

        if (!$title || !$artist_id) {
            $message = "กรุณากรอกชื่ออัลบัมและเลือกศิลปิน";
        } else {
            $coverFilename = null;
            if ($file && $file['tmp_name']) {
                $uploadDir = 'uploads/images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $coverFilename = basename($file['name']);
                move_uploaded_file($file['tmp_name'], $uploadDir . $coverFilename);
            }
            $stmt = $pdo->prepare("INSERT INTO albums (title, artist_id, cover_image) 
                VALUES (:title, :artist_id, :cover_image)");
            $stmt->execute([
                ':title'=>$title,
                ':artist_id'=>$artist_id,
                ':cover_image'=>$coverFilename
            ]);
            $message = "เพิ่มอัลบัมเรียบร้อยแล้ว!";
        }
    }
}

// เรียกฟังก์ชันตามหน้า
if ($view === 'songs') addSong($pdo, $message);
if ($view === 'artists') addArtist($pdo, $message);
if ($view === 'albums') addAlbum($pdo, $message);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel | Music Again</title>
<link rel="stylesheet" href="css/style-admin.css">
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
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_song'])) include __DIR__ . '/add_song.php';
        if (isset($_POST['add_artist'])) include __DIR__ . '/add_artist.php';
        if (isset($_POST['add_album'])) include __DIR__ . '/add_album.php';
    }
    ?>
</div>

</body>
</html>
<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? 'ผู้ใช้';

// ดึงอัลบั้มทั้งหมด พร้อมชื่อศิลปิน
$albums = $pdo->query("SELECT al.id, al.title AS album_title, al.cover_image, a.name AS artist_name
                    FROM albums al
                    JOIN artists a ON al.artist_id = a.id
                    ORDER BY al.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>อัลบั้มทั้งหมด</title>
<link href="css/style-index.css" rel="stylesheet">
</head>
<body>
<div class="Tap">
    <div class="left">
        <a href="index.php"><img class="Logo" src="images/Logo.png" alt="Logo"></a>
    </div>
    <div class="center"><h1>อัลบั้มทั้งหมด</h1></div>
    <div class="right">
        <div class="dropdown">
            <img class="User" src="images/Lguser.png" alt="User">
            <div class="username-below"><?php echo htmlspecialchars($username); ?></div>
            <div class="dropdown-content">
                <a href="logout.php">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</div>

<div class="Main">
    <!-- พื้นที่ซ้าย: แสดงเพลงที่เลือก -->
    <div class="leftmain">
        <h2>เพลงที่เลือก</h2>
        <div id="player-container">
            <div id="selected-song-info">ยังไม่ได้เลือกเพลง</div>
            <audio id="main-player" controls style="display:none;">
                <source id="audio-source" src="" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        </div>
    </div>
    <div class="centermain">
        <div class="gridcard">
            <?php foreach ($albums as $album): ?>
                <div class="card">
                    <img class="ImgSong" src="uploads/images/<?php echo htmlspecialchars($album['cover_image']); ?>" alt="cover">
                    <h3><?php echo htmlspecialchars($album['album_title']); ?></h3>
                    <p><?php echo htmlspecialchars($album['artist_name']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
            <?php include 'footer.php'; ?>
    </div>
    <!-- พื้นที่ขวา: เมนูผู้ใช้ -->
    <div class="rightmain">
        <h2>เมนูของฉัน</h2>
        <div class="user-panel">
            <?php if ($user_id): ?>
                <a href="index.php" class="user-btn">หน้าหลัก</a>
                <a href="all_songs.php" class="user-btn">เพลงทั้งหมด</a>
                <a href="all_albums.php" class="user-btn">อัลบั้มทั้งหมด</a>
                <a href="all_artists.php" class="user-btn">ศิลปินทั้งหมด</a>
                <a href="favorites.php" class="user-btn">เพลงโปรดของฉัน</a>
                <a href="playlists.php" class="user-btn">เพลย์ลิสต์ของฉัน</a>

                <form action="create_playlist.php" method="POST">
                    <h2>สร้างเพลย์ลิสต์</h2>
                    <input type="text" name="playlist_name" placeholder="ชื่อเพลย์ลิสต์" required>
                    <button type="submit" class="user-btn">สร้างเพลย์ลิสต์</button>
                </form>
            <?php else: ?>
                <p>กรุณาเข้าสู่ระบบ<br>เพื่อใช้งานเมนูนี้</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
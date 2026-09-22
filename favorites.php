<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

// ดึงเพลงโปรดของผู้ใช้
$sql = "SELECT songs.id, songs.title AS song_title, songs.file_path,
            artists.name AS artist_name, artists.image AS artist_image,
            albums.title AS album_title, albums.cover_image AS album_cover
        FROM favorites
        JOIN songs ON favorites.song_id = songs.id
        JOIN artists ON songs.artist_id = artists.id
        JOIN albums ON songs.album_id = albums.id
        WHERE favorites.user_id = :user_id
        ORDER BY favorites.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$favorites = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เพลงโปรดของ <?php echo htmlspecialchars($username); ?></title>
<link href="css/style-index.css" rel="stylesheet">
</head>
<body>

<!-- ========== Navbar ========== -->
<div class="Tap">
    <div class="left">
        <a href="index.php"><img class="Logo" src="images/Logo.png" alt="Logo"></a>
    </div>
    <div class="center"><h3>เพลงโปรดของ <?php echo htmlspecialchars($username); ?></h3></div>
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

    <!-- ===== Center (เพลงโปรด) ===== -->
    <div class="centermain">

        <?php if(!$favorites): ?>
            <p style="text-align:center; margin-top:20px;">คุณยังไม่มีเพลงโปรด</p>
        <?php else: ?>
            <div class="gridcard">
                <?php foreach ($favorites as $song): ?>
                    <div class="card">
                        <!-- ปุ่มดาว -->
                        <button class="fav-btn active" onclick="toggleFavorite(this, <?php echo $song['id']; ?>)">★</button>

                        <!-- รูปอัลบั้ม -->
                        <img class="ImgSong" src="uploads/images/<?php echo htmlspecialchars($song['album_cover']); ?>" alt="Album Cover">

                        <!-- ปุ่มเล่น -->
                        <div class="play-icon" 
                            onclick="playSound(
                                'uploads/songs/<?php echo htmlspecialchars($song['file_path']); ?>',
                                '<?php echo htmlspecialchars($song['song_title']); ?>',
                                '<?php echo htmlspecialchars($song['artist_name']); ?>',
                                'uploads/images/<?php echo htmlspecialchars($song['album_cover']); ?>'
                            )">
                            <img src="images/Lgplay.png" alt="Play">
                        </div>

                        <h3><?php echo htmlspecialchars($song['song_title']); ?></h3>
                        <p><?php echo htmlspecialchars($song['artist_name']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php include 'footer.php'; ?>
    </div>

    <!-- ===== Right ===== -->
    <div class="rightmain">
        <h2>เมนูของฉัน</h2>
        <div class="user-panel">
            <a href="index.php" class="user-btn">หน้าหลัก</a>
            <a href="all_songs.php" class="user-btn">เพลงทั้งหมด</a>
            <a href="all_albums.php" class="user-btn">อัลบั้มทั้งหมด</a>
            <a href="all_artists.php" class="user-btn">ศิลปินทั้งหมด</a>
            <a href="favorites.php" class="user-btn active">เพลงโปรด</a>
            <a href="playlists.php" class="user-btn">เพลย์ลิสต์</a>

            <form action="create_playlist.php" method="POST">
                <h2>สร้างเพลย์ลิสต์</h2>
                <input type="text" name="playlist_name" placeholder="ชื่อเพลย์ลิสต์" required>
                <button type="submit" class="user-btn">สร้างเพลย์ลิสต์</button>
            </form>
        </div>
    </div>
</div>

<script>
function playSound(filePath, title, artist, imagePath) {
    const player = document.getElementById('main-player');
    const source = document.getElementById('audio-source');
    const infoBox = document.getElementById('selected-song-info');

    source.src = filePath;
    player.load();
    player.play();
    player.style.display = 'block';

    infoBox.innerHTML = `
        <img src="${imagePath}" alt="${title}"
            style="width: 80%; border-radius: 10px; margin-top: 10px;">
        <p style="margin-top:10px; font-size:16px;"><strong>${title}</strong></p>
        <p style="color:#aaa; font-size:14px;">${artist}</p>
    `;
}

function toggleFavorite(btn, songId) {
    fetch('toggle_favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'song_id=' + songId
    })
    .then(res => res.text())
    .then(status => {
        if(status === 'added'){
            btn.classList.add('active');
        } else if(status === 'removed'){
            btn.classList.remove('active');
        }
    })
    .catch(err => console.error(err));
}
</script>

</body>
</html>
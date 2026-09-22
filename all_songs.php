<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? 'ผู้ใช้';

// ดึงเพลงทั้งหมด
$songs = $pdo->query("SELECT s.id, s.title, s.file_path, a.name AS artist_name, al.cover_image
                      FROM songs s
                      JOIN artists a ON s.artist_id = a.id
                      JOIN albums al ON s.album_id = al.id
                      ORDER BY s.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เพลงทั้งหมด</title>
<link href="css/style-index.css" rel="stylesheet">
</head>
<body>
<div class="Tap">
    <div class="left">
        <a href="index.php"><img class="Logo" src="images/Logo.png" alt="Logo"></a>
    </div>
    <div class="center"><h1>เพลงทั้งหมด</h1></div>
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
            <?php foreach ($songs as $song): ?>
                <div class="card">
                    <!-- ปุ่มโปรด -->
                    <?php if ($user_id): ?>
                    <?php
                        $isFav = false;
                        $checkFav = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND song_id = ?");
                        $checkFav->execute([$user_id, $song['id']]);
                        $isFav = $checkFav->fetch() ? true : false;
                    ?>
                    <button class="fav-btn <?php echo $isFav ? 'active' : ''; ?>" 
                            onclick="toggleFavorite(this, <?php echo $song['id']; ?>)">
                        ★
                    </button>
                    <?php endif; ?>

                    <!-- ปุ่มเพิ่มเพลย์ลิสต์ -->
                    <?php if ($user_id): ?>
                    <div class="add-playlist-container">
                        <button type="button" class="add-btn" onclick="togglePlaylistDropdown(<?php echo $song['id']; ?>)">+</button>
                        <div class="playlist-dropdown" id="playlist-dropdown-<?php echo $song['id']; ?>">
                            <form action="add_to_playlist.php" method="POST">
                                <input type="hidden" name="song_id" value="<?php echo $song['id']; ?>">
                                <select name="playlist_id" required>
                                    <option value="">เลือกเพลย์ลิสต์</option>
                                    <?php
                                        $pls = $pdo->prepare("SELECT * FROM playlists WHERE user_id = ?");
                                        $pls->execute([$user_id]);
                                        foreach ($pls as $p):
                                    ?>
                                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="submit-btn">เพิ่ม</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- รูปและข้อมูลเพลง -->
                    <img class="ImgSong" src="uploads/images/<?php echo htmlspecialchars($song['cover_image']); ?>" alt="cover">
                    <div class="play-icon" 
                         onclick="playSong(
                            'uploads/songs/<?php echo htmlspecialchars($song['file_path']); ?>', 
                            '<?php echo htmlspecialchars($song['title']); ?>', 
                            '<?php echo htmlspecialchars($song['artist_name']); ?>', 
                            'uploads/images/<?php echo htmlspecialchars($song['cover_image']); ?>'
                    )">
                        <img src="images/Lgplay.png" alt="Play">
                    </div>
                    <h3><?php echo htmlspecialchars($song['title']); ?></h3>
                    <p><?php echo htmlspecialchars($song['artist_name']); ?></p>
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

<script>
function playSong(filePath, title, artist, imagePath) {
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

function togglePlaylistDropdown(id){
    const dropdown = document.getElementById('playlist-dropdown-' + id);

    // ปิด dropdown อื่น ๆ ก่อน
    document.querySelectorAll('.playlist-dropdown').forEach(d => {
        if(d !== dropdown) d.style.display = 'none';
    });

    // เปิด/ปิด dropdown ที่คลิก
    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
}

// ป้องกันไม่ให้คลิก + แล้ว dropdown ปิดเอง
document.querySelectorAll('.add-btn').forEach(btn => {
    btn.addEventListener('click', e => e.stopPropagation());
});

// ซ่อน dropdown ถ้าคลิกนอก
document.addEventListener('click', function(e){
    if(!e.target.closest('.add-playlist-container')){
        document.querySelectorAll('.playlist-dropdown').forEach(d => d.style.display = 'none');
    }
});
</script>
</body>
</html>
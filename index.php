<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;

$search = trim($_GET['search'] ?? '');

if ($search) {
    $sql = "SELECT songs.id, songs.title AS song_title, songs.file_path,
                artists.name AS artist_name, artists.image AS artist_image,
                albums.title AS album_title, albums.cover_image AS album_cover
            FROM songs
            JOIN artists ON songs.artist_id = artists.id
            JOIN albums ON songs.album_id = albums.id
            WHERE songs.title LIKE :search OR artists.name LIKE :search
            ORDER BY songs.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search' => "%$search%"]);
}  else {
    $sql = "SELECT songs.id, songs.title AS song_title, songs.file_path,
                artists.name AS artist_name, artists.image AS artist_image,
                albums.title AS album_title, albums.cover_image AS album_cover
            FROM songs
            JOIN artists ON songs.artist_id = artists.id
            JOIN albums ON songs.album_id = albums.id
            ORDER BY RAND()
            LIMIT 6";
    $stmt = $pdo->query($sql);
}
$songs = $stmt->fetchAll();
// ดึงศิลปินสุ่ม 3 คน
$artists_stmt = $pdo->query("
    SELECT id, name, image 
    FROM artists 
    ORDER BY RAND() 
    LIMIT 3
");
$artists = $artists_stmt->fetchAll();

// ดึงอัลบั้มสุ่ม 3 อัลบั้ม
$albums_stmt = $pdo->query("
    SELECT id, title, cover_image 
    FROM albums 
    ORDER BY RAND() 
    LIMIT 3
");
$albums = $albums_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Music Again</title>
<link href="css/style-index.css" rel="stylesheet">
</head>
<body>

<!-- ===== Navbar ===== -->
<div class="Tap">
    <div class="left">
        <a href="index.php"><img class="Logo" src="images/Logo.png" alt="Logo"></a>
    </div>
    <div class="center">
        <form method="get" action="index.php" style="display:flex;">
            <input class="Insearch" type="text" name="search" placeholder="ค้นหาเพลง..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btnSearch" type="submit"><img src="images/LgSearch.png" alt="ค้นหา"></button>
        </form>
    </div>
    <div class="right">
        <?php if ($user_id): ?>
            <div class="dropdown">
                <img class="User" src="images/Lguser.png" alt="User">
                <div class="username-below"><?php echo htmlspecialchars($username); ?></div>
                <div class="dropdown-content">
                    <a href="logout.php">ออกจากระบบ</a>
                </div>
            </div>
        <?php else: ?>
            <div class="dropdown">
                <img class="User" src="images/Lguser.png" alt="User">
                <div class="username-below">Guest</div>
                <div class="dropdown-content">
                    <a href="register.php">สมัครสมาชิก</a>
                    <a href="login.php">เข้าสู่ระบบ</a>
                </div>
            </div>
        <?php endif; ?>
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

    <!-- พื้นที่กลาง: รายการเพลง -->
    <div class="centermain">
        <h1>เพลงแนะนำ</h1>
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
                    <button
                        class="fav-btn <?php echo $isFav ? 'active' : ''; ?>" 
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
                    <img class="ImgSong" src="uploads/images/<?php echo htmlspecialchars($song['album_cover']); ?>">
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

        <h1>อัลบั้มแนะนำ</h1>
            <div class="gridcard">
            <?php foreach ($albums as $album): ?>
                <div class="card">
                <img class="ImgSong" src="uploads/images/<?php echo htmlspecialchars($album['cover_image']); ?>" alt="<?php echo htmlspecialchars($album['title']); ?>">
                <h3><?php echo htmlspecialchars($album['title']); ?></h3>
                </div>
            <?php endforeach; ?>
            </div>

        <h1>ศิลปินยอดนิยม</h1>
            <div class="gridcard">
            <?php foreach ($artists as $artist): ?>
                <div class="card">
                <img class="ImgSong" src="uploads/images/<?php echo htmlspecialchars($artist['image']); ?>" alt="<?php echo htmlspecialchars($artist['name']); ?>">
                <h3><?php echo htmlspecialchars($artist['name']); ?></h3>
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
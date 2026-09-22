<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'ผู้ใช้';

// ดึงเพลย์ลิสต์ของผู้ใช้
$playlists_stmt = $pdo->prepare("SELECT * FROM playlists WHERE user_id = ? ORDER BY created_at DESC");
$playlists_stmt->execute([$user_id]);
$playlists = $playlists_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เพลย์ลิสต์ของ <?php echo htmlspecialchars($username); ?></title>
<link href="css/style-index.css" rel="stylesheet">
</head>
<body>

<div class="Tap">
    <div class="left">
        <a href="index.php"><img class="Logo" src="images/Logo.png" alt="Logo"></a>
    </div>
    <div class="center"><h3>เพลย์ลิสต์ของ <?php echo htmlspecialchars($username); ?></h3></div>
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
    <div class="leftmain">
        <h2>เพลงที่เลือก</h2>
        <div id="player-container">
            <div id="selected-song-info">ยังไม่ได้เลือกเพลง</div>
            <audio id="main-player" controls style="display:none;">
                <source id="audio-source" src="" type="audio/mpeg">
            </audio>
        </div>
    </div>

    <div class="centermain">
        <?php if(!$playlists): ?>
            <p style="text-align:center; margin-top:20px;">คุณยังไม่มีเพลย์ลิสต์</p>
        <?php else: ?>
            <div class="playlist-grid">
                <?php foreach ($playlists as $pl): ?>
                    <div class="playlist-card" onclick="loadPlaylist(<?php echo $pl['id']; ?>)">
                        <div class="card-buttons">
                            <button class="edit-btn" title="แก้ไขชื่อเพลย์ลิสต์"
                                onclick="event.stopPropagation(); editPlaylist(<?php echo $pl['id']; ?>, '<?php echo htmlspecialchars($pl['name']); ?>')">✎
                            </button>
                            <button class="delete-btn" title="ลบเพลย์ลิสต์"
                                onclick="event.stopPropagation(); deletePlaylist(<?php echo $pl['id']; ?>)">🗑</button>
                        </div>

                        <div class="playlist-name">
                            <?php echo htmlspecialchars($pl['name']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="songs-container" class="songs-grid"></div>
        <?php endif; ?>
        <?php include 'footer.php'; ?>
    </div>

    <div class="rightmain">
        <h2>เมนูของฉัน</h2>
        <div class="user-panel">
            <a href="index.php" class="user-btn">หน้าหลัก</a>
            <a href="all_songs.php" class="user-btn">เพลงทั้งหมด</a>
            <a href="all_albums.php" class="user-btn">อัลบั้มทั้งหมด</a>
            <a href="all_artists.php" class="user-btn">ศิลปินทั้งหมด</a>
            <a href="favorites.php" class="user-btn">เพลงโปรดของฉัน</a>
            <a href="playlists.php" class="user-btn active">เพลย์ลิสต์ของฉัน</a>

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
        <img src="${imagePath}" alt="${title}" style="width:80%; border-radius:10px; margin-top:10px;">
        <p><strong>${title}</strong></p>
        <p style="color:#aaa;">${artist}</p>
    `;
}

function loadPlaylist(id) {
    fetch('load_playlist_songs.php?id=' + id)
        .then(res => res.text())
        .then(html => document.getElementById('songs-container').innerHTML = html);
}

// ลบเพลย์ลิสต์
function deletePlaylist(id) {
    if (!confirm("ต้องการลบเพลย์ลิสต์นี้ใช่ไหม? เพลงทั้งหมดในเพลย์ลิสต์จะถูกลบออกด้วย")) return;
    fetch("delete_playlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "ok") location.reload();
        else alert("เกิดข้อผิดพลาดในการลบเพลย์ลิสต์");
    });
}

// แก้ไขชื่อเพลย์ลิสต์
function editPlaylist(id, oldName) {
    const newName = prompt("กรอกชื่อเพลย์ลิสต์ใหม่:", oldName);
    if (!newName) return;
    fetch("update_playlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id + "&name=" + encodeURIComponent(newName)
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "ok") location.reload();
        else alert("ไม่สามารถเปลี่ยนชื่อได้");
    });
}

// ลบเพลงออกจากเพลย์ลิสต์ (เงียบ ๆ)
function removeSongFromPlaylist(ps_id, playlist_id) {
    fetch("remove_song_from_playlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "ps_id=" + ps_id
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "ok") loadPlaylist(playlist_id);
    });
}
</script>

</body>
</html>
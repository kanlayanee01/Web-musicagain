<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$playlist_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT ps.id AS ps_id, s.id AS song_id, s.title AS song_title, s.file_path,
           a.name AS artist_name, al.cover_image AS album_cover
    FROM playlist_songs ps
    JOIN songs s ON ps.song_id = s.id
    JOIN artists a ON s.artist_id = a.id
    JOIN albums al ON s.album_id = al.id
    WHERE ps.playlist_id = ?
");
$stmt->execute([$playlist_id]);
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="songs-grid">
<?php foreach ($songs as $song): ?>
<div class="song-card">
    <button class="remove-btn"
        onclick="removeSongFromPlaylist(<?php echo $song['ps_id']; ?>, <?php echo $playlist_id; ?>)">×</button>

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

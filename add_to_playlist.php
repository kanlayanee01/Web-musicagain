<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$playlist_id = $_POST['playlist_id'] ?? null;
$song_id = $_POST['song_id'] ?? null;

if ($playlist_id && $song_id) {
    // ตรวจสอบว่าเพลงนี้อยู่ในเพลย์ลิสต์หรือยัง
    $check = $pdo->prepare("SELECT * FROM playlist_songs WHERE playlist_id = ? AND song_id = ?");
    $check->execute([$playlist_id, $song_id]);

    if ($check->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)");
        $stmt->execute([$playlist_id, $song_id]);
    }
}

header("Location: playlists.php");
exit;
?>

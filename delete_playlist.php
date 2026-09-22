<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) exit('no');

$id = $_POST['id'] ?? 0;
$user_id = $_SESSION['user_id'];

if ($id) {
    // ลบเพลงทั้งหมดในเพลย์ลิสต์ก่อน
    $pdo->prepare("DELETE FROM playlist_songs WHERE playlist_id = ?")->execute([$id]);
    // ลบเพลย์ลิสต์เอง
    $pdo->prepare("DELETE FROM playlists WHERE id = ? AND user_id = ?")->execute([$id, $user_id]);
    echo 'ok';
} else {
    echo 'error';
}
?>
<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['playlist_name'] ?? '');

if ($name !== '') {
    $stmt = $pdo->prepare("INSERT INTO playlists (user_id, name, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $name]);
}

header("Location: playlists.php");
exit;
?>
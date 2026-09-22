<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) exit('no');

$id = $_POST['id'] ?? 0;
$name = trim($_POST['name'] ?? '');
$user_id = $_SESSION['user_id'];

if ($id && $name) {
    $stmt = $pdo->prepare("UPDATE playlists SET name = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$name, $id, $user_id]);
    echo 'ok';
} else {
    echo 'error';
}
?>
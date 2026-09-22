<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo 'login_required';
    exit;
}

$user_id = $_SESSION['user_id'];
$song_id = $_POST['song_id'] ?? null;

if (!$song_id) {
    echo 'error';
    exit;
}

// ตรวจสอบว่ามีอยู่แล้วไหม
$stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND song_id = ?");
$stmt->execute([$user_id, $song_id]);

if ($stmt->rowCount() > 0) {
    // ถ้ามีแล้ว ให้ลบออก
    $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND song_id = ?")->execute([$user_id, $song_id]);
    echo 'removed';
} else {
    // ถ้ายังไม่มี ให้เพิ่มเข้า favorites
    $pdo->prepare("INSERT INTO favorites (user_id, song_id) VALUES (?, ?)")->execute([$user_id, $song_id]);
    echo 'added';
}
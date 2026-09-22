<?php
require 'db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password) {
        $message = "กรุณากรอกข้อมูลให้ครบ";
    } else {
        // ตรวจสอบว่ามี username หรือ email ซ้ำไหม
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username'=>$username, ':email'=>$email]);
        if ($stmt->fetch()) {
            $message = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้ว";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute([':username'=>$username, ':email'=>$email, ':password'=>$hash]);
            $message = "สมัครสมาชิกเรียบร้อยแล้ว!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | Music Again</title>
    <link href="css/style-login.css" rel="stylesheet">
</head>
<body>
    <div class="main">
        <div class="left">
            <img src="images/Music.png">
        </div>
        
        <div class="right">
            <div class="boxfrom">
            <a href="index.php"><h1>Music Again</h1></a>
            <form method="post" class="login-form">
                <label for="username">Username:</label>
                <input type="text" name="username" placeholder="username" required>
                <label for="password">Gmail:</label>
                <input type="email" name="email" placeholder="gmail" required>
                <label for="password">Password:</label>
                <input type="password" name="password" placeholder="password" required>
                <button type="submit" class="button">Register</button>
            </form>
            <?php if (!empty($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
                <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <p>มีบัญชีแล้วใช่ไหม? <a href="login.php">เข้าสู่ระบบ</a></p>
            </div>
        </div>
    </div>
</body>
</html>
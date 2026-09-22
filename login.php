<?php
session_start();
require 'db.php';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // เก็บข้อมูล session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // ✅ ใช้ role แทน is_admin

            // แยกหน้าเข้าตาม role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error = "กรุณากรอกข้อมูลให้ครบ";
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
                    <label for="password">Password:</label>
                    <input type="password" name="password" placeholder="password" required>
                    <button type="submit" class="button">Login</button>
                </form>
                <?php if (!empty($error)): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <?php if (!empty($message)): ?>
                    <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>

                <p>ยังไม่มีบัญชีใช่ไหม? <a href="register.php">สมัครสมาชิก</a></p>
            </div>
        </div>
    </div>
</body>
</html>
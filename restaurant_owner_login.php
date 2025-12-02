<?php
// restaurant_owner_login.php
session_start();
include "includes/db.php";

if (isset($_SESSION['owner_id'])) {
    header("Location: restaurant panel/dashboard.php");
    exit();
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $msg = "Enter username and password.";
    } else {
        $stmt = $conn->prepare("SELECT owner_id, username, password, restaurant_id FROM restaurant_owners WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row['password'])) {
            // load restaurant name
            $r = $conn->prepare("SELECT name FROM restaurants WHERE restaurant_id = ?");
            $r->bind_param("i", $row['restaurant_id']);
            $r->execute();
            $rest = $r->get_result()->fetch_assoc();
            $r->close();

            $_SESSION['owner_id'] = $row['owner_id'];
            $_SESSION['owner_username'] = $row['username'];
            $_SESSION['restaurant_id'] = $row['restaurant_id'];
            $_SESSION['restaurant_name'] = $rest['name'] ?? '';

            header("Location: restaurant panel/dashboard.php");
            exit();
        } else {
            $msg = "Invalid credentials.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Restaurant Owner Login - MealHub</title>
<style>
body{font-family:Poppins,Arial,sans-serif;background:#2a65ff;margin:0}
.container{max-width:420px;margin:80px auto;background:#fff;padding:28px;border-radius:10px;box-shadow:0 8px 26px rgba(0,0,0,0.2)}
h2{text-align:center;margin:0 0 12px 0}
.input{width:100%;padding:10px;margin-top:10px;border-radius:6px;border:1px solid #ddd}
.btn{width:100%;padding:10px;margin-top:12px;background:#007bff;color:#fff;border:none;border-radius:6px;cursor:pointer}
.link{display:block;text-align:center;margin-top:8px}
.msg{background:#ffeef0;color:#d93025;padding:8px;border-radius:6px}
</style>
</head>
<body>
<div class="container">
  <h2>Restaurant Owner Login</h2>
  <?php if($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
  <form method="post">
    <input class="input" type="text" name="username" placeholder="Username" required>
    <input class="input" type="password" name="password" placeholder="Password" required>
    <button class="btn" type="submit">Login</button>
  </form>
  <div class="link">
    <a href="index.php">Back to Customer Login</a>
  </div>
</div>
</body>
</html>

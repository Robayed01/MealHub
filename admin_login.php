<?php
// admin_login.php - fixed credentials
session_start();

// Hardcoded admin credentials (change before production)
$admin_username = "admin";
$admin_password = "admin123";

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $admin_username && $password === $admin_password) {
        // set admin session (simple)
        $_SESSION['admin'] = $admin_username;
        // redirect to admin dashboard
        header("Location: admin/dashboard.php");
        exit();
    } else {
        $msg = "Invalid admin username or password.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Login - MealHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  :root{--accent:#ff6b6b;--accent-dark:#e04848;--glass:#fff;}
  html,body{height:100%;margin:0}
  body{
    font-family:"Poppins",Arial,sans-serif;
    display:flex;align-items:center;justify-content:center;
    background-image: url('assets/images/online-food-delivery-amazon-tw.jpg');
    background-size:cover;background-position:center;
    padding:20px;
  }
  .overlay{position:fixed;inset:0;background:linear-gradient(180deg, rgba(0,0,0,0.45), rgba(0,0,0,0.6));z-index:0}
  .card{position:relative;z-index:1;width:360px;background:rgba(255,255,255,0.94);padding:32px;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,0.35);text-align:center}
  h2{margin:0 0 12px 0;color:#222}
  p.lead{margin:0 0 18px 0;color:#333;opacity:0.9}
  .msg{padding:10px;border-radius:8px;margin-bottom:12px;color:#ff4d4f;background:#ffecec;display:block;text-align:left}
  .input{width:100%;padding:12px;border-radius:10px;border:1px solid #e6e6e6;margin:8px 0}
  .input:focus{outline:none;box-shadow:0 6px 18px rgba(255,107,107,0.12);border-color:var(--accent)}
  .btn{width:100%;padding:12px;border-radius:10px;border:none;background:var(--accent);color:#fff;font-weight:600;cursor:pointer}
  .btn:hover{background:var(--accent-dark)}
  .links{margin-top:12px;font-size:14px}
  .links a{color:var(--accent);text-decoration:none;font-weight:600}
  @media(max-width:420px){.card{width:100%}}
</style>
</head>
<body>
  <div class="overlay" aria-hidden="true"></div>

  <div class="card" role="main">
    <h2>Admin Login</h2>
    <p class="lead">Enter admin credentials</p>

    <?php if (!empty($msg)): ?>
      <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <input class="input" type="text" name="username" placeholder="Admin username" required>
      <input class="input" type="password" name="password" placeholder="Password" required>
      <button class="btn" type="submit" name="login">Login</button>
    </form>

    <div class="links">
      <a href="index.php">Back to Customer Login</a>
    </div>
  </div>
</body>
</html>

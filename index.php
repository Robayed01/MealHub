<?php
// index.php - Customer login
session_start();
include "includes/db.php";

// If already logged in as customer, go to menu
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
    header("Location: customer/menu.php");
    exit();
}

$msg = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $msg = "Please fill in both fields.";
        $msg_type = "error";
    } else {
        $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ? AND role = 'customer'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['role'] = $row['role'];
                header("Location: customer/menu.php");
                exit();
            } else {
                $msg = "Invalid password.";
                $msg_type = "error";
            }
        } else {
            $msg = "Email not registered. Please sign up.";
            $msg_type = "error";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Customer Login - MealHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  :root{
    --glass-bg: rgba(255,255,255,0.9);
    --accent: #007bff;
    --accent-dark: #0056b3;
    --success: #28a745;
    --error: #ff4d4f;
    font-family: "Poppins", Arial, sans-serif;
  }
  html,body{height:100%;margin:0}
  body{
    background-image: url('assets/images/online-food-delivery-amazon-tw.jpg');
    background-size: cover;
    background-position: center;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
  }
  /* dark overlay */
  .overlay{
    position:fixed;inset:0;background:linear-gradient(180deg, rgba(0,0,0,0.35), rgba(0,0,0,0.5));
    z-index:0;
  }
  .card{
    position:relative; z-index:1;
    width:380px;
    background: var(--glass-bg);
    border-radius:14px;
    padding:30px;
    box-shadow:0 12px 40px rgba(0,0,0,0.35);
    backdrop-filter: blur(6px);
    text-align:center;
    animation:pop .45s ease;
  }
  @keyframes pop{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:none}}
  h1{margin:0 0 10px 0;font-size:22px;color:#222}
  p.lead{margin:0 0 16px 0;color:#333;opacity:0.85}
  .msg{padding:10px;border-radius:8px;margin-bottom:12px;text-align:left;display:none}
  .msg.show{display:block}
  .msg.error{background:#ffecec;color:var(--error);border:1px solid rgba(255,77,79,0.12)}
  .msg.success{background:#ecfff0;color:var(--success);border:1px solid rgba(40,167,69,0.12)}
  .input{width:100%;padding:12px;border-radius:10px;border:1px solid #e6e6e6;margin:8px 0;font-size:15px}
  .input:focus{outline:none;box-shadow:0 6px 18px rgba(0,123,255,0.12);border-color:var(--accent)}
  .btn{width:100%;padding:12px;border-radius:10px;border:none;background:var(--accent);color:white;font-weight:600;cursor:pointer;margin-top:8px}
  .btn:hover{background:var(--accent-dark)}
  .links{margin-top:14px;color:#222;opacity:0.9;font-size:14px}
  .links a{color:var(--accent);text-decoration:none;font-weight:600}
  .small-note{font-size:13px;margin-top:10px;color:#222;opacity:0.8}
  @media(max-width:420px){ .card{width:100%;padding:22px} }
</style>
</head>
<body>
  <div class="overlay" aria-hidden="true"></div>

  <div class="card" role="main" aria-labelledby="loginTitle">
    <h1 id="loginTitle">Welcome to MealHub</h1>
    <p class="lead">Login to order delicious food</p>

    <?php if ($msg !== ""): ?>
      <div class="msg <?php echo ($msg_type==='error')? 'error show' : 'success show'; ?>">
        <?php echo htmlspecialchars($msg, ENT_QUOTES|ENT_HTML5); ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <input class="input" type="email" name="email" placeholder="Email address" required>
      <input class="input" type="password" name="password" placeholder="Password" required>
      <button class="btn" type="submit" name="login">Login</button>
    </form>

    <div class="links">
      <div>Don't have an account? <a href="register.php">Sign up</a></div>
      <div style="margin-top:8px">Admin? <a href="admin_login.php">Login here</a></div>
    </div>

    
  </div>
</body>
</html>

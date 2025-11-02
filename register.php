<?php
// register.php
include "includes/db.php";
session_start();

$msg = "";
$msg_type = "";

function is_valid_phone($p){
    return preg_match('/^[0-9+\-\s()]{7,20}$/', $p);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if ($name==='' || $email==='' || $phone==='' || $password==='' || $confirm==='') {
        $msg = "Please fill in all fields.";
        $msg_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Enter a valid email address.";
        $msg_type = "error";
    } elseif (!is_valid_phone($phone)) {
        $msg = "Enter a valid phone number.";
        $msg_type = "error";
    } elseif ($password !== $confirm) {
        $msg = "Passwords do not match.";
        $msg_type = "error";
    } else {
        // check duplicate
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        $res = $chk->get_result();
        if ($res && $res->num_rows > 0) {
            $msg = "Email already registered. Please login.";
            $msg_type = "error";
            $chk->close();
        } else {
            $chk->close();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')");
            $ins->bind_param("ssss", $name, $email, $phone, $hash);
            if ($ins->execute()) {
                $msg = "Registration successful. Redirecting to login...";
                $msg_type = "success";
                header("Refresh:2; url=index.php");
            } else {
                $msg = "Registration failed. Please try again.";
                $msg_type = "error";
            }
            $ins->close();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register - MealHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  :root{--accent:#007bff;--accent-dark:#0056b3}
  html,body{height:100%;margin:0}
  body{
    font-family:"Poppins",Arial,sans-serif;
    background-image:url('assets/images/online-food-delivery-amazon-tw.jpg');
    background-size:cover;background-position:center;
    display:flex;align-items:center;justify-content:center;padding:20px;
  }
  .overlay{position:fixed;inset:0;background:linear-gradient(180deg, rgba(0,0,0,0.35), rgba(0,0,0,0.55));z-index:0}
  .card{position:relative;z-index:1;width:420px;background:rgba(255,255,255,0.95);padding:26px;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,0.35);text-align:center}
  h2{margin:0 0 10px 0}
  .msg{padding:10px;border-radius:8px;margin-bottom:12px;text-align:left;display:block}
  .msg.error{background:#ffecec;color:#ff4d4f;border:1px solid rgba(255,77,79,0.12)}
  .msg.success{background:#ecfff0;color:#28a745;border:1px solid rgba(40,167,69,0.12)}
  .input{width:100%;padding:12px;border-radius:10px;border:1px solid #e6e6e6;margin:8px 0}
  .input:focus{outline:none;box-shadow:0 6px 18px rgba(0,123,255,0.12);border-color:var(--accent)}
  .btn{width:100%;padding:12px;border-radius:10px;border:none;background:var(--accent);color:#fff;font-weight:600;cursor:pointer}
  .btn:hover{background:var(--accent-dark)}
  .links{margin-top:12px}
  .links a{color:var(--accent);text-decoration:none;font-weight:600}
  @media(max-width:460px){.card{width:100%;padding:18px}}
</style>
</head>
<body>
  <div class="overlay" aria-hidden="true"></div>

  <div class="card" role="main">
    <h2>Create an account</h2>

    <?php if ($msg !== ""): ?>
      <div class="msg <?php echo ($msg_type==='error') ? 'error' : 'success'; ?>">
        <?php echo htmlspecialchars($msg, ENT_QUOTES|ENT_HTML5); ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <input class="input" type="text" name="name" placeholder="Full name" required value="<?php echo isset($_POST['name'])?htmlspecialchars($_POST['name']):''; ?>">
      <input class="input" type="email" name="email" placeholder="Email address" required value="<?php echo isset($_POST['email'])?htmlspecialchars($_POST['email']):''; ?>">
      <input class="input" type="text" name="phone" placeholder="Phone number" required value="<?php echo isset($_POST['phone'])?htmlspecialchars($_POST['phone']):''; ?>">
      <input class="input" type="password" name="password" placeholder="Create password" required>
      <input class="input" type="password" name="confirm" placeholder="Confirm password" required>
      <label style="display:block;text-align:left;font-size:13px;margin:8px 0"><input type="checkbox" required> I accept terms &amp; conditions</label>
      <button class="btn" type="submit" name="register">Register Now</button>
    </form>

    <div class="links">Already registered? <a href="index.php">Login here</a></div>
  </div>
</body>
</html>

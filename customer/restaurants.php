<?php
session_start();
include "../includes/db.php";
if(!isset($_SESSION['user_id'])){ header("Location: ../index.php"); exit(); }

$res = $conn->query("SELECT * FROM restaurants ORDER BY name");
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Select Restaurant</title>
<style>
body{font-family:Poppins,Arial,sans-serif;margin:0;background:#f8f9fa}
.header{background:#007bff;color:#fff;padding:15px 30px;display:flex;justify-content:space-between;align-items:center}
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;padding:30px}
.card{background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.1);overflow:hidden;text-align:center;transition:.3s}
.card:hover{transform:translateY(-3px)}
.card img{width:100%;height:180px;object-fit:cover}
.card h3{margin:10px 0 5px}
.card p{padding:0 15px 10px;color:#555;font-size:14px}
a.btn{display:inline-block;margin-bottom:15px;background:#007bff;color:#fff;padding:8px 14px;border-radius:6px;text-decoration:none}
a.btn:hover{background:#0056b3}
</style>
</head>
<body>
<div class="header">
  <div><strong>MealHub</strong> | Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></div>
  <div><a href="../logout.php" style="color:white">Logout</a></div>
</div>
<div class="cards">
  <?php while($r=$res->fetch_assoc()): ?>
    <div class="card">
      <img src="../assets/images/<?php echo htmlspecialchars($r['image']); ?>" alt="">
      <h3><?php echo htmlspecialchars($r['name']); ?></h3>
      <p><?php echo htmlspecialchars($r['description']); ?></p>
      <a class="btn" href="menu.php?rid=<?php echo $r['restaurant_id']; ?>">View Menu</a>
    </div>
  <?php endwhile; ?>
</div>
</body>
</html>

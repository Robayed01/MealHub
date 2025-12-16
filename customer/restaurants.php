<?php
// customer/restaurants.php
session_start();
include "../includes/db.php";
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$res = $conn->query("SELECT * FROM restaurants ORDER BY name");
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Select Restaurant</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body {
      font-family: "Poppins", sans-serif;
      margin: 0;
      background: #C7CFB7
    }

    .container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 20px
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 20px
    }

    .card {
      background: #F7F7E8;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      text-align: center;
      transition: .3s
    }

    .card:hover {
      transform: translateY(-3px)
    }

    .card img {
      width: 100%;
      height: 180px;
      object-fit: cover
    }

    .card h3 {
      margin: 10px 0 5px
    }

    .card p {
      padding: 0 15px 10px;
      color: #555;
      font-size: 14px
    }

    a.btn {
      display: inline-block;
      margin-bottom: 15px;
      background: #557174;
      color: #fff;
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none
    }

    a.btn:hover {
      background: #1F2937
    }

    .back-btn {
      background: #557174;
      display: inline-block;
      padding: 10px 16px;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      margin-bottom: 20px
    }

    .header {
      background: #557174;
      font-weight: bold;
      color: #1F2937;
      padding: 15px;
      font-size: 20px
    }
  </style>
</head>

<body>
  <div class="header">MealHub - Restaurants</div>

  <div class="container">
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

    <h2>Restaurants</h2>
    <div class="cards">
      <?php while ($r = $res->fetch_assoc()): ?>
        <div class="card">
          <img src="../assets/images/<?php echo htmlspecialchars($r['image']); ?>" alt="">
          <h3><?php echo htmlspecialchars($r['name']); ?></h3>
          <p><?php echo htmlspecialchars($r['description']); ?></p>
          <a class="btn" href="menu.php?rid=<?php echo $r['restaurant_id']; ?>">View Menu</a>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

</body>

</html>
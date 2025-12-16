<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$rid = intval($_GET['rid'] ?? 0);
if ($rid <= 0) {
  header("Location: restaurants.php");
  exit();
}

// fetch restaurant info
$rest = $conn->prepare("SELECT * FROM restaurants WHERE restaurant_id = ?");
$rest->bind_param("i", $rid);
$rest->execute();
$rinfo = $rest->get_result()->fetch_assoc();
if (!$rinfo) {
  header("Location: restaurants.php");
  exit();
}

// categories for this restaurant
$catq = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY category_name");
$catq->bind_param("i", $rid);
$catq->execute();
$cats = $catq->get_result();

$category = intval($_GET['category'] ?? 0);
$search = trim($_GET['search'] ?? '');

// build food items query for this restaurant
$sql = "SELECT f.*, c.category_name FROM food_items f 
        LEFT JOIN categories c ON f.category_id = c.category_id
        WHERE f.restaurant_id = ?";
$types = "i";
$params = [$rid];

if ($category > 0) {
  $sql .= " AND f.category_id = ?";
  $types .= "i";
  $params[] = $category;
}
if ($search !== '') {
  $sql .= " AND f.name LIKE ?";
  $types .= "s";
  $params[] = "%$search%";
}
$sql .= " ORDER BY f.name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$foods = $stmt->get_result();

// get total items in cart (global for user)
$cntStmt = $conn->prepare("SELECT SUM(quantity) as total_qty FROM cart WHERE user_id = ?");
$cntStmt->bind_param("i", $user_id);
$cntStmt->execute();
$cntRes = $cntStmt->get_result()->fetch_assoc();
$cartCount = (int) ($cntRes['total_qty'] ?? 0);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($rinfo['name']); ?> - Menu</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* basic modern styling */
    body {
      font-family: Inter, system-ui, Arial, sans-serif;
      margin: 0;
      background: #C7CFB7
    }

    .header {
      background: #557174;
      color: #1F2937;
      font-weight: bold;
      padding: 14px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center
    }

    .header a {
      color: white;
      text-decoration: none;
      margin-left: 10px
    }

    .container {
      padding: 20px;
      max-width: 1100px;
      margin: 0 auto
    }

    .restaurant {
      display: flex;
      align-items: center;
      gap: 18px;
      margin-bottom: 18px
    }

    .restaurant img {
      width: 92px;
      height: 92px;
      object-fit: cover;
      border-radius: 10px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12)
    }

    .searchbar {
      display: flex;
      gap: 8px;
      margin-bottom: 18px
    }

    .input,
    select {
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #dfe6ef
    }

    .btn {
      padding: 10px 14px;
      border-radius: 8px;
      border: none;
      background: #557174;
      color: #fff;
      cursor: pointer
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 16px
    }

    .card {
      background: #F7F7E8;
      padding: 12px;
      border-radius: 10px;
      box-shadow: 0 6px 18px rgba(16, 24, 40, 0.06);
      text-align: center
    }

    .card img {
      width: 100%;
      height: 140px;
      object-fit: cover;
      border-radius: 8px
    }

    .card h3 {
      margin: 10px 0 6px;
      font-size: 16px
    }

    .card p.price {
      font-weight: 900;
      color: #557174;
      margin: 6px 0
    }

    .card form {
      margin-top: 8px
    }

    .smalllinks {
      font-size: 13px;
      color: #eef5ff
    }

    @media(max-width:520px) {
      .restaurant {
        flex-direction: column;
        align-items: flex-start
      }

      .restaurant img {
        width: 80px;
        height: 80px
      }
    }
  </style>
  <style>
    .header {
      background: #557174;
      color: #1F2937;
      padding: 15px;
      font-size: 20px;
      font-family: Poppins;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  </style>
</head>

<body>
  <div class="header">
    <div>MealHub - Menu</div>
    <div>
      <a href="cart.php" style="color:white;text-decoration:none">🛒 Cart (<?php echo $cartCount; ?>)</a>
    </div>
  </div>

  <div class="container">
    <div class="restaurant">
      <img src="../assets/images/<?php echo htmlspecialchars($rinfo['image']); ?>" alt="">
      <div>
        <h2 style="margin:0"><?php echo htmlspecialchars($rinfo['name']); ?></h2>
        <p style="margin:6px 0;color:#555"><?php echo htmlspecialchars($rinfo['description']); ?></p>
        <div class="smalllinks"><a href="restaurants.php">← Back to Restaurants</a></div>
      </div>
    </div>

    <form method="get" class="searchbar" style="align-items:center">
      <input type="hidden" name="rid" value="<?php echo $rid; ?>">
      <input class="input" type="text" name="search" placeholder="Search menu..."
        value="<?php echo htmlspecialchars($search); ?>">
      <select name="category" class="input">
        <option value="0">All Categories</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?php echo $c['category_id']; ?>" <?php if ($category == $c['category_id'])
               echo "selected"; ?>>
            <?php echo htmlspecialchars($c['category_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button class="btn" type="submit">Filter</button>
    </form>

    <div class="grid">
      <?php while ($f = $foods->fetch_assoc()): ?>
        <div class="card">
          <img src="../assets/images/<?php echo htmlspecialchars($f['image']); ?>" alt="">
          <h3><?php echo htmlspecialchars($f['name']); ?></h3>
          <p class="price">Tk<?php echo number_format($f['price'], 2); ?></p>
          <form method="post" action="add_to_cart.php">
            <input type="hidden" name="food_id" value="<?php echo (int) $f['food_id']; ?>">
            <button class="btn" type="submit">Add to cart</button>
          </form>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>

</html>
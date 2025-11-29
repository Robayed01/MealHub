<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_login.php");
    exit();
}
include "../includes/db.php";

// -----------------------------------------------------------
// PAGE 1 — Show restaurant cards if restaurant_id is NOT set
// -----------------------------------------------------------
if (!isset($_GET['restaurant_id'])) {

    $restaurants = $conn->query("SELECT * FROM restaurants ORDER BY name ASC");
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Manage Food Items</title>
        <style>
            body { font-family: Poppins, sans-serif; background: #f2f4f8; margin: 0; }
            .container { max-width: 1200px; margin: 40px auto; }
            .title { font-size: 28px; font-weight: 600; margin-bottom: 20px; }
            .card {
                background: #fff;
                width: 300px;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
                margin: 20px;
                display: inline-block;
                vertical-align: top;
                text-align: center;
            }
            .card img { width: 100%; height: 180px; object-fit: cover; }
            .card h3 { margin-top: 14px; }
            .btn {
                display: inline-block;
                padding: 10px 18px;
                background: #007bff;
                color: white;
                border-radius: 8px;
                margin: 10px 0 20px 0;
                text-decoration: none;
            }
            .back-btn {
                display: inline-block;
                padding: 10px 18px;
                background: #6c757d;
                color: white;
                border-radius: 8px;
                text-decoration: none;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>

    <div class="container">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        <div class="title">Select Restaurant to Manage Food Items</div>

        <?php while($r = $restaurants->fetch_assoc()): ?>
            <div class="card">
                <img src="../assets/images/<?php echo $r['image']; ?>">
                <h3><?php echo $r['name']; ?></h3>
                <p style="padding:0 10px;"><?php echo $r['description']; ?></p>
                <a class="btn" href="food_items.php?restaurant_id=<?php echo $r['restaurant_id']; ?>">Manage Menu</a>
            </div>
        <?php endwhile; ?>
    </div>

    </body>
    </html>
    <?php
    exit;
}



// -----------------------------------------------------------
// PAGE 2 — Manage food items for a specific restaurant
// -----------------------------------------------------------

$restaurant_id = intval($_GET['restaurant_id']);
$msg = "";

// ===========================================================
// FORCE DELETE FOOD (EVEN IF USED IN ORDERS)
// ===========================================================
if (isset($_POST['delete_id'])) {
    $food_id = intval($_POST['delete_id']);

    // delete order items first
    $conn->query("DELETE FROM order_items WHERE food_id = $food_id");

    // delete image
    $img = $conn->query("SELECT image FROM food_items WHERE food_id=$food_id")->fetch_assoc();
    if ($img && !empty($img['image'])) {
        @unlink("../assets/images/" . $img['image']);
    }

    // delete food
    $conn->query("DELETE FROM food_items WHERE food_id=$food_id");

    $msg = "Food item deleted successfully!";
}



// ===========================================================
// ADD FOOD ITEM
// ===========================================================
if (isset($_POST['save_food'])) {

    $category_id = intval($_POST['category_id']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $imageName = "";

    // upload image
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "../assets/images/" . $imageName);
    }

    $stmt = $conn->prepare("INSERT INTO food_items (restaurant_id, category_id, name, price, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $restaurant_id, $category_id, $name, $price, $imageName);
    $stmt->execute();

    $msg = "Food item added successfully!";
}



// Load restaurant
$rest = $conn->query("SELECT * FROM restaurants WHERE restaurant_id=$restaurant_id")->fetch_assoc();

// Load categories
$categories = $conn->query("SELECT * FROM categories WHERE restaurant_id=$restaurant_id");

// Load food items
$items = $conn->query("SELECT * FROM food_items WHERE restaurant_id=$restaurant_id ORDER BY food_id DESC");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Food Items</title>
    <style>
        body { font-family: Poppins, sans-serif; background: #f2f4f8; margin: 0; }
        .container { max-width: 1100px; margin: 30px auto; }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; }
        .btn { padding: 10px 14px; border-radius: 8px; background: #007bff; color: white; text-decoration: none; }
        .btn-danger { background: #dc3545; color: white; padding: 8px 14px; border-radius: 8px; border: none; }
        .thumb { width: 70px; height: 55px; object-fit: cover; border-radius: 6px; }
        input, select { width: 95%; padding: 10px; margin-bottom: 10px; border-radius: 6px; border:1px solid #ccc; }
        .back-btn { padding: 10px 18px; background:#6c757d; color:white; border-radius:8px; text-decoration:none; }
    </style>
</head>
<body>

<div class="container">

    <a href="food_items.php" class="back-btn">← Back to Food Items</a>
    <h2>Manage Menu — <?php echo $rest['name']; ?></h2>

    <?php if($msg): ?>
        <div class="card" style="background:#eaf4ff;color:#007bff;font-weight:bold;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>


    <!-- ADD FOOD FORM -->
    <div class="card">
        <h3>Add Food Item</h3>

        <form method="POST" enctype="multipart/form-data">
            <label>Category</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while($c = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $c['category_id']; ?>"><?php echo $c['category_name']; ?></option>
                <?php endwhile; ?>
            </select>

            <input type="text" name="name" placeholder="Food Name" required>
            <input type="text" name="price" placeholder="Price" required>
            <input type="file" name="image">

            <button class="btn" name="save_food">Save</button>
        </form>
    </div>


    <!-- FOOD LIST -->
    <div class="card">
        <h3>Food Items</h3>

        <table>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Delete</th>
            </tr>

            <?php while($f = $items->fetch_assoc()): ?>
                <tr>
                    <td><img class="thumb" src="../assets/images/<?php echo $f['image']; ?>"></td>
                    <td><?php echo $f['name']; ?></td>
                    <td><?php echo $f['price']; ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this food?');">
                            <input type="hidden" name="delete_id" value="<?php echo $f['food_id']; ?>">
                            <button class="btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>

        </table>
    </div>

</div>

</body>
</html>

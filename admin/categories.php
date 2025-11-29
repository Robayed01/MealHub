<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_login.php");
    exit();
}
include "../includes/db.php";

$msg = "";

// Handle add / edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $id = intval($_POST['id'] ?? 0);
    $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
    $category_name = trim($_POST['category_name'] ?? '');

    if ($restaurant_id <= 0 || $category_name === '') {
        $msg = "Please select a restaurant and give a category name.";
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET category_name = ?, restaurant_id = ? WHERE category_id = ?");
            $stmt->bind_param("sii", $category_name, $restaurant_id, $id);
            $stmt->execute();
            $msg = "Category updated.";
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (category_name, restaurant_id) VALUES (?, ?)");
            $stmt->bind_param("si", $category_name, $restaurant_id);
            $stmt->execute();
            $msg = "Category added.";
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);

    // Check if any food items use this category
    $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM food_items WHERE category_id = ?");
    $chk->bind_param("i", $del_id);
    $chk->execute();
    $cnt = $chk->get_result()->fetch_assoc()['cnt'] ?? 0;

    if ($cnt > 0) {
        $msg = "Cannot delete category: there are food items assigned to this category. Reassign or remove them first.";
    } else {
        $del = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $del->bind_param("i", $del_id);
        $del->execute();
        $msg = "Category deleted.";
    }
}

// Load ALL categories (NO FILTER)
$categories = $conn->query("
    SELECT category_id, category_name, restaurant_id 
    FROM categories 
    ORDER BY category_name
");

// Load edit category
$edit = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $st = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $st->bind_param("i", $eid);
    $st->execute();
    $edit = $st->get_result()->fetch_assoc();
    $st->close();
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Categories - Admin</title>
<style>
body{font-family:Poppins,Arial,sans-serif;background:#f5f7fb;margin:0}
.header{background:#007bff;color:#fff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center}
.container{max-width:1000px;margin:18px auto;padding:16px}
.card{background:#fff;border-radius:10px;padding:16px;box-shadow:0 6px 18px rgba(16,24,40,0.05);margin-bottom:14px}
.input,select{width:100%;padding:10px;border-radius:8px;border:1px solid #e6ecf2;margin-top:8px}
.btn{display:inline-block;padding:10px 14px;border-radius:8px;border:none;background:#007bff;color:#fff;cursor:pointer}
.btn-danger{background:#dc3545}
.small{font-size:13px;color:#666}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
.back{background:#6c757d}
.back-btn { padding: 10px 18px; background:#6c757d; color:white; border-radius:8px; text-decoration:none}
</style>
</head>
<body>
<div class="header">
  <div><strong>MealHub Admin</strong></div>
</div>

<div class="container">
<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
<br><br>

  <?php if($msg): ?>
    <div class="card" style="background:#e8f3ff;color:#007bff">
      <?php echo htmlspecialchars($msg); ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <h3><?php echo $edit ? 'Edit Category' : 'Add Category'; ?></h3>

    <form method="post">
      <label class="small">Restaurant</label>
      <select name="restaurant_id" required>
        <option value="">-- Select restaurant --</option>
        <?php
        $res_rest = $conn->query("SELECT restaurant_id, name FROM restaurants ORDER BY name");
        while ($r = $res_rest->fetch_assoc()):
        ?>
          <option value="<?php echo $r['restaurant_id']; ?>" 
            <?php if($edit && $edit['restaurant_id']==$r['restaurant_id']) echo 'selected'; ?>>
            <?php echo htmlspecialchars($r['name']); ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label class="small">Category name</label>
      <input class="input" type="text" name="category_name"
             value="<?php echo $edit ? htmlspecialchars($edit['category_name']) : ''; ?>" required>

      <input type="hidden" name="id" value="<?php echo $edit ? intval($edit['category_id']) : 0; ?>">
      
      <div style="margin-top:10px">
        <button class="btn" name="save_category" type="submit">
          <?php echo $edit ? 'Update' : 'Add'; ?>
        </button>
        <?php if($edit): ?>
          <a class="btn back" href="categories.php" style="margin-left:8px">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="card">
    <h3>Categories</h3>

    <table class="table">
      <thead>
        <tr><th>#</th><th>Restaurant</th><th>Category</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php
        $idx = 1;
        while ($c = $categories->fetch_assoc()):
          $st = $conn->prepare("SELECT name FROM restaurants WHERE restaurant_id = ?");
          $st->bind_param("i", $c['restaurant_id']);
          $st->execute();
          $restname = $st->get_result()->fetch_assoc()['name'] ?? '';
          $st->close();
        ?>
        <tr>
          <td><?php echo $idx++; ?></td>
          <td><?php echo htmlspecialchars($restname); ?></td>
          <td><?php echo htmlspecialchars($c['category_name']); ?></td>
          <td>
            <a class="btn" href="categories.php?edit=<?php echo $c['category_id']; ?>">Edit</a>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete this category?');">
              <input type="hidden" name="delete_id" value="<?php echo $c['category_id']; ?>">
              <button class="btn btn-danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

  </div>

</div>
</body>
</html>

<?php
// restaurant_panel/categories.php (owner can add/edit/delete categories for their restaurant)
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: ../restaurant_owner_login.php");
    exit();
}
include "../includes/db.php";

$rid = intval($_SESSION['restaurant_id']);
$msg = "";

// Save (add or update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_cat'])) {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['category_name'] ?? '');
    if ($name === '') {
        $msg = "Category name required.";
    } else {
        if ($id > 0) {
            // ensure category belongs to this restaurant
            $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM categories WHERE category_id = ? AND restaurant_id = ?");
            $chk->bind_param("ii", $id, $rid);
            $chk->execute();
            $cnt = $chk->get_result()->fetch_assoc()['cnt'] ?? 0;
            if ($cnt == 0) {
                $msg = "Not allowed to edit this category.";
            } else {
                $up = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
                $up->bind_param("si", $name, $id);
                $up->execute();
                $msg = "Category updated.";
            }
        } else {
            $ins = $conn->prepare("INSERT INTO categories (category_name, restaurant_id) VALUES (?, ?)");
            $ins->bind_param("si", $name, $rid);
            $ins->execute();
            $msg = "Category added.";
        }
    }
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del = intval($_POST['delete_id']);
    // check ownership
    $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM categories WHERE category_id = ? AND restaurant_id = ?");
    $chk->bind_param("ii", $del, $rid);
    $chk->execute();
    $cnt = $chk->get_result()->fetch_assoc()['cnt'] ?? 0;
    if ($cnt == 0) {
        $msg = "Not allowed.";
    } else {
        // check food usage
        $u = $conn->prepare("SELECT COUNT(*) AS cnt FROM food_items WHERE category_id = ?");
        $u->bind_param("i", $del);
        $u->execute();
        $used = $u->get_result()->fetch_assoc()['cnt'] ?? 0;
        if ($used > 0) $msg = "Cannot delete: some food items use this category.";
        else {
            $d = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
            $d->bind_param("i", $del);
            $d->execute();
            $msg = "Category deleted.";
        }
    }
}

// load edit target
$edit = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $s = $conn->prepare("SELECT * FROM categories WHERE category_id = ? AND restaurant_id = ?");
    $s->bind_param("ii", $eid, $rid);
    $s->execute();
    $edit = $s->get_result()->fetch_assoc();
    $s->close();
}

// load categories
$cats = $conn->prepare("SELECT category_id, category_name FROM categories WHERE restaurant_id = ? ORDER BY category_name");
$cats->bind_param("i", $rid);
$cats->execute();
$categories = $cats->get_result();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Categories - <?php echo htmlspecialchars($_SESSION['restaurant_name']); ?></title>
<style>
body{font-family:Poppins,Arial,sans-serif;background:#f4f6f8;margin:0}
.container{max-width:900px;margin:20px auto;padding:16px}
.card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(16,24,40,0.05);margin-bottom:14px}
.input{width:100%;padding:10px;border-radius:6px;border:1px solid #ddd;margin-top:8px}
.btn{padding:8px 12px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none;border:none}
.btn-danger{background:#dc3545}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
.msg{background:#e8f3ff;color:#007bff;padding:8px;border-radius:6px;margin-bottom:8px}
</style>
</head>
<body>
<div class="container">
  <a href="dashboard.php" class="btn" style="background:#6c757d">← Back to Dashboard</a>
  <br><br>
  <div class="card">
    <?php if($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <h3><?php echo $edit ? 'Edit Category' : 'Add Category'; ?></h3>
    <form method="post">
      <input class="input" type="text" name="category_name" placeholder="Category name" required value="<?php echo $edit ? htmlspecialchars($edit['category_name']) : ''; ?>">
      <input type="hidden" name="id" value="<?php echo $edit ? intval($edit['category_id']) : 0; ?>">
      <div style="margin-top:8px">
        <button class="btn" name="save_cat" type="submit"><?php echo $edit ? 'Update' : 'Save'; ?></button>
        <?php if($edit): ?><a class="btn" href="categories.php" style="background:#6c757d;margin-left:8px">Cancel</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="card">
    <h3>Your Categories</h3>
    <table class="table">
      <thead><tr><th>#</th><th>Name</th><th>Action</th></tr></thead>
      <tbody>
        <?php $i=1; while($c = $categories->fetch_assoc()): ?>
          <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($c['category_name']); ?></td>
            <td>
              <a class="btn" href="categories.php?edit=<?php echo $c['category_id']; ?>">Edit</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this category?');">
                <input type="hidden" name="delete_id" value="<?php echo $c['category_id']; ?>">
                <button class="btn-danger" type="submit">Delete</button>
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

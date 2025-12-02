<?php
// restaurant_panel/food_items.php (add / edit / delete for owner; edit with image replace)
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: ../restaurant_owner_login.php");
    exit();
}
include "../includes/db.php";

$rid = intval($_SESSION['restaurant_id']);
$msg = "";

// load categories for select
$catStmt = $conn->prepare("SELECT category_id, category_name FROM categories WHERE restaurant_id = ? ORDER BY category_name");
$catStmt->bind_param("i",$rid);
$catStmt->execute();
$categories = $catStmt->get_result();

// Handle add or update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_food'])) {
    $fid = intval($_POST['fid'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);

    if ($name === '' || $category_id <= 0) {
        $msg = "Name and category required.";
    } else {
        // Image handling
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $imageName = time() . "_" . basename($_FILES['image']['name']);
            if (!is_dir("../assets/images")) @mkdir("../assets/images", 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/" . $imageName);
        }

        if ($fid > 0) {
            // update: ensure this food belongs to this restaurant
            $chk = $conn->prepare("SELECT image FROM food_items WHERE food_id = ? AND restaurant_id = ?");
            $chk->bind_param("ii", $fid, $rid);
            $chk->execute();
            $old = $chk->get_result()->fetch_assoc();
            if (!$old) {
                $msg = "Not allowed to edit this item.";
            } else {
                if ($imageName) {
                    // delete old image file if exists
                    if (!empty($old['image'])) @unlink("../assets/images/".$old['image']);
                    $upd = $conn->prepare("UPDATE food_items SET category_id = ?, name = ?, price = ?, image = ? WHERE food_id = ?");
                    $upd->bind_param("isdsi", $category_id, $name, $price, $imageName, $fid);
                } else {
                    $upd = $conn->prepare("UPDATE food_items SET category_id = ?, name = ?, price = ? WHERE food_id = ?");
                    $upd->bind_param("idsi", $category_id, $name, $price, $fid);
                }
                $upd->execute();
                $msg = "Food updated.";
            }
            $chk->close();
        } else {
            // insert new
            $ins = $conn->prepare("INSERT INTO food_items (restaurant_id, category_id, name, price, image) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param("iisss", $rid, $category_id, $name, $price, $imageName);
            $ins->execute();
            $msg = "Food added.";
        }
    }
}

// Delete food
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $fid = intval($_POST['delete_id']);
    // check ownership
    $chk = $conn->prepare("SELECT image FROM food_items WHERE food_id = ? AND restaurant_id = ?");
    $chk->bind_param("ii", $fid, $rid);
    $chk->execute();
    $old = $chk->get_result()->fetch_assoc();
    if (!$old) {
        $msg = "Not allowed.";
    } else {
        // check if used in order_items
        $u = $conn->prepare("SELECT COUNT(*) AS cnt FROM order_items WHERE food_id = ?");
        $u->bind_param("i", $fid);
        $u->execute();
        $used = $u->get_result()->fetch_assoc()['cnt'] ?? 0;
        if ($used > 0) {
            $msg = "Cannot delete: item used in orders.";
        } else {
            if (!empty($old['image'])) @unlink("../assets/images/".$old['image']);
            $d = $conn->prepare("DELETE FROM food_items WHERE food_id = ?");
            $d->bind_param("i", $fid);
            $d->execute();
            $msg = "Food deleted.";
        }
        $u->close();
    }
    $chk->close();
}

// load edit target if requested
$edit = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $st = $conn->prepare("SELECT food_id, category_id, name, price, image FROM food_items WHERE food_id = ? AND restaurant_id = ?");
    $st->bind_param("ii", $eid, $rid);
    $st->execute();
    $edit = $st->get_result()->fetch_assoc();
    $st->close();
}

// load items
$itemsStmt = $conn->prepare("SELECT food_id, category_id, name, price, image FROM food_items WHERE restaurant_id = ? ORDER BY food_id DESC");
$itemsStmt->bind_param("i", $rid);
$itemsStmt->execute();
$items = $itemsStmt->get_result();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Menu - <?php echo htmlspecialchars($_SESSION['restaurant_name']); ?></title>
<style>
body{font-family:Poppins,Arial,sans-serif;background:#f4f6f8;margin:0}
.container{max-width:1000px;margin:20px auto;padding:16px}
.card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(16,24,40,0.05);margin-bottom:14px}
.input,select{width:100%;padding:10px;border-radius:6px;border:1px solid #ddd;margin-top:8px}
.btn{padding:8px 12px;background:#007bff;color:#fff;border-radius:6px;border:none}
.btn-danger{background:#dc3545}
.thumb{width:70px;height:55px;object-fit:cover;border-radius:6px}
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

    <h3><?php echo $edit ? 'Edit Food' : 'Add Food'; ?></h3>
    <form method="post" enctype="multipart/form-data">
      <label>Category</label>
      <select name="category_id" required>
        <option value="">-- Select category --</option>
        <?php
        // reload categories to ensure up-to-date (rewind)
        $catStmt = $conn->prepare("SELECT category_id, category_name FROM categories WHERE restaurant_id = ? ORDER BY category_name");
        $catStmt->bind_param("i",$rid);
        $catStmt->execute();
        $cats2 = $catStmt->get_result();
        while($c = $cats2->fetch_assoc()):
        ?>
          <option value="<?php echo $c['category_id']; ?>" <?php if($edit && $edit['category_id']==$c['category_id']) echo 'selected'; ?>>
            <?php echo htmlspecialchars($c['category_name']); ?>
          </option>
        <?php endwhile; ?>
      </select>

      <input class="input" type="text" name="name" placeholder="Food name" required value="<?php echo $edit ? htmlspecialchars($edit['name']) : ''; ?>">
      <input class="input" type="text" name="price" placeholder="Price" required value="<?php echo $edit ? htmlspecialchars($edit['price']) : ''; ?>">
      <label>Image <?php if($edit && $edit['image']): ?>(current shown below)<?php endif; ?></label>
      <input type="file" name="image" class="input">

      <?php if($edit && $edit['image']): ?>
        <div style="margin-top:8px"><img src="../assets/images/<?php echo htmlspecialchars($edit['image']); ?>" class="thumb"></div>
      <?php endif; ?>

      <input type="hidden" name="fid" value="<?php echo $edit ? intval($edit['food_id']) : 0; ?>">
      <div style="margin-top:8px"><button class="btn" name="save_food" type="submit"><?php echo $edit ? 'Update' : 'Save'; ?></button></div>
    </form>
  </div>

  <div class="card">
    <h3>Your Menu</h3>
    <table class="table">
      <thead><tr><th>#</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Action</th></tr></thead>
      <tbody>
        <?php $i=1; while($it = $items->fetch_assoc()):
          // fetch category name
          $catN = '';
          $cst = $conn->prepare("SELECT category_name FROM categories WHERE category_id = ?");
          $cst->bind_param("i", $it['category_id']);
          $cst->execute();
          $catN = $cst->get_result()->fetch_assoc()['category_name'] ?? '';
          $cst->close();
        ?>
          <tr>
            <td><?php echo $i++; ?></td>
            <td><?php if($it['image']): ?><img src="../assets/images/<?php echo htmlspecialchars($it['image']); ?>" class="thumb"><?php endif; ?></td>
            <td><?php echo htmlspecialchars($it['name']); ?></td>
            <td><?php echo htmlspecialchars($catN); ?></td>
            <td><?php echo number_format((float)$it['price'],2); ?></td>
            <td>
              <a class="btn" href="food_items.php?edit=<?php echo $it['food_id']; ?>">Edit</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this item?');">
                <input type="hidden" name="delete_id" value="<?php echo $it['food_id']; ?>">
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

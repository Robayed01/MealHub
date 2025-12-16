<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: ../admin_login.php");
  exit();
}
include "../includes/db.php";

$msg = "";

// Fetch restaurants for dropdown
$restaurants = $conn->query("SELECT restaurant_id, name FROM restaurants ORDER BY name");

// ADD / EDIT OWNER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_owner'])) {
  $id = intval($_POST['id'] ?? 0);
  $username = trim($_POST['username'] ?? "");
  $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
  $password = trim($_POST['password'] ?? "");

  if ($username === "" || $restaurant_id <= 0) {
    $msg = "All fields except password are required.";
  } else {
    if ($id == 0) {
      // ADD OWNER
      if ($password === "") {
        $msg = "Password required when adding a new owner.";
      } else {
        // Check duplicate username
        $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM restaurant_owners WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $dup = $check->get_result()->fetch_assoc()['cnt'] ?? 0;

        if ($dup > 0) {
          $msg = "Username already exists.";
        } else {
          $hash = password_hash($password, PASSWORD_DEFAULT);
          $stmt = $conn->prepare("INSERT INTO restaurant_owners (username, password, restaurant_id) VALUES (?, ?, ?)");
          $stmt->bind_param("ssi", $username, $hash, $restaurant_id);
          $stmt->execute();
          $msg = "Restaurant owner added.";
        }
      }
    } else {
      // EDIT OWNER — Password NOT changed
      $stmt = $conn->prepare("UPDATE restaurant_owners SET username = ?, restaurant_id = ? WHERE owner_id = ?");
      $stmt->bind_param("sii", $username, $restaurant_id, $id);
      $stmt->execute();
      $msg = "Restaurant owner updated.";
    }
  }
}

// DELETE OWNER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  $del_id = intval($_POST['delete_id']);
  $stmt = $conn->prepare("DELETE FROM restaurant_owners WHERE owner_id = ?");
  $stmt->bind_param("i", $del_id);
  $stmt->execute();
  $msg = "Restaurant owner deleted.";
}

// FETCH OWNERS LIST
$owners = $conn->query("
    SELECT o.owner_id, o.username, r.name AS restaurant_name
    FROM restaurant_owners o
    LEFT JOIN restaurants r ON o.restaurant_id = r.restaurant_id
    ORDER BY o.owner_id DESC
");

// EDIT MODE
$edit = null;
if (isset($_GET['edit'])) {
  $eid = intval($_GET['edit']);
  $stmt = $conn->prepare("SELECT * FROM restaurant_owners WHERE owner_id = ?");
  $stmt->bind_param("i", $eid);
  $stmt->execute();
  $edit = $stmt->get_result()->fetch_assoc();
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Manage Restaurant Owners - Admin</title>
  <style>
    body {
      font-family: Poppins, Arial, sans-serif;
      background: #C7CFB7;
      margin: 0
    }

    .header {
      background: #557174;
      color: #1F2937;
      font-weight: bold;
      padding: 15px;
      font-size: 20px;
      font-family: Poppins
    }

    .container {
      max-width: 1000px;
      margin: 18px auto;
      padding: 16px
    }

    .card {
      background: #F7F7E8;
      border-radius: 10px;
      padding: 16px;
      box-shadow: 0 6px 18px rgba(16, 24, 40, 0.05);
      margin-bottom: 14px
    }

    .input,
    select {
      width: 550px;
      padding: 8px;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-top: 8px;
      font-size: 15px
    }

    .btn {
      padding: 10px 16px;
      background: #557174;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none
    }

    .btn-danger {
      background: #dc3545
    }

    .back {
      background: #557174;
      color: #fff
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px
    }

    .table th,
    .table td {
      padding: 10px;
      border-bottom: 1px solid #eee;
      text-align: left
    }

    .msg {
      background: #F7F7E8;
      color: #1F2937;
      padding: 8px;
      border-radius: 6px;
      margin-bottom: 10px
    }

    .back-btn {
      background: #557174
    }
  </style>
</head>

<body>

  <div class="header">
    <div><strong>Manage Restaurant Owners</strong></div>

  </div>

  <div class="container">
    <a href="dashboard.php" style="text-decoration-line: none" class="btn back-btn">← Back to Dashboard</a>
    <br><br>

    <?php if ($msg): ?>
      <div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

    <div class="card">
      <h3><?php echo $edit ? "Edit Owner" : "Add Restaurant Owner"; ?></h3>

      <form method="post">
        <label class="small">Username</label>
        <input class="input" type="text" name="username"
          value="<?php echo $edit ? htmlspecialchars($edit['username']) : "" ?>" required>
        <br><br>
        <label class="small">Restaurant</label>
        <select name="restaurant_id" required>
          <option value="">-- Select Restaurant --</option>
          <?php
          $reslist = $conn->query("SELECT restaurant_id, name FROM restaurants ORDER BY name");
          while ($r = $reslist->fetch_assoc()):
            ?>
            <option value="<?php echo $r['restaurant_id']; ?>" <?php if ($edit && $edit['restaurant_id'] == $r['restaurant_id'])
                 echo "selected"; ?>>
              <?php echo htmlspecialchars($r['name']); ?>
            </option>
          <?php endwhile; ?>
        </select>
        <br><br>
        <?php if (!$edit): ?>
          <label class="small">Password</label>
          <input class="input" type="password" name="password" required>
        <?php endif; ?>

        <input type="hidden" name="id" value="<?php echo $edit ? intval($edit['owner_id']) : 0; ?>">

        <div style="margin-top:12px">
          <button class="btn" name="save_owner" type="submit">Save</button>
          <?php if ($edit): ?>
            <a class="btn back" href="owners.php" style="margin-left:8px">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card">
      <h3>Restaurant Owners</h3>

      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Restaurant</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          while ($o = $owners->fetch_assoc()):
            ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($o['username']); ?></td>
              <td><?php echo htmlspecialchars($o['restaurant_name']); ?></td>
              <td>
                <a class="btn" href="owners.php?edit=<?php echo $o['owner_id']; ?>">Edit</a>

                <form method="post" style="display:inline" onsubmit="return confirm('Delete this owner?');">
                  <input type="hidden" name="delete_id" value="<?php echo $o['owner_id']; ?>">
                  <button class="btn-danger btn" type="submit">Delete</button>
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
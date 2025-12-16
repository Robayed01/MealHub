<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_login.php");
    exit();
}
include "../includes/db.php";

$msg = "";

// Allowed image types
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 2 * 1024 * 1024;

// ADD / UPDATE
if (isset($_POST['save_restaurant'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $description = trim($_POST['description']);

    $image_name = null;

    if (!empty($_FILES['image']['name'])) {
        $img = $_FILES['image'];

        if ($img['error'] === UPLOAD_ERR_OK) {
            if (!in_array($img['type'], $allowed_types)) {
                $msg = "Invalid image type!";
            } elseif ($img['size'] > $max_size) {
                $msg = "Image too large!";
            } else {
                $ext = pathinfo($img['name'], PATHINFO_EXTENSION);
                $image_name = time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
                move_uploaded_file($img['tmp_name'], "../assets/images/" . $image_name);
            }
        }
    }

    if ($id > 0) {
        // EDIT
        if ($image_name) {
            $old = $conn->prepare("SELECT image FROM restaurants WHERE restaurant_id=?");
            $old->bind_param("i", $id);
            $old->execute();
            $oldData = $old->get_result()->fetch_assoc();
            if ($oldData && $oldData['image'])
                @unlink("../assets/images/" . $oldData['image']);

            $stmt = $conn->prepare("UPDATE restaurants 
                                   SET name=?, address=?, phone=?, description=?, image=? 
                                   WHERE restaurant_id=?");
            $stmt->bind_param("sssssi", $name, $address, $phone, $description, $image_name, $id);
        } else {
            $stmt = $conn->prepare("UPDATE restaurants 
                                   SET name=?, address=?, phone=?, description=? 
                                   WHERE restaurant_id=?");
            $stmt->bind_param("ssssi", $name, $address, $phone, $description, $id);
        }

        $stmt->execute();
        $msg = "Restaurant updated!";
    } else {
        // ADD
        $stmt = $conn->prepare("INSERT INTO restaurants (name, address, phone, description, image)
                                VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $name, $address, $phone, $description, $image_name);
        $stmt->execute();
        $msg = "Restaurant added!";
    }
}

// DELETE
if (isset($_POST['delete_id'])) {
    $del = intval($_POST['delete_id']);

    // Check if categories exist → block delete
    $chk = $conn->prepare("SELECT COUNT(*) AS total FROM categories WHERE restaurant_id=?");
    $chk->bind_param("i", $del);
    $chk->execute();
    $count = $chk->get_result()->fetch_assoc()['total'];

    if ($count > 0) {
        $msg = "Cannot delete restaurant: It has categories or menu items.";
    } else {
        $stmt = $conn->prepare("DELETE FROM restaurants WHERE restaurant_id=?");
        $stmt->bind_param("i", $del);
        $stmt->execute();
        $msg = "Restaurant deleted!";
    }
}

// EDIT MODE
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM restaurants WHERE restaurant_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

// GET LIST
$list = $conn->query("SELECT * FROM restaurants ORDER BY restaurant_id DESC");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Restaurants - Admin</title>
    <style>
        body {
            font-family: Poppins;
            margin: 0;
            background: #C7CFB7
        }

        .header {
            background: #557174;
            color: #1F2937;
            font-weight: bold;
            padding: 15px;
            font-size: 20px
        }

        .container {
            max-width: 1100px;
            margin: auto;
            padding: 20px
        }

        .card {
            background: #F7F7E8;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1)
        }

        .input {
            width: 550px;
            padding: 10px;
            margin-top: 8px;
            border-radius: 8px;
            border: 1px solid #ccc
        }

        .btn {
            padding: 10px 16px;
            background: #557174;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer
        }

        .btn-danger {
            background: #dc3545
        }

        .thumb {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px
        }

        a {
            text-decoration: none;
            color: #557174
        }

        .back-btn {
            background: #557174
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee
        }
    </style>
</head>

<body>

    <div class="header">Manage Restaurants</div>

    <div class="container">

        <a href="dashboard.php" class="btn back-btn">← Back to Dashboard</a>
        <br><br>

        <?php if ($msg): ?>
            <div class="card" style="background:#e8f3ff;color:#007bff;"><?php echo $msg; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><?php echo isset($edit) ? "Edit Restaurant" : "Add Restaurant"; ?></h3>

            <form method="post" enctype="multipart/form-data">
                <input type="text" name="name" class="input" required placeholder="Restaurant name"
                    value="<?php echo $edit['name'] ?? ""; ?>">

                <input type="text" name="address" class="input" required placeholder="Address"
                    value="<?php echo $edit['address'] ?? ""; ?>">

                <input type="text" name="phone" class="input" placeholder="Phone number"
                    value="<?php echo $edit['phone'] ?? ""; ?>">

                <textarea name="description" class="input" placeholder="Description"><?php
                echo $edit['description'] ?? ""; ?></textarea>
                <br><br>

                <label>Restaurant Image (optional):</label>
                <br>
                <input type="file" name="image" class="input">

                <?php if ($edit && $edit['image']): ?>
                    <img src="../assets/images/<?php echo $edit['image']; ?>" class="thumb">
                <?php endif; ?>

                <input type="hidden" name="id" value="<?php echo $edit['restaurant_id'] ?? 0; ?>">
                <br><br>
                <button class="btn" name="save_restaurant">Save</button>
                <?php if ($edit): ?><a href="restaurants.php" class="btn back-btn">Cancel</a><?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h3>All Restaurants</h3>
            <table>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>

                <?php $i = 1;
                while ($r = $list->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td>
                            <?php if ($r['image']): ?>
                                <img src="../assets/images/<?php echo $r['image']; ?>" class="thumb">
                            <?php endif; ?>
                        </td>
                        <td><?php echo $r['name']; ?></td>
                        <td><?php echo $r['address']; ?></td>
                        <td>
                            <a href="restaurants.php?edit=<?php echo $r['restaurant_id']; ?>" class="btn">Edit</a>

                            <form method="post" style="display:inline"
                                onsubmit="return confirm('Delete this restaurant?');">
                                <input type="hidden" name="delete_id" value="<?php echo $r['restaurant_id']; ?>">
                                <button class="btn-danger btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>
</body>

</html>
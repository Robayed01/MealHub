<?php
// restaurant_panel/reports.php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: ../restaurant_owner_login.php");
    exit();
}
include "../includes/db.php";

$rid = intval($_SESSION['restaurant_id']);
$rname = htmlspecialchars($_SESSION['restaurant_name']);

$end = isset($_GET['end_date']) && $_GET['end_date'] !== '' ? $_GET['end_date'] : date('Y-m-d');
$start = isset($_GET['start_date']) && $_GET['start_date'] !== '' ? $_GET['start_date'] : date('Y-m-d', strtotime('-29 days'));

// VALIDATE dates
$start_dt = DateTime::createFromFormat('Y-m-d', $start);
$end_dt = DateTime::createFromFormat('Y-m-d', $end);
if (!$start_dt) $start = date('Y-m-d', strtotime('-29 days'));
if (!$end_dt) $end = date('Y-m-d');
if (strtotime($end) < strtotime($start)) { $tmp = $start; $start = $end; $end = $tmp; }

// GET SUMMARY FOR THIS RESTAURANT
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT oi.order_id) AS total_orders,
           COALESCE(SUM(oi.price * oi.quantity), 0) AS revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    WHERE oi.restaurant_id = ?
      AND DATE(o.created_at) BETWEEN ? AND ?
");
$stmt->bind_param("iss", $rid, $start, $end);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$totalOrders = (int)($row['total_orders'] ?? 0);
$revenue = (float)($row['revenue'] ?? 0);

// DAILY SUMMARY
$dailyStmt = $conn->prepare("
    SELECT DATE(o.created_at) AS dt,
           COUNT(DISTINCT oi.order_id) AS orders_count,
           COALESCE(SUM(oi.price * oi.quantity), 0) AS revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    WHERE oi.restaurant_id = ?
      AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY DATE(o.created_at)
    ORDER BY DATE(o.created_at) DESC
    LIMIT 90
");
$dailyStmt->bind_param("iss", $rid, $start, $end);
$dailyStmt->execute();
$daily = $dailyStmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $rname; ?> — Revenue Report</title>
<style>
body{font-family:Poppins,Arial,sans-serif;background:#f5f6f7;margin:0}
.container{max-width:900px;margin:20px auto;padding:16px}
.card{background:#fff;border-radius:10px;padding:18px;margin-bottom:16px;box-shadow:0 5px 15px rgba(0,0,0,0.08)}
.btn{padding:8px 14px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none;border:none}
.btn-back{background:#6c757d}
.input{padding:8px;border:1px solid #ccc;border-radius:6px}
.table{width:100%;border-collapse:collapse;margin-top:14px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee}
.stat{font-size:28px;font-weight:700}
.small{font-size:13px;color:#777}
</style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn btn-back">← Back to Dashboard</a>
    <br><br>

    <div class="card">
        <h2>Revenue Report — <?php echo $rname; ?></h2>

        <form method="get" style="display:flex;gap:12px;flex-wrap:wrap;">
            <label>Start:
                <input class="input" type="date" name="start_date" value="<?php echo $start; ?>">
            </label>

            <label>End:
                <input class="input" type="date" name="end_date" value="<?php echo $end; ?>">
            </label>

            <button class="btn" type="submit">Filter</button>
            <a href="reports.php" class="btn btn-back">Reset</a>
        </form>

        <div style="display:flex;gap:20px;margin-top:20px">
            <div style="flex:1;background:#f8fbff;padding:16px;border-radius:8px;text-align:center">
                <div class="small">Total Orders</div>
                <div class="stat"><?php echo $totalOrders; ?></div>
            </div>

            <div style="flex:1;background:#f8fff8;padding:16px;border-radius:8px;text-align:center">
                <div class="small">Total Revenue</div>
                <div class="stat" style="color:green;">৳<?php echo number_format($revenue,2); ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <h3>Daily Summary</h3>
        <table class="table">
            <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
            <tbody>
                <?php if ($daily->num_rows > 0): ?>
                    <?php while($d = $daily->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $d['dt']; ?></td>
                            <td><?php echo $d['orders_count']; ?></td>
                            <td>৳<?php echo number_format($d['revenue'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">No data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>

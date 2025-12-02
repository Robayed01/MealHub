<?php
// admin/reports.php (fixed: use orders.created_at for date filtering)
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_login.php");
    exit();
}
include "../includes/db.php";

// load restaurants for dropdown
$resList = $conn->query("SELECT restaurant_id, name FROM restaurants ORDER BY name");

// date range default last 30 days
$end = isset($_GET['end_date']) && $_GET['end_date'] !== '' ? $_GET['end_date'] : date('Y-m-d');
$start = isset($_GET['start_date']) && $_GET['start_date'] !== '' ? $_GET['start_date'] : date('Y-m-d', strtotime('-29 days'));

// selected restaurant (0 means ALL)
$selected_rest = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']) : 0;

// validate dates
$start_dt = DateTime::createFromFormat('Y-m-d', $start);
$end_dt = DateTime::createFromFormat('Y-m-d', $end);
if (!$start_dt) $start = date('Y-m-d', strtotime('-29 days'));
if (!$end_dt) $end = date('Y-m-d');
if (strtotime($end) < strtotime($start)) { $tmp=$start; $start=$end; $end=$tmp; }

// Build totals depending on selection
if ($selected_rest === 0) {
    // ALL restaurants: aggregate order_items joined with orders (date from orders.created_at)
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT oi.order_id) AS cnt, COALESCE(SUM(oi.price * oi.quantity),0) AS rev
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE DATE(o.created_at) BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $start, $end);
} else {
    // specific restaurant
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT oi.order_id) AS cnt, COALESCE(SUM(oi.price * oi.quantity),0) AS rev
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.restaurant_id = ? AND DATE(o.created_at) BETWEEN ? AND ?
    ");
    $stmt->bind_param("iss", $selected_rest, $start, $end);
}
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$totalOrders = (int)($row['cnt'] ?? 0);
$revenue = (float)($row['rev'] ?? 0.0);
$stmt->close();

// daily breakdown (group by DATE(orders.created_at))
if ($selected_rest === 0) {
    $daily_sql = "
      SELECT DATE(o.created_at) AS dt, COUNT(DISTINCT oi.order_id) AS orders_count, COALESCE(SUM(oi.price * oi.quantity),0) AS revenue
      FROM order_items oi
      JOIN orders o ON oi.order_id = o.order_id
      WHERE DATE(o.created_at) BETWEEN ? AND ?
      GROUP BY DATE(o.created_at)
      ORDER BY DATE(o.created_at) DESC
      LIMIT 90
    ";
    $daily_stmt = $conn->prepare($daily_sql);
    $daily_stmt->bind_param("ss", $start, $end);
} else {
    $daily_sql = "
      SELECT DATE(o.created_at) AS dt, COUNT(DISTINCT oi.order_id) AS orders_count, COALESCE(SUM(oi.price * oi.quantity),0) AS revenue
      FROM order_items oi
      JOIN orders o ON oi.order_id = o.order_id
      WHERE oi.restaurant_id = ? AND DATE(o.created_at) BETWEEN ? AND ?
      GROUP BY DATE(o.created_at)
      ORDER BY DATE(o.created_at) DESC
      LIMIT 90
    ";
    $daily_stmt = $conn->prepare($daily_sql);
    $daily_stmt->bind_param("iss", $selected_rest, $start, $end);
}
$daily_stmt->execute();
$daily_result = $daily_stmt->get_result();
$daily_stmt->close();

// helper: get selected restaurant name for heading
$selected_name = "All Restaurants";
if ($selected_rest !== 0) {
    $s = $conn->prepare("SELECT name FROM restaurants WHERE restaurant_id = ?");
    $s->bind_param("i", $selected_rest);
    $s->execute();
    $selected_name = $s->get_result()->fetch_assoc()['name'] ?? 'Selected Restaurant';
    $s->close();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Reports - MealHub Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Poppins,Arial,sans-serif;background:#f4f6f8;margin:0}
.header{background:#007bff;color:#fff;padding:14px}
.container{max-width:1000px;margin:20px auto;padding:16px}
.card{background:#fff;padding:20px;border-radius:10px;margin-bottom:14px;box-shadow:0 6px 20px rgba(16,24,40,0.05)}
.stat{font-size:28px;font-weight:700}
.table{width:100%;border-collapse:collapse;margin-top:10px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
.form-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.input{padding:8px;border-radius:6px;border:1px solid #ddd}
.btn{padding:8px 12px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none;border:none}
.small{font-size:13px;color:#666}
.back-btn{background:#6c757d}
</style>
</head>
<body>

<div class="header">MealHub Admin — Reports</div>

<div class="container">
<a href="dashboard.php" class="btn back-btn">← Back to Dashboard</a>
<br><br>

  <div class="card">
    <h2>Summary — <?php echo htmlspecialchars($selected_name); ?></h2>

    <form method="get" style="margin-top:12px" class="form-row">
      <label>Restaurant:
        <select name="restaurant_id" class="input">
          <option value="0"<?php if($selected_rest===0) echo ' selected'; ?>>-- All Restaurants --</option>
          <?php
          $resList = $conn->query("SELECT restaurant_id, name FROM restaurants ORDER BY name");
          while ($r = $resList->fetch_assoc()):
          ?>
            <option value="<?php echo (int)$r['restaurant_id']; ?>" <?php if($selected_rest==(int)$r['restaurant_id']) echo 'selected'; ?>>
              <?php echo htmlspecialchars($r['name']); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </label>

      <label>Start: <input class="input" type="date" name="start_date" value="<?php echo htmlspecialchars($start); ?>"></label>
      <label>End: <input class="input" type="date" name="end_date" value="<?php echo htmlspecialchars($end); ?>"></label>
      <button class="btn" type="submit">Filter</button>
    </form>

    <div style="display:flex;gap:20px;margin-top:18px">
      <div style="flex:1;padding:16px;background:#f8fbff;border-radius:8px;text-align:center">
        <div class="small">Total Orders</div>
        <div class="stat"><?php echo $totalOrders; ?></div>
      </div>
      <div style="flex:1;padding:16px;background:#f8fff8;border-radius:8px;text-align:center">
        <div class="small">Total Revenue</div>
        <div class="stat" style="color:green">৳<?php echo number_format($revenue, 2); ?> </div>
      </div>
    </div>
  </div>

  <div class="card">
    <h3>Daily summary (<?php echo htmlspecialchars($start); ?> → <?php echo htmlspecialchars($end); ?>)</h3>
    <table class="table">
      <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
      <tbody>
        <?php if ($daily_result && $daily_result->num_rows > 0): ?>
          <?php while($r = $daily_result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['dt']); ?></td>
              <td><?php echo (int)$r['orders_count']; ?></td>
              <td>৳<?php echo number_format((float)$r['revenue'], 2); ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="3">No data for this range.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>

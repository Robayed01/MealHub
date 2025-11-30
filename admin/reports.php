<?php
// admin/reports.php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_login.php");
    exit();
}
include "../includes/db.php";

// determine date range: default last 30 days
$end = isset($_GET['end_date']) && $_GET['end_date'] !== '' ? $_GET['end_date'] : date('Y-m-d');
$start = isset($_GET['start_date']) && $_GET['start_date'] !== '' ? $_GET['start_date'] : date('Y-m-d', strtotime('-29 days'));

// validate dates (simple)
$start_dt = DateTime::createFromFormat('Y-m-d', $start);
$end_dt = DateTime::createFromFormat('Y-m-d', $end);
if (!$start_dt) $start = date('Y-m-d', strtotime('-29 days'));
if (!$end_dt) $end = date('Y-m-d');

// make sure end is >= start
if (strtotime($end) < strtotime($start)) {
    // swap
    $tmp = $start; $start = $end; $end = $tmp;
}

// compute totals for range (use orders.total_amount)
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(total_amount),0) AS rev
                        FROM orders
                        WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt->bind_param("ss", $start, $end);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$totalOrders = (int)($row['cnt'] ?? 0);
$revenue = (float)($row['rev'] ?? 0.0);
$stmt->close();

// daily breakdown for the range (limit to 90 rows to be safe)
$daily_stmt = $conn->prepare("
    SELECT DATE(created_at) AS dt, COUNT(*) AS orders_count, COALESCE(SUM(total_amount),0) AS revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) DESC
    LIMIT 90
");
$daily_stmt->bind_param("ss", $start, $end);
$daily_stmt->execute();
$daily_result = $daily_stmt->get_result();
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
.card{background:#fff;padding:20px;border-radius:10px;margin-bottom:14px;
      box-shadow:0 6px 20px rgba(16,24,40,0.05)}
.stat{font-size:28px;font-weight:700}
.table{width:100%;border-collapse:collapse;margin-top:10px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
.form-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.input{padding:8px;border-radius:6px;border:1px solid #ddd}
.btn{padding:8px 12px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none;border:none}
.small{font-size:13px;color:#666}
.back-btn { padding: 10px 18px; background:#6c757d; color:white; border-radius:8px; text-decoration:none}
</style>
</head>
<body>

<div class="header">MealHub Admin</div>

<div class="container">
<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
<br><br>

  <div class="card">
    <h2>Summary</h2>
    <p class="small">Totals for the selected date range.</p>

    <form method="get" style="margin-top:12px" class="form-row">
      <label>Start: <input class="input" type="date" name="start_date" value="<?php echo htmlspecialchars($start); ?>"></label>
      <label>End: <input class="input" type="date" name="end_date" value="<?php echo htmlspecialchars($end); ?>"></label>
      <button class="btn" type="submit">Filter</button>
      <a class="btn" href="reports.php" style="background:#6c757d;margin-left:8px">Reset (last 30 days)</a>
    </form>

    <div style="display:flex;gap:20px;margin-top:18px">
      <div style="flex:1;padding:16px;background:#f8fbff;border-radius:8px;text-align:center">
        <div class="small">Total Orders</div>
        <div class="stat"><?php echo $totalOrders; ?></div>
      </div>
      <div style="flex:1;padding:16px;background:#f8fff8;border-radius:8px;text-align:center">
        <div class="small">Total Revenue</div>
        <div class="stat" style="color:green"><?php echo number_format($revenue, 2); ?> </div>
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
              <td><?php echo number_format((float)$r['revenue'], 2); ?></td>
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

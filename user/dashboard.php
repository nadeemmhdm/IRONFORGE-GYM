<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}
include '../db_connect.php';

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();

// Check Fee Status for current month
$current_month = date('Y-m');
$fee_query = $conn->query("SELECT status FROM payments WHERE user_id=$uid AND month_year='$current_month' AND type='monthly'");
$fee_status = "Unpaid";
if ($fee_query->num_rows > 0) {
    $f = $fee_query->fetch_assoc();
    $fee_status = ucfirst($f['status']);
}

// Workout Stats
$workout_count = $conn->query("SELECT COUNT(*) as c FROM workouts WHERE user_id=$uid")->fetch_assoc()['c'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h1>Welcome,
                <?php echo explode(' ', $user['full_name'])[0]; ?>
            </h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Status</h3>
                    <p><span class="badge badge-<?php echo strtolower($user['status']); ?>">
                            <?php echo strtoupper($user['status']); ?>
                        </span></p>
                </div>
                <div class="stat-card">
                    <h3>Fee:
                        <?php echo date('M Y'); ?>
                    </h3>
                    <p>
                        <span
                            class="badge badge-<?php echo strtolower($fee_status == 'Approved' ? 'active' : ($fee_status == 'Pending' ? 'pending' : 'blocked')); ?>">
                            <?php echo $fee_status; ?>
                        </span>
                    </p>
                    <?php if ($fee_status == 'Unpaid'): ?>
                        <a href="payments.php"
                            style="color: var(--danger); font-size: 0.8rem; display: block; margin-top: 5px;">Pay Now
                            &rarr;</a>
                    <?php endif; ?>
                </div>
                <div class="stat-card">
                    <h3>
                        <?php echo $workout_count; ?>
                    </h3>
                    <p>Workouts Logged</p>
                </div>
            </div>

            <div class="grid-2">
                <div class="stat-card">
                    <h3>Recent Activity</h3>
                    <?php
                    $w_res = $conn->query("SELECT * FROM workouts WHERE user_id=$uid ORDER BY date DESC LIMIT 3");
                    if ($w_res->num_rows > 0) {
                        while ($w = $w_res->fetch_assoc()) {
                            echo "<div style='border-bottom:1px solid #333; padding: 10px 0;'>";
                            echo "<strong>" . date('M d', strtotime($w['date'])) . "</strong>: " . $w['exercise_name'];
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No workouts yet.</p>";
                    }
                    ?>
                </div>
                <div class="stat-card">
                    <h3>Profile</h3>
                    <p><strong>Plan:</strong>
                        <?php echo $user['membership_type']; ?>
                    </p>
                    <p><strong>Member Since:</strong>
                        <?php echo date('M Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>
            </div>

        </div>
    </div>
</body>

</html>
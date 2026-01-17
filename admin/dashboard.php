<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../admin_login.php");
    exit();
}
include '../db_connect.php';
include 'system_checks.php';

// --- Stats Logic ---

// Basic Counts
$total_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
$active_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE status='active'")->fetch_assoc()['c'];
$archived_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE status='archived'")->fetch_assoc()['c'];

// Financials
$current_month = date('Y-m');
$current_year = date('Y');

// Monthly Collected (This Month)
$m_coll = $conn->query("SELECT SUM(amount) as s FROM payments WHERE type='monthly' AND month_year='$current_month' AND status='approved'")->fetch_assoc()['s'] ?? 0;
// Yearly Collected (This Year)
$y_coll = $conn->query("SELECT SUM(amount) as s FROM payments WHERE type='yearly' AND month_year='$current_year' AND status='approved'")->fetch_assoc()['s'] ?? 0;

// Monthly Paid/Unpaid (Active Users)
// Assuming all active users with Monthly membership need to pay monthly
$monthly_users_count = $conn->query("SELECT COUNT(*) as c FROM users WHERE status='active' AND membership_type='Monthly'")->fetch_assoc()['c'];
$paid_monthly_users = $conn->query("SELECT COUNT(DISTINCT user_id) as c FROM payments WHERE type='monthly' AND month_year='$current_month' AND status='approved'")->fetch_assoc()['c'];
$unpaid_monthly = $monthly_users_count - $paid_monthly_users;
// Note: This logic assumes only Monthly members pay monthly fee. 
// If Quarterly/Yearly members don't pay monthly, this is correct for Monthly Users specifically.
// But prompt asks for "Monthly Paid / Unpaid Users". I will stick to Membership Type logic.

// Yearly Paid/Unpaid
// Assuming Yearly users pay once a year
$yearly_users_count = $conn->query("SELECT COUNT(*) as c FROM users WHERE status='active' AND membership_type='Yearly'")->fetch_assoc()['c'];
$paid_yearly_users = $conn->query("SELECT COUNT(DISTINCT user_id) as c FROM payments WHERE type='yearly' AND month_year='$current_year' AND status='approved'")->fetch_assoc()['c'];
$unpaid_yearly = $yearly_users_count - $paid_yearly_users;


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .counter-anim {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .warning-text {
            color: var(--danger);
        }
    </style>
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h1>Admin Dashboard</h1>

            <div class="stats-grid">
                <!-- Total Users -->
                <div class="stat-card">
                    <div class="counter-anim" data-target="<?php echo $total_users; ?>">0</div>
                    <p>Total Users</p>
                </div>
                <!-- Active Members -->
                <div class="stat-card">
                    <div class="counter-anim" data-target="<?php echo $active_users; ?>" style="color: var(--success);">
                        0</div>
                    <p>Active Members</p>
                </div>
                <!-- Archived -->
                <div class="stat-card">
                    <div class="counter-anim" data-target="<?php echo $archived_users; ?>"
                        style="color: var(--text-gray);">0</div>
                    <p>Archived Accounts</p>
                </div>
            </div>

            <div class="grid-2">
                <!-- Monthly Stats -->
                <div class="stat-card">
                    <h3>Monthly Overview (<?php echo date('M Y'); ?>)</h3>
                    <div class="flex-between mb-20">
                        <span>Paid Members:</span>
                        <span
                            style="color: var(--success); font-weight: bold;"><?php echo $paid_monthly_users; ?></span>
                    </div>
                    <div class="flex-between mb-20">
                        <span>Unpaid Members:</span>
                        <span style="color: var(--danger); font-weight: bold;"><?php echo $unpaid_monthly; ?></span>
                    </div>
                    <div style="background: #333; height: 10px; border-radius: 5px; overflow: hidden;">
                        <?php
                        $pct = ($monthly_users_count > 0) ? ($paid_monthly_users / $monthly_users_count) * 100 : 0;
                        ?>
                        <div
                            style="width: <?php echo $pct; ?>%; background: var(--success); height: 100%; transition: width 1s ease;">
                        </div>
                    </div>
                    <p style="margin-top: 10px; font-size: 0.9rem;">Revenue: $<?php echo $m_coll; ?></p>

                    <a href="defaulters.php?type=monthly" class="btn btn-outline"
                        style="width: 100%; margin-top: 20px; text-align: center;">View Monthly Defaulters</a>
                </div>

                <!-- Yearly Stats -->
                <div class="stat-card">
                    <h3>Yearly Overview (<?php echo date('Y'); ?>)</h3>
                    <div class="flex-between mb-20">
                        <span>Paid Members:</span>
                        <span style="color: var(--success); font-weight: bold;"><?php echo $paid_yearly_users; ?></span>
                    </div>
                    <div class="flex-between mb-20">
                        <span>Unpaid Members:</span>
                        <span style="color: var(--danger); font-weight: bold;"><?php echo $unpaid_yearly; ?></span>
                    </div>
                    <p style="margin-top: 10px; font-size: 0.9rem;">Revenue: $<?php echo $y_coll; ?></p>

                    <a href="defaulters.php?type=yearly" class="btn btn-outline"
                        style="width: 100%; margin-top: 20px; text-align: center;">View Yearly Defaulters</a>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Counter Animation
        const counters = document.querySelectorAll('.counter-anim');
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const inc = target / 50; // Speed

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 20);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });
    </script>
</body>

</html>
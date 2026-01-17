<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../admin_login.php");
    exit();
}
include '../db_connect.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'monthly';
$current_month = date('Y-m');
$current_year = date('Y');

$defaulters = [];

if ($type == 'monthly') {
    // Get all Active Monthly memebrs who DO NOT have an approved payment for current month
    $sql = "SELECT u.id, u.full_name, u.phone, u.membership_type 
            FROM users u 
            WHERE u.status = 'active' 
            AND u.membership_type = 'Monthly' 
            AND u.id NOT IN (
                SELECT user_id FROM payments WHERE type='monthly' AND month_year='$current_month' AND status='approved'
            )";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $defaulters[] = $row;
    }
} elseif ($type == 'yearly') {
    // Get all Active Yearly members who DO NOT have approved payment for current year
    $sql = "SELECT u.id, u.full_name, u.phone, u.membership_type 
            FROM users u 
            WHERE u.status = 'active' 
            AND u.membership_type = 'Yearly' 
            AND u.id NOT IN (
                SELECT user_id FROM payments WHERE type='yearly' AND month_year='$current_year' AND status='approved'
            )";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $defaulters[] = $row;
    }
}

// Handle Reminders / Actions (Mockup for now as no email system configured)
if (isset($_GET['archive_id'])) {
    $aid = intval($_GET['archive_id']);
    $conn->query("UPDATE users SET status='archived' WHERE id=$aid");
    header("Location: defaulters.php?type=$type");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        <?php echo ucfirst($type); ?> Defaulters
    </title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h1>
                <?php echo ucfirst($type); ?> Fee Defaulters
            </h1>
            <p style="margin-bottom: 20px; color: var(--text-gray);">
                Showing members who haven't paid for
                <?php echo ($type == 'monthly') ? $current_month : $current_year; ?>
            </p>

            <div class="mb-20">
                <a href="defaulters.php?type=monthly"
                    class="btn <?php echo $type == 'monthly' ? '' : 'btn-outline'; ?>">Monthly</a>
                <a href="defaulters.php?type=yearly"
                    class="btn <?php echo $type == 'yearly' ? '' : 'btn-outline'; ?>">Yearly</a>
            </div>

            <?php if (empty($defaulters)): ?>
                <div class="stat-card text-center">
                    <h3 style="color: var(--success);">All Clear!</h3>
                    <p>No defaulters found for this period.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($defaulters as $row): ?>
                            <tr style="border-left: 3px solid var(--danger);">
                                <td>
                                    <?php echo $row['full_name']; ?>
                                </td>
                                <td>
                                    <?php echo $row['phone']; ?>
                                </td>
                                <td>
                                    <?php echo $row['membership_type']; ?>
                                </td>
                                <td><span class="badge badge-blocked">Unpaid</span></td>
                                <td>
                                    <a href="#" class="btn btn-outline" style="padding: 5px;">Reminder</a>
                                    <a href="defaulters.php?type=<?php echo $type; ?>&archive_id=<?php echo $row['id']; ?>"
                                        class="btn btn-danger" style="padding: 5px;">Archive</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
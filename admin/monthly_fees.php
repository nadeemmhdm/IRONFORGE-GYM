<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../admin_login.php");
    exit();
}
include '../db_connect.php';

$current_month = date('Y-m');

if (isset($_POST['mark_paid'])) {
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];
    // Check if exists
    $check = $conn->query("SELECT id FROM payments WHERE user_id=$user_id AND month_year='$current_month' AND type='monthly'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO payments (user_id, type, amount, month_year, status, remarks) VALUES ($user_id, 'monthly', '$amount', '$current_month', 'approved', 'Manual Payment by Admin')");
    } else {
        $conn->query("UPDATE payments SET status='approved' WHERE user_id=$user_id AND month_year='$current_month' AND type='monthly'");
    }
    header("Location: monthly_fees.php");
    exit();
}

$bg_query = "SELECT setting_value FROM settings WHERE setting_key = 'monthly_fee'";
$result = $conn->query($bg_query);
$default_fee = ($result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : "30";
$users = $conn->query("SELECT id, full_name, membership_type FROM users WHERE status='active' AND role='user'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Monthly Fee Management</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h1>Monthly Fees - <?php echo date('F Y'); ?></h1>

            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()):
                        $uid = $row['id'];
                        $pay_check = $conn->query("SELECT status, amount FROM payments WHERE user_id=$uid AND month_year='$current_month' AND type='monthly'");
                        $status = "Unpaid";
                        $amount = $default_fee; // Default
                        if ($pay_check->num_rows > 0) {
                            $p_data = $pay_check->fetch_assoc();
                            $status = ucfirst($p_data['status']);
                            $amount = $p_data['amount'];
                        }
                        ?>
                        <tr>
                            <td>
                                <?php echo $row['full_name']; ?>
                            </td>
                            <td>
                                <?php echo $row['membership_type']; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($status); ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td>$
                                <?php echo $amount; ?>
                            </td>
                            <td>
                                <?php if ($status != 'Approved'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                                        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                                        <button type="submit" name="mark_paid" class="btn btn-outline"
                                            style="padding: 5px;">Mark Paid</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--success);">✔ Paid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
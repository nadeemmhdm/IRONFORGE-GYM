<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../admin_login.php");
    exit();
}
include '../db_connect.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $pid = intval($_GET['id']);
    $action = $_GET['action'];
    $status = ($action == 'approve') ? 'approved' : 'rejected';

    $conn->query("UPDATE payments SET status='$status' WHERE id=$pid");

    // If Registration Fee Approved, activate user
    if ($action == 'approve') {
        $p_res = $conn->query("SELECT user_id, type FROM payments WHERE id=$pid");
        if ($p_res->num_rows > 0) {
            $p_row = $p_res->fetch_assoc();
            if ($p_row['type'] == 'registration') {
                $conn->query("UPDATE users SET status='active' WHERE id=" . $p_row['user_id']);
            }
        }
    }

    header("Location: approve_payments.php");
    exit();
}

$sql = "SELECT p.*, u.full_name FROM payments p JOIN users u ON p.user_id = u.id WHERE p.status='pending'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fee Approvals</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h1>Pending Payment Approvals</h1>

            <?php if ($result->num_rows == 0): ?>
                <p>No pending payments.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Proof</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo $row['full_name']; ?>
                                </td>
                                <td>
                                    <?php echo strtoupper($row['type']); ?>
                                </td>
                                <td>$
                                    <?php echo $row['amount']; ?>
                                </td>
                                <td>
                                    <?php echo date('M d', strtotime($row['created_at'])); ?>
                                </td>
                                <td>
                                    <a href="../<?php echo $row['screenshot_path']; ?>" target="_blank" class="btn btn-outline"
                                        style="padding: 5px;">View Proof</a>
                                </td>
                                <td>
                                    <a href="approve_payments.php?action=approve&id=<?php echo $row['id']; ?>" class="btn"
                                        style="padding: 5px; background: var(--success); color: #000;">Approve</a>
                                    <a href="approve_payments.php?action=reject&id=<?php echo $row['id']; ?>"
                                        class="btn btn-danger" style="padding: 5px;">Reject</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
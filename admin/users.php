<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../admin_login.php");
    exit();
}
include '../db_connect.php';

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    $status = '';

    if ($action == 'approve')
        $status = 'active';
    if ($action == 'reject')
        $status = 'rejected';
    if ($action == 'block')
        $status = 'blocked';
    if ($action == 'unblock')
        $status = 'active';
    if ($action == 'archive')
        $status = 'archived';
    if ($action == 'unarchive')
        $status = 'active';

    if ($status) {
        $conn->query("UPDATE users SET status='$status' WHERE id=$id");

        // If approving, also mark registration payment as approved if exists
        if ($action == 'approve') {
            $conn->query("UPDATE payments SET status='approved' WHERE user_id=$id AND type='registration'");
        }
    }
    header("Location: users.php");
    exit();
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$sql = "SELECT u.*, p.screenshot_path FROM users u LEFT JOIN payments p ON u.id = p.user_id AND p.type='registration' WHERE u.role='user'";
if ($filter) {
    $sql .= " AND u.status='$filter'";
}
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h1>User Management</h1>

            <div class="mb-20">
                <a href="users.php" class="btn btn-outline">All</a>
                <a href="users.php?filter=active" class="btn btn-outline">Active</a>
                <a href="users.php?filter=pending" class="btn btn-outline">Pending</a>
                <a href="users.php?filter=blocked" class="btn btn-outline">Blocked</a>
                <a href="users.php?filter=archived" class="btn btn-outline">Archived</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Plan</th>
                        <th>Reg Proof</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#
                                <?php echo $row['id']; ?>
                            </td>
                            <td>
                                <?php echo $row['full_name']; ?>
                                <br><small style="color:var(--text-gray);">
                                    <?php echo $row['email']; ?>
                                </small>
                            </td>
                            <td>
                                <?php echo $row['phone']; ?>
                            </td>
                            <td>
                                <?php echo $row['membership_type']; ?>
                            </td>
                            <td>
                                <?php if ($row['screenshot_path']): ?>
                                    <a href="../<?php echo $row['screenshot_path']; ?>" target="_blank"
                                        style="color: var(--primary);">View</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span></td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <a href="users.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-outline"
                                        style="padding: 5px;">Approve</a>
                                    <a href="users.php?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-danger"
                                        style="padding: 5px;">Reject</a>
                                <?php elseif ($row['status'] == 'active'): ?>
                                    <a href="users.php?action=block&id=<?php echo $row['id']; ?>" class="btn btn-danger"
                                        style="padding: 5px;">Block</a>
                                    <a href="users.php?action=archive&id=<?php echo $row['id']; ?>" class="btn btn-outline"
                                        style="padding: 5px;">Archive</a>
                                <?php elseif ($row['status'] == 'blocked'): ?>
                                    <a href="users.php?action=unblock&id=<?php echo $row['id']; ?>" class="btn btn-outline"
                                        style="padding: 5px;">Unblock</a>
                                <?php elseif ($row['status'] == 'archived'): ?>
                                    <a href="users.php?action=unarchive&id=<?php echo $row['id']; ?>" class="btn btn-outline"
                                        style="padding: 5px;">Unarchive</a>
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